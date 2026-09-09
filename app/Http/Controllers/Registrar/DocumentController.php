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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Approximate rows (year/semester labels + subject rows) that fit on
     * one printed TOR page before a header/signature repeat is needed.
     * Tune this after checking a real multi-page render.
     */
    private const TOR_ROWS_PER_PAGE = 25;

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
            'elementary_graduation_year'    => 'nullable|string|max:20',
            'high_school'                   => 'nullable|string|max:255',
            'high_school_graduation_year'   => 'nullable|string|max:20',
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
     * flattens the year/semester/subject structure into a single ordered
     * list of "entries" (year label, semester label, subject rows), then
     * chunks that list into pages of roughly tor_rows_per_page rows each.
     * a group label is never left as the very last row on a page.
     */
    private function paginatetorentries(array $yeargroups): array
    {
        $flat = [];
        foreach ($yeargroups as $year) {
            $flat[] = ['type' => 'year', 'label' => $year['year_label']];
            foreach ($year['semesters'] as $sem) {
                $flat[] = ['type' => 'semester', 'label' => $sem['semester_label']];
                foreach ($sem['subjects'] as $subject) {
                    $flat[] = array_merge(['type' => 'subject'], $subject);
                }
            }
        }

        if (empty($flat)) {
            return [['entries' => [], 'isLast' => true]];
        }

        $pages = [];
        $current = [];
        $count = count($flat);

        for ($i = 0; $i < $count; $i++) {
            $entry = $flat[$i];
            $wouldbelastonpage = (count($current) + 1) >= self::TOR_ROWS_PER_PAGE;
            $islabel = in_array($entry['type'], ['year', 'semester']);
            $hasmoreafter = $i < $count - 1;

            // don't leave a group label as the final row on a page — push it to the next page instead.
            if ($wouldbelastonpage && $islabel && $hasmoreafter) {
                $pages[] = ['entries' => $current, 'isLast' => false];
                $current = [];
            }

            $current[] = $entry;

            if (count($current) >= self::TOR_ROWS_PER_PAGE && $i < $count - 1) {
                $pages[] = ['entries' => $current, 'isLast' => false];
                $current = [];
            }
        }

        if (!empty($current)) {
            $pages[] = ['entries' => $current, 'isLast' => true];
        }

        if (!empty($pages)) {
            $pages[count($pages) - 1]['isLast'] = true;
        }

        return $pages;
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
        $semesters = Semester::whereHas('enrollments', function ($q) use ($student) {
            $q->where('student_id', $student->id)
              ->whereHas('grade', fn($q) => $q->where('status', 'finalized'));
        })->get();

        return view('registrar.cog', compact('student', 'semesters'));
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
            $supersededIds = CogRecord::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->where('is_current', true)
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

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $documentNumber . '.pdf"',
        ]);
    }

    public function torForm(Student $student)
    {
        $hasFinalized = Enrollment::where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->exists();

        return view('registrar.tor', compact('student', 'hasFinalized'));
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
        $pages = $this->paginateTorEntries($yearGroups);

        $totalUnits = $enrollments->sum(fn($e) => $e->subject->units);
        $cumulativeGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $tor = new TorRecord(array_merge(
            ['document_number' => 'PREVIEW', 'cumulative_gwa' => $cumulativeGwa],
            $request->only(array_keys($this->torValidationRules()))
        ));

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
            $supersededIds = TorRecord::where('student_id', $student->id)
                ->where('is_current', true)
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
        $pages = $this->paginateTorEntries($yearGroups);

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
        $semesterId = $request->input('semester_id');
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
        if ($semesterId) {
            $cogQuery->where('semester_id', $semesterId);
        } elseif ($schoolYear) {
            $cogQuery->whereHas('semester', fn($q) => $q->where('school_year_id', $schoolYear));
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
        $semesters   = Semester::orderBy('semester_order')->get();
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
