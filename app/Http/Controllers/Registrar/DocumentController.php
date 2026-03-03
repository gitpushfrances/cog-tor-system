<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Semester;
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
        // Load all finalized enrollments grouped by school year → semester
        $enrollments = Enrollment::with(['subject', 'grade', 'semester.schoolYear'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        // Group: school_year_id → semester_id → enrollments
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

                    // Check if COG already generated for this semester
                    $cogRecord = CogRecord::where('student_id', $bySem->first()->student_id)
                        ->where('semester_id', $semester->id)
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

        // Latest TOR record
        $torRecord = TorRecord::where('student_id', $student->id)->latest()->first();

        // Cumulative GWA across all finalized
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

        $documentNumber = 'COG-' . strtoupper(uniqid());

        $cog = CogRecord::create([
            'student_id'      => $student->id,
            'semester_id'     => $semester->id,
            'generated_by'    => auth()->id(),
            'document_number' => $documentNumber,
            'semester_gwa'    => $semesterGwa,
            'grade_data'      => $gradeData,
            'generated_at'    => now(),
        ]);

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
        $enrollments = Enrollment::with(['subject', 'grade', 'semester'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn($q) => $q->where('status', 'finalized'))
            ->get();

        $allGradesData = $enrollments->groupBy('semester_id')->map(function ($group) {
            $first = $group->first();
            return [
                'semester'  => $first->semester->name ?? 'N/A',
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

        $documentNumber = 'TOR-' . strtoupper(uniqid());

        $tor = TorRecord::create([
            'student_id'      => $student->id,
            'generated_by'    => auth()->id(),
            'document_number' => $documentNumber,
            'cumulative_gwa'  => $cumulativeGwa,
            'all_grades_data' => $allGradesData,
            'tor_type'        => 'complete',
            'generated_at'    => now(),
        ]);

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
}
