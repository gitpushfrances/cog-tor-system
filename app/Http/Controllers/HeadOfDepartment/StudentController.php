<?php

namespace App\Http\Controllers\HeadOfDepartment;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    private function getDepartmentCourses()
    {
        return Course::where('department_id', auth()->user()->department_id)
                     ->where('status', 'active')
                     ->get();
    }

    private function departmentStudents()
    {
        return Student::whereHas('course', function ($q) {
            $q->where('department_id', auth()->user()->department_id);
        });
    }

    public function index(Request $request)
    {
        $query = $this->departmentStudents()->with('course');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('course_id'))  $query->where('course_id', $request->course_id);
        if ($request->filled('year_level')) $query->where('year_level', $request->year_level);
        if ($request->filled('status'))     $query->where('status', $request->status);

        $students = $query->orderBy('last_name')->paginate(20)->withQueryString();
        $courses  = $this->getDepartmentCourses();

        return view('head_of_department.students.index', compact('students', 'courses'));
    }

    public function create()
    {
        $courses = $this->getDepartmentCourses();
        return view('head_of_department.students.create', compact('courses'));
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
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated',
        ]);

        Course::where('id', $request->course_id)
              ->where('department_id', auth()->user()->department_id)
              ->firstOrFail();

        Student::create($request->all());

        return redirect()->route('head_of_department.students.index')
                         ->with('success', 'Student added successfully.');
    }

    public function show(Student $student)
    {
        abort_unless($student->course->department_id === auth()->user()->department_id, 403);
        $student->load('course', 'enrollments');
        return view('head_of_department.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        abort_unless($student->course->department_id === auth()->user()->department_id, 403);
        $courses = $this->getDepartmentCourses();
        return view('head_of_department.students.edit', compact('student', 'courses'));
    }

    public function update(Request $request, Student $student)
    {
        abort_unless($student->course->department_id === auth()->user()->department_id, 403);

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
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated',
        ]);

        Course::where('id', $request->course_id)
              ->where('department_id', auth()->user()->department_id)
              ->firstOrFail();

        $student->update($request->all());

        return redirect()->route('head_of_department.students.index')
                         ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        abort_unless($student->course->department_id === auth()->user()->department_id, 403);

        if ($student->enrollments()->count() > 0) {
            return redirect()->route('head_of_department.students.index')
                             ->with('error', 'Cannot delete student with existing enrollments.');
        }

        $student->delete();

        return redirect()->route('head_of_department.students.index')
                         ->with('success', 'Student deleted successfully.');
    }
}
