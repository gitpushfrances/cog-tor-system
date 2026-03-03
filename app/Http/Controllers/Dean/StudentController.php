<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // Get department-scoped courses for the logged-in Dean
    private function getDepartmentCourses()
    {
        return Course::where('department_id', auth()->user()->department_id)
                     ->where('status', 'active')
                     ->get();
    }

    // Get department-scoped student query
    private function departmentStudents()
    {
        return Student::whereHas('course', function ($q) {
            $q->where('department_id', auth()->user()->department_id);
        });
    }

    public function index(Request $request)
    {
        $query = $this->departmentStudents()->with('course');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by year level
        if ($request->filled('year_level')) {
            $query->where('year_level', $request->year_level);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('last_name')->paginate(20)->withQueryString();
        $courses = $this->getDepartmentCourses();

        return view('dean.students.index', compact('students', 'courses'));
    }

    public function create()
    {
        $courses = $this->getDepartmentCourses();
        return view('dean.students.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number',
            'course_id'      => 'required|exists:courses,id',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'suffix'         => 'nullable|string|max:10',
            'birth_date'     => 'nullable|date',
            'gender'         => 'nullable|in:Male,Female',
            'email'          => 'nullable|email|unique:students,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'status'         => 'required|in:active,inactive,graduated',
        ]);

        // Ensure course belongs to Dean's department
        $course = Course::where('id', $request->course_id)
                        ->where('department_id', auth()->user()->department_id)
                        ->firstOrFail();

        Student::create($request->all());

        return redirect()->route('dean.students.index')
                         ->with('success', 'Student added successfully.');
    }

    public function show(Student $student)
    {
        // Ensure student belongs to Dean's department
        abort_unless(
            $student->course->department_id === auth()->user()->department_id,
            403
        );

        $student->load('course', 'enrollments');
        return view('dean.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        abort_unless(
            $student->course->department_id === auth()->user()->department_id,
            403
        );

        $courses = $this->getDepartmentCourses();
        return view('dean.students.edit', compact('student', 'courses'));
    }

    public function update(Request $request, Student $student)
    {
        abort_unless(
            $student->course->department_id === auth()->user()->department_id,
            403
        );

        $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number,' . $student->id,
            'course_id'      => 'required|exists:courses,id',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'suffix'         => 'nullable|string|max:10',
            'birth_date'     => 'nullable|date',
            'gender'         => 'nullable|in:Male,Female',
            'email'          => 'nullable|email|unique:students,email,' . $student->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'status'         => 'required|in:active,inactive,graduated',
        ]);

        // Ensure new course also belongs to Dean's department
        Course::where('id', $request->course_id)
              ->where('department_id', auth()->user()->department_id)
              ->firstOrFail();

        $student->update($request->all());

        return redirect()->route('dean.students.index')
                         ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        abort_unless(
            $student->course->department_id === auth()->user()->department_id,
            403
        );

        // Block delete if enrollments exist
        if ($student->enrollments()->count() > 0) {
            return redirect()->route('dean.students.index')
                             ->with('error', 'Cannot delete student with existing enrollments.');
        }

        $student->delete();

        return redirect()->route('dean.students.index')
                         ->with('success', 'Student deleted successfully.');
    }
}
