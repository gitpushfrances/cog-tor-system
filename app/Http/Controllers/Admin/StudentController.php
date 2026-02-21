<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('course')->paginate(15);
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')->get();
        return view('admin.students.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|string|max:20|unique:students,student_id',
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'email'       => 'required|email|unique:students,email',
            'course_id'   => 'required|exists:courses,id',
            'year_level'  => 'required|integer|min:1|max:5',
            'status'      => 'required|in:active,inactive,graduated',
        ]);

        Student::create($request->all());

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        $courses = Course::where('status', 'active')->get();
        return view('admin.students.edit', compact('student', 'courses'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'student_id'  => 'required|string|max:20|unique:students,student_id,' . $student->id,
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'email'       => 'required|email|unique:students,email,' . $student->id,
            'course_id'   => 'required|exists:courses,id',
            'year_level'  => 'required|integer|min:1|max:5',
            'status'      => 'required|in:active,inactive,graduated',
        ]);

        $student->update($request->all());

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        if ($student->enrollments()->count() > 0) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Cannot delete student with existing enrollments.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
