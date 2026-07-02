<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Semester;
use App\Models\SchoolYear;
use App\Models\Department;
use App\Models\CogRecord;
use App\Models\TorRecord;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
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

        return view('registrar.student-profile', compact('student', 'grouped', 'torRecord', 'cumulativeGwa'));
    }

    public function cogForm(Student $student)
    {
        $semesters = Semester::whereHas('enrollments', function ($q) use ($student) {
            $q->where('student_id', $student->id)
              ->whereHas('grade', fn($q) => $q->where('status', 'finalized'));
        })->get();

        return view('registrar.cog', compact('student', 'semesters'));
    }

    public function generateCog(Request $request, Student $student)
    {
        $request->validate(['semester_id' => 'required|exists:semesters,id']);

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

        $cog = \DB::transaction(function () use ($student, $semester, $gradeData, $semesterGwa) {
            $superseded = CogRecord::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            \Log::info('COG supersede check', [
                'student_id'       => $student->id,
                'semester_id'      => $semester->id,
                'rows_superseded'  => $superseded,
            ]);

            $documentNumber = 'COG-' . strtoupper(uniqid());

            return CogRecord::create([
                'student_id'      => $student->id,
                'semester_id'     => $semester->id,
                'generated_by'    => auth()->id(),
                'document_number' => $documentNumber,
                'semester_gwa'    => $semesterGwa,
                'grade_data'      => $gradeData,
                'generated_at'    => now(),
                'is_current'      => true,
            ]);
        });

        $documentNumber = $cog->document_number;

        $pdf = Pdf::loadView('registrar.pdf.cog', compact('student', 'semester', 'gradeData', 'semesterGwa', 'cog'));
        $path = 'cog/' . $documentNumber . '.pdf';
        $pdfOutput = $pdf->output();
        Storage::put($path, $pdfOutput);
        $cog->update(['pdf_path' => $path]);

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

    public function generateTor(Request $request, Student $student)
    {
        $enrollments = Enrollment::with(['subject', 'grade', 'semester.schoolYear'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $allGradesData = $enrollments->groupBy('semester_id')->map(function ($group) {
            $first = $group->first();
            $semester = $first->semester;
            $schoolYear = $semester->schoolYear->year_code ?? 'N/A';
            $semLabel = ($semester->semester_name ?? 'N/A') . ' — SY ' . $schoolYear;
            return [
                'semester'  => $semLabel,
                'subjects'  => $group->map(fn($e) => [
                    'subject_code' => $e->subject->code,
                    'subject_name' => $e->subject->name,
                    'units'        => $e->subject->units,
                    'grade'        => $e->grade->grade,
                ])->toArray(),
            ];
        })->values()->toArray();

        $totalUnits = $enrollments->sum(fn($e) => $e->subject->units);
        $cumulativeGwa = $totalUnits > 0
            ? $enrollments->sum(fn($e) => $e->grade->grade * $e->subject->units) / $totalUnits
            : null;

        $tor = \DB::transaction(function () use ($student, $allGradesData, $cumulativeGwa) {
            $superseded = TorRecord::where('student_id', $student->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            \Log::info('TOR supersede check', [
                'student_id'      => $student->id,
                'rows_superseded' => $superseded,
            ]);

            $documentNumber = 'TOR-' . strtoupper(uniqid());

            return TorRecord::create([
                'student_id'      => $student->id,
                'generated_by'    => auth()->id(),
                'document_number' => $documentNumber,
                'cumulative_gwa'  => $cumulativeGwa,
                'all_grades_data' => $allGradesData,
                'tor_type'        => 'complete',
                'generated_at'    => now(),
                'is_current'      => true,
            ]);
        });

        $documentNumber = $tor->document_number;

        $pdf = Pdf::loadView('registrar.pdf.tor', compact('student', 'allGradesData', 'cumulativeGwa', 'tor'));
        $path = 'tor/' . $documentNumber . '.pdf';
        $pdfOutput = $pdf->output();
        Storage::put($path, $pdfOutput);
        $tor->update(['pdf_path' => $path]);

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
