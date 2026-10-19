<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Semester;
use App\Models\SchoolYear;
use App\Models\Department;
use App\Models\CogRecord;
use App\Models\DocumentSetting;
use App\Models\TorRecord;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Table rows (year/semester labels + subjects) that fit on one A4 TOR page
     * together with the header, grading legend, a one-line remark and the
     * signatures that repeat on every page. Measured with DomPDF renders.
     */
    private const TOR_ROWS_PER_PAGE = 21;

    /** Characters of a descriptive title that fit on one line of its column; longer titles wrap and take more rows. */
    private const TOR_TITLE_CHARS_PER_LINE = 50;

    /** Characters of remarks that fit on one printed line; each extra line costs table rows. */
    private const TOR_REMARK_CHARS_PER_LINE = 100;
    private const TOR_ROWS_PER_REMARK_LINE = 1;

    /** Rows given up on the last page for the graduation statement. */
    private const TOR_GRAD_STATEMENT_ROWS = 3;

    /** Largest printed size of a scanned previous-school page (A4 content area minus the page header). */
    private const TOR_SCAN_MAX_WIDTH_IN  = 6.85;
    private const TOR_SCAN_MAX_HEIGHT_IN = 6.9;

    private function torValidationRules(): array
    {
        return [
            'remarks'                      => 'nullable|string|max:1000',
            'prepared_by_name'              => 'required|string|max:255',
            'prepared_by_title'             => 'required|string|max:255',
            'checked_by_name'               => 'required|string|max:255',
            'checked_by_credentials'        => 'nullable|string|max:255',
            'checked_by_title'              => 'required|string|max:255',
            'campus_admin_name'             => 'required|string|max:255',
            'campus_admin_title'            => 'required|string|max:255',
            'place_of_birth'                => 'nullable|string|max:255',
            'elementary_school'             => 'nullable|string|max:255',
            'elementary_graduation_year'    => 'nullable|integer|digits:4|min:1950|max:' . now()->year,
            'high_school'                   => 'nullable|string|max:255',
            'high_school_graduation_year'   => 'nullable|integer|digits:4|min:1950|max:' . now()->year,
            'degree_awarded'                => 'nullable|string|max:255',
            'major_at_graduation'           => 'nullable|string|max:255',
            'graduation_date'               => 'nullable|date',
            'board_resolution_no'           => 'nullable|string|max:255',
            'board_regents_approval_date'   => 'nullable|date',
            'nstp_serial_number'            => 'nullable|string|max:255',
        ];
    }

    private function yearlevellabel($level): string
    {
        $words = [1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth', 6 => 'sixth'];
        return ($words[$level] ?? $level . 'th') . ' year';
    }

    /**
     * groups a student's finalized enrollments into the flat,
     * storage-shape structure: year_level -> semesters -> subjects.
     * this is what gets saved in tor_records.all_grades_data.
     */
    private function buildtorgradegroups($enrollments): array
    {
        return $enrollments
            ->groupby(fn($e) => $e->subject->year_level)
            ->sortkeys()
            ->map(function ($byyear, $yearlevel) {
                $semesters = $byyear
                    ->groupby('semester_id')
                    ->sortby(fn($group) => $group->first()->semester->schoolyear->year_code . '-' . $group->first()->semester->semester_order)
                    ->map(function ($bysem) {
                        $semester = $bysem->first()->semester;
                        $label = ($semester->semester_name ?? 'n/a') . ', ' . ($semester->schoolyear->year_code ?? 'n/a');

                        return [
                            'semester_label' => $label,
                            'semester_order' => (int) ($semester->semester_order ?? 0),
                            'subjects' => $bysem->map(fn($e) => [
                                'course_code'  => $e->subject->code,
                                'subject_name' => $e->subject->name,
                                'units'        => $e->subject->units,
                                'grade'        => $e->grade->grade,
                                're_exam'      => $e->grade->re_exam_grade,
                            ])->values()->toarray(),
                        ];
                    })
                    ->values()
                    ->toarray();

                return [
                    'year_level' => $yearlevel,
                    'year_label' => $this->yearlevellabel($yearlevel),
                    'semesters'  => $semesters,
                ];
            })
            ->values()
            ->toarray();
    }

    /**
     * Builds the printed TOR pages in chronological order (year level, then
     * semester): ESSU grade rows and scanned previous-school pages.
     *
     * - ESSU rows are chunked into pages of $rowsPerPage; a year/semester label
     *   is never left as the last row of a page.
     * - A scanned record starts on a new page, one page per image.
     * - The last page is always a rows page (it carries the signatures and the
     *   graduation statement) and holds at most $lastPageRows rows.
     */
    private function paginatetorentries(array $yeargroups, $previousRecords = [], int $rowsPerPage = self::TOR_ROWS_PER_PAGE, ?int $lastPageRows = null): array
    {
        $lastPageRows ??= $rowsPerPage;

        // ESSU semesters keep their existing order; each previous-school record goes
        // in front of the first ESSU semester that comes at or after its start.
        $essu = [];
        foreach ($yeargroups as $year) {
            foreach ($year['semesters'] as $sem) {
                $essu[] = ['key' => (int) $year['year_level'] * 10 + (int) ($sem['semester_order'] ?? 0), 'year' => $year, 'sem' => $sem];
            }
        }
        $scans = collect($previousRecords)->sortBy(fn ($r) => $r->startKey())->values()->all();

        $blocks = [];
        foreach ($essu as $block) {
            while (! empty($scans) && $scans[0]->startKey() <= $block['key']) {
                $blocks[] = ['record' => array_shift($scans)];
            }
            $blocks[] = $block;
        }
        foreach ($scans as $record) {
            $blocks[] = ['record' => $record];
        }

        $pages    = [];
        $buffer   = [];
        $lastYear = null;

        foreach ($blocks as $block) {
            if (isset($block['record'])) {
                $pages    = array_merge($pages, $this->chunkTorRows($buffer, $rowsPerPage));
                $buffer   = [];
                $lastYear = null;
                $pages    = array_merge($pages, $this->torScanPages($block['record']));
                continue;
            }

            if ($block['year']['year_level'] !== $lastYear) {
                $buffer[] = ['type' => 'year', 'label' => $block['year']['year_label']];
                $lastYear = $block['year']['year_level'];
            }
            $buffer[] = ['type' => 'semester', 'label' => $block['sem']['semester_label']];
            foreach ($block['sem']['subjects'] as $subject) {
                $buffer[] = array_merge(['type' => 'subject'], $subject);
            }
        }

        $tail = $this->chunkTorRows($buffer, $rowsPerPage);

        // The last page must be a rows page that also fits the end-of-transcript content.
        $lastEntries = empty($tail) ? [] : array_pop($tail)['entries'];
        if (array_sum(array_map(fn ($e) => $this->torRowWeight($e), $lastEntries)) > $lastPageRows) {
            $split = $this->chunkTorRows($lastEntries, $lastPageRows);
            $lastEntries = array_pop($split)['entries'];
            $tail = array_merge($tail, $split);
        }

        $pages = array_merge($pages, $tail);
        $pages[] = ['type' => 'rows', 'entries' => $lastEntries, 'isLast' => true];

        return $pages;
    }

    /**
     * Chunks table rows into non-final pages by printed height (a subject whose
     * title wraps counts as several rows); never ends a page on a year/semester label.
     */
    private function chunkTorRows(array $entries, int $rowsPerPage): array
    {
        $pages   = [];
        $current = [];
        $used    = 0;
        $count   = count($entries);

        for ($i = 0; $i < $count; $i++) {
            $entry  = $entries[$i];
            $weight = $this->torRowWeight($entry);

            // A year/semester label must share its page with the labels after it and the first subject.
            $needed = $weight;
            if ($entry['type'] !== 'subject') {
                for ($j = $i + 1; $j < $count; $j++) {
                    $needed += $this->torRowWeight($entries[$j]);
                    if ($entries[$j]['type'] === 'subject') {
                        break;
                    }
                }
            }

            if (! empty($current) && $used + $needed > $rowsPerPage) {
                $pages[] = ['type' => 'rows', 'entries' => $current, 'isLast' => false];
                $current = [];
                $used    = 0;
            }

            $current[] = $entry;
            $used     += $weight;
        }

        if (! empty($current)) {
            $pages[] = ['type' => 'rows', 'entries' => $current, 'isLast' => false];
        }

        return $pages;
    }

    private function torRowWeight(array $entry): int
    {
        if ($entry['type'] !== 'subject') {
            return 1;
        }

        return max(1, (int) ceil(mb_strlen((string) $entry['subject_name']) / self::TOR_TITLE_CHARS_PER_LINE));
    }

    /** One printed page per scanned image, scaled to fit without distortion. */
    private function torScanPages($record): array
    {
        $pages = [];
        $total = $record->pages->count();

        foreach ($record->pages as $i => $page) {
            $scale = min(
                self::TOR_SCAN_MAX_WIDTH_IN / max(1, $page->width),
                self::TOR_SCAN_MAX_HEIGHT_IN / max(1, $page->height)
            );

            $pages[] = [
                'type'      => 'scan',
                'isLast'    => false,
                'school'    => $record->school_name,
                'coverage'  => $record->coverageLabel(),
                'pageNo'    => $i + 1,
                'pageCount' => $total,
                'file'      => Storage::disk('local')->path($page->path),
                'url'       => route('registrar.previous-school-pages.image', $page),
                'widthIn'   => round($page->width * $scale, 2),
                'heightIn'  => round($page->height * $scale, 2),
            ];
        }

        return $pages;
    }

    /**
     * Row budgets for this TOR: long remarks repeat on every page, and the
     * graduation statement sits on the last page only.
     *
     * @return array{0: int, 1: int} [rows per page, rows on the last page]
     */
    private function torRowBudgets(Student $student, TorRecord $tor): array
    {
        $remarkLines = max(1, (int) ceil(mb_strlen((string) $tor->remarks) / self::TOR_REMARK_CHARS_PER_LINE));
        $rows = max(5, self::TOR_ROWS_PER_PAGE - ($remarkLines - 1) * self::TOR_ROWS_PER_REMARK_LINE);

        $hasGradStatement = $student->status === 'graduated' && $tor->degree_awarded;

        return [$rows, max(3, $rows - ($hasGradStatement ? self::TOR_GRAD_STATEMENT_ROWS : 0))];
    }

    private function previousSchoolRecordsFor(Student $student)
    {
        return $student->previousSchoolRecords()->with('pages')->get();
    }
    public function students()
    {
        $students = Student::with('course')->active()->paginate(15);
        return view('registrar.students', compact('students'));
    }

    public function studentProfile(Student $student)
    {
        $enrollments = Enrollment::with(['subject', 'grade', 'semester.schoolYear'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $grouped = $enrollments
            ->groupBy(fn($e) => $e->semester->schoolYear->id)
            ->map(function ($byYear) {
                $schoolYear = $byYear->first()->semester->schoolYear;
                $semesters  = $byYear->groupBy('semester_id')->map(function ($bySem) {
                    $semester    = $bySem->first()->semester;
                    $totalUnits  = $bySem->sum(fn($e) => $e->subject->units);
                    $semesterGwa = $totalUnits > 0
                        ? $bySem->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
                        : null;

                    $cogRecord = CogRecord::where('student_id', $bySem->first()->student_id)
                        ->where('semester_id', $semester->id)
                        ->current()
                        ->latest()
                        ->first();

                    return [
                        'semester'    => $semester,
                        'enrollments' => $bySem,
                        'totalUnits'  => $totalUnits,
                        'semesterGwa' => $semesterGwa,
                        'cogRecord'   => $cogRecord,
                    ];
                });

                return [
                    'schoolYear' => $schoolYear,
                    'semesters'  => $semesters,
                ];
            });

        $torRecord = TorRecord::where('student_id', $student->id)->current()->latest()->first();

        $totalUnits    = $enrollments->sum(fn($e) => $e->subject->units);
        $cumulativeGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $documentSettings = DocumentSetting::current();

        return view('registrar.student-profile', compact('student', 'grouped', 'torRecord', 'cumulativeGwa', 'documentSettings'));
    }

    public function cogForm(Student $student)
    {
        // COG is generated from the student profile modal; the old standalone page is retired.
        return redirect()->route('registrar.students.profile', $student);
    }

    /**
     * Renders the exact COG document (same Blade view DomPDF uses)
     * as HTML, without saving anything — powers the Step 2 preview
     * in the generate modal.
     */
    public function cogPreview(Request $request, Student $student)
    {
        $request->validate([
            'semester_id'           => 'required|exists:semesters,id',
            'purpose'               => 'required|string|max:255',
            'or_number'             => 'required|string|max:50',
            'issued_date'           => 'required|date',
            'signatory_name'        => 'required|string|max:255',
            'signatory_credentials' => 'nullable|string|max:255',
            'signatory_title'       => 'required|string|max:255',
        ]);

        $semester = Semester::findOrFail($request->semester_id);

        $enrollments = Enrollment::with(['subject', 'grade'])
            ->where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $gradeData = $enrollments->map(fn($e) => [
            'subject_code' => $e->subject->code,
            'subject_name' => $e->subject->name,
            'units'        => $e->subject->units,
            'grade'        => $e->grade->grade,
        ])->toArray();

        $totalUnits = $enrollments->sum(fn($e) => $e->subject->units);
        $semesterGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $cog = new CogRecord([
            'document_number'       => 'PREVIEW',
            'purpose'               => $request->purpose,
            'or_number'             => $request->or_number,
            'issued_date'           => $request->issued_date,
            'signatory_name'        => $request->signatory_name,
            'signatory_credentials' => $request->signatory_credentials,
            'signatory_title'       => $request->signatory_title,
        ]);

        $forPdf = false;
        $html = view('registrar.pdf.cog', compact('student', 'semester', 'gradeData', 'semesterGwa', 'cog', 'forPdf'))->render();

        return response()->json(['html' => $html]);
    }

    public function generateCog(Request $request, Student $student)
    {
        $request->validate([
            'semester_id'           => 'required|exists:semesters,id',
            'purpose'               => 'required|string|max:255',
            'or_number'             => 'required|string|max:50',
            'issued_date'           => 'required|date',
            'signatory_name'        => 'required|string|max:255',
            'signatory_credentials' => 'nullable|string|max:255',
            'signatory_title'       => 'required|string|max:255',
        ]);

        $semester = Semester::findOrFail($request->semester_id);

        $enrollments = Enrollment::with(['subject', 'grade'])
            ->where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $gradeData = $enrollments->map(fn($e) => [
            'subject_code' => $e->subject->code,
            'subject_name' => $e->subject->name,
            'units'        => $e->subject->units,
            'grade'        => $e->grade->grade,
        ])->toArray();

        $totalUnits = $enrollments->sum(fn($e) => $e->subject->units);
        $semesterGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $supersededIds = [];

        $cog = \DB::transaction(function () use ($request, $student, $semester, $gradeData, $semesterGwa, &$supersededIds) {
            // One generation at a time per student (double click, two tabs): the second waits, then supersedes the first.
            Student::whereKey($student->id)->lockForUpdate()->first();

            $supersededIds = CogRecord::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->where('is_current', true)
                ->lockForUpdate()
                ->pluck('id')
                ->toArray();

            CogRecord::whereIn('id', $supersededIds)->update(['is_current' => false]);

            \Log::info('COG supersede check', [
                'student_id'       => $student->id,
                'semester_id'      => $semester->id,
                'rows_superseded'  => count($supersededIds),
            ]);

            $documentNumber = 'COG-' . strtoupper(uniqid());

            return CogRecord::create([
                'student_id'             => $student->id,
                'semester_id'            => $semester->id,
                'generated_by'           => auth()->id(),
                'document_number'        => $documentNumber,
                'semester_gwa'           => $semesterGwa,
                'grade_data'             => $gradeData,
                'generated_at'           => now(),
                'is_current'             => true,
                'purpose'                => $request->purpose,
                'or_number'              => $request->or_number,
                'issued_date'            => $request->issued_date,
                'signatory_name'         => $request->signatory_name,
                'signatory_credentials'  => $request->signatory_credentials,
                'signatory_title'        => $request->signatory_title,
            ]);
        });

        $documentNumber = $cog->document_number;

        try {
            $forPdf = true;
            $pdf = Pdf::loadView('registrar.pdf.cog', compact('student', 'semester', 'gradeData', 'semesterGwa', 'cog', 'forPdf'));
            $path = 'cog/' . $documentNumber . '.pdf';
            $pdfOutput = $pdf->output();
            Storage::put($path, $pdfOutput);
            $cog->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            \DB::transaction(function () use ($cog, $supersededIds) {
                $cog->delete();
                if (!empty($supersededIds)) {
                    CogRecord::whereIn('id', $supersededIds)->update(['is_current' => true]);
                }
            });

            \Log::error('COG PDF generation/storage failed, record rolled back', [
                'student_id'  => $student->id,
                'semester_id' => $semester->id,
                'error'       => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Something went wrong while generating the COG document. No record was saved.');
        }

        activity('document')->performedOn($cog)->event('generated')
            ->withProperties(['document_number' => $cog->document_number, 'student' => $student->getFullName(), 'semester' => $semester->semester_name])
            ->log('Generated COG');

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $documentNumber . '.pdf"',
        ]);
    }

    public function torForm(Student $student)
    {
        // TOR is generated from the student profile modal; the old standalone page is retired.
        return redirect()->route('registrar.students.profile', $student);
    }

    /**
     * Renders the exact TOR document (same Blade view DomPDF uses)
     * as HTML, without saving anything — powers the preview step
     * in the generate modal.
     */
    public function torPreview(Request $request, Student $student)
    {
        $request->validate($this->torValidationRules());

        $enrollments = Enrollment::with(['subject', 'grade', 'semester.schoolYear'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $yearGroups = $this->buildTorGradeGroups($enrollments);

        $totalUnits = $enrollments->sum(fn($e) => $e->subject->units);
        $cumulativeGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $tor = new TorRecord(array_merge(
            ['document_number' => 'PREVIEW', 'cumulative_gwa' => $cumulativeGwa],
            $request->only(array_keys($this->torValidationRules()))
        ));

        [$rowsPerPage, $lastPageRows] = $this->torRowBudgets($student, $tor);
        $pages = $this->paginateTorEntries($yearGroups, $this->previousSchoolRecordsFor($student), $rowsPerPage, $lastPageRows);

        $forPdf = false;
        $html = view('registrar.pdf.tor', compact('student', 'pages', 'tor', 'forPdf'))->render();

        return response()->json(['html' => $html]);
    }

    public function generateTor(Request $request, Student $student)
    {
        $request->validate($this->torValidationRules());

        $enrollments = Enrollment::with(['subject', 'grade', 'semester.schoolYear'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $yearGroups = $this->buildTorGradeGroups($enrollments);

        $totalUnits = $enrollments->sum(fn($e) => $e->subject->units);
        $cumulativeGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $validated = $request->only(array_keys($this->torValidationRules()));

        $supersededIds = [];

        $tor = \DB::transaction(function () use ($student, $yearGroups, $cumulativeGwa, $validated, &$supersededIds) {
            // One generation at a time per student (double click, two tabs): the second waits, then supersedes the first.
            Student::whereKey($student->id)->lockForUpdate()->first();

            $supersededIds = TorRecord::where('student_id', $student->id)
                ->where('is_current', true)
                ->lockForUpdate()
                ->pluck('id')
                ->toArray();

            TorRecord::whereIn('id', $supersededIds)->update(['is_current' => false]);

            \Log::info('TOR supersede check', [
                'student_id'      => $student->id,
                'rows_superseded' => count($supersededIds),
            ]);

            $documentNumber = 'TOR-' . strtoupper(uniqid());

            return TorRecord::create(array_merge($validated, [
                'student_id'      => $student->id,
                'generated_by'    => auth()->id(),
                'document_number' => $documentNumber,
                'cumulative_gwa'  => $cumulativeGwa,
                'all_grades_data' => $yearGroups,
                'tor_type'        => 'complete',
                'generated_at'    => now(),
                'is_current'      => true,
            ]));
        });

        $documentNumber = $tor->document_number;
        [$rowsPerPage, $lastPageRows] = $this->torRowBudgets($student, $tor);
        $pages = $this->paginateTorEntries($yearGroups, $this->previousSchoolRecordsFor($student), $rowsPerPage, $lastPageRows);

        try {
            $forPdf = true;
            $pdf = Pdf::loadView('registrar.pdf.tor', compact('student', 'pages', 'tor', 'forPdf'));
            $path = 'tor/' . $documentNumber . '.pdf';
            $pdfOutput = $pdf->output();
            Storage::put($path, $pdfOutput);
            $tor->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            \DB::transaction(function () use ($tor, $supersededIds) {
                $tor->delete();
                if (!empty($supersededIds)) {
                    TorRecord::whereIn('id', $supersededIds)->update(['is_current' => true]);
                }
            });

            \Log::error('TOR PDF generation/storage failed, record rolled back', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Something went wrong while generating the TOR document. No record was saved.');
        }

        // Keep the student's background in sync with what was printed (blank modal fields never erase saved values).
        $background = array_filter(
            Arr::only($validated, ['place_of_birth', 'elementary_school', 'elementary_graduation_year', 'high_school', 'high_school_graduation_year']),
            fn ($value) => filled($value)
        );
        if ($background) {
            $student->update($background);
        }

        activity('document')->performedOn($tor)->event('generated')
            ->withProperties(['document_number' => $tor->document_number, 'student' => $student->getFullName()])
            ->log('Generated TOR');

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $documentNumber . '.pdf"',
        ]);
    }

    public function downloadCog(CogRecord $cog)
    {
        abort_unless($cog->hasFile(), 404);
        return Storage::download($cog->pdf_path);
    }

    public function downloadTor(TorRecord $tor)
    {
        abort_unless($tor->hasFile(), 404);
        return Storage::download($tor->pdf_path);
    }

    public function previewCog(CogRecord $cog)
    {
        abort_unless($cog->hasFile(), 404);

        return response()->file(storage_path('app/' . $cog->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $cog->document_number . '.pdf"',
        ]);
    }

    public function previewTor(TorRecord $tor)
    {
        abort_unless($tor->hasFile(), 404);

        return response()->file(storage_path('app/' . $tor->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $tor->document_number . '.pdf"',
        ]);
    }

    /**
     * Documents tab — browsable, filterable list of every COG/TOR ever
     * generated, with current-vs-superseded status.
     */
    public function documentsIndex(Request $request)
    {
        $type       = $request->input('type');           // 'cog' | 'tor' | null (all)
        $status     = $request->input('status');          // 'current' | 'superseded' | null (all)
        $search     = $request->input('search');          // student name/number
        $schoolYear = $request->input('school_year_id');
        $semester   = $request->input('semester');        // semester name, e.g. "1st Semester"
        $departmentId = $request->input('department_id');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');

        $cogQuery = CogRecord::with(['student.course.department', 'semester.schoolYear', 'generatedBy']);
        $torQuery = TorRecord::with(['student.course.department', 'generatedBy']);

        // Student search (name or student number)
        if ($search) {
            $cogQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
            $torQuery->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Department filter (via student -> course -> department)
        if ($departmentId) {
            $cogQuery->whereHas('student.course', fn($q) => $q->where('department_id', $departmentId));
            $torQuery->whereHas('student.course', fn($q) => $q->where('department_id', $departmentId));
        }

        // Status filter
        if ($status === 'current') {
            $cogQuery->where('is_current', true);
            $torQuery->where('is_current', true);
        } elseif ($status === 'superseded') {
            $cogQuery->where('is_current', false);
            $torQuery->where('is_current', false);
        }

        // Date range filter (on generated_at)
        if ($dateFrom) {
            $cogQuery->whereDate('generated_at', '>=', $dateFrom);
            $torQuery->whereDate('generated_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $cogQuery->whereDate('generated_at', '<=', $dateTo);
            $torQuery->whereDate('generated_at', '<=', $dateTo);
        }

        // School Year / Semester filters — COG only (TOR is cumulative, has no single semester)
        if ($schoolYear) {
            $cogQuery->whereHas('semester', fn($q) => $q->where('school_year_id', $schoolYear));
        }
        if ($semester) {
            $cogQuery->whereHas('semester', fn($q) => $q->where('semester_name', $semester));
        }

        $cogRecords = ($type === 'tor') ? collect() : $cogQuery->get()->map(function ($r) {
            $r->doc_type = 'COG';
            return $r;
        });

        $torRecords = ($type === 'cog') ? collect() : $torQuery->get()->map(function ($r) {
            $r->doc_type = 'TOR';
            return $r;
        });

        $documents = $cogRecords->concat($torRecords)->sortByDesc('generated_at')->values();

        // Group by student for the "grouped" view toggle
        $groupedByStudent = $documents->groupBy(fn($d) => $d->student_id);

        $schoolYears = SchoolYear::orderBy('year_code', 'desc')->get();
        $semesters   = Semester::query()
            ->select('semester_name')
            ->groupBy('semester_name')
            ->orderByRaw('MIN(semester_order)')
            ->pluck('semester_name');
        $departments = Department::orderBy('name')->get();

        return view('registrar.documents', compact(
            'documents',
            'groupedByStudent',
            'schoolYears',
            'semesters',
            'departments'
        ));
    }
}
