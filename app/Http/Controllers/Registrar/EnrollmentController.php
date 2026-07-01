<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        $activeSemester = Semester::active()->first();

        $enrollments = Enrollment::with(['student', 'subject.course', 'semester'])
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->latest()
            ->paginate(20);

        $students = Student::active()->orderBy('last_name')->get();
        $subjects = Subject::active()->orderBy('code')->get();

        $enrolledMap = Enrollment::where('semester_id', $activeSemester?->id)
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->pluck('subject_id')->toArray());

        return view('registrar.enrollments.index', compact('enrollments', 'students', 'subjects', 'activeSemester', 'enrolledMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $activeSemester = Semester::active()->firstOrFail();

        $subject = Subject::findOrFail($request->subject_id);

        try {
            [$enrollment, $created] = Enrollment::firstOrCreate(
                [
                    'student_id'  => $request->student_id,
                    'subject_id'  => $subject->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'enrolled_by'     => auth()->id(),
                    'enrollment_date' => now(),
                    'status'          => 'enrolled',
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Student is already enrolled in this subject for the active semester.');
        }

        if (!$created) {
            return back()->with('error', 'Student is already enrolled in this subject for the active semester.');
        }

        return back()->with('success', 'Student enrolled successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        if ($enrollment->grade()->exists()) {
            return back()->with('error', 'Cannot remove enrollment — a grade record already exists.');
        }

        $enrollment->delete();

        return back()->with('success', 'Enrollment removed.');
    }
}
