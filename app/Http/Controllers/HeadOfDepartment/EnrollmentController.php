<?php

namespace App\Http\Controllers\HeadOfDepartment;

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
        $departmentId   = auth()->user()->department_id;
        $activeSemester = Semester::active()->first();

        $enrollments = Enrollment::with(['student', 'subject.course', 'semester'])
            ->whereHas('subject.course', fn($q) => $q->where('department_id', $departmentId))
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->latest()
            ->paginate(20);

        $students = Student::whereHas('course', fn($q) => $q->where('department_id', $departmentId))
            ->active()->orderBy('last_name')->get();

        $subjects = Subject::whereHas('course', fn($q) => $q->where('department_id', $departmentId))
            ->active()->orderBy('code')->get();

        return view('head_of_department.enrollments.index', compact('enrollments', 'students', 'subjects', 'activeSemester'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $activeSemester = Semester::active()->firstOrFail();
        $departmentId   = auth()->user()->department_id;

        $subject = Subject::whereHas('course', fn($q) => $q->where('department_id', $departmentId))
            ->findOrFail($request->subject_id);

        $exists = Enrollment::where('student_id', $request->student_id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $activeSemester->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Student is already enrolled in this subject for the active semester.');
        }

        Enrollment::create([
            'student_id'      => $request->student_id,
            'subject_id'      => $subject->id,
            'semester_id'     => $activeSemester->id,
            'enrolled_by'     => auth()->id(),
            'enrollment_date' => now(),
            'status'          => 'enrolled',
        ]);

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
