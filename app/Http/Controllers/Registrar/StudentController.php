<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('course');

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
        $courses  = Course::active()->orderBy('name')->get();

        return view('registrar.students.index', compact('students', 'courses'));
    }

    public function create()
    {
        $courses = Course::active()->orderBy('name')->get();
        return view('registrar.students.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number',
            'course_id'      => 'required|exists:courses,id',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'suffix'         => 'nullable|string|max:10',
            'birth_date'     => 'required|date',
            'gender'         => 'required|in:Male,Female',
            'email'          => 'required|email|unique:students,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated,dropped',
        ]);

        Student::create($validated);

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Student added successfully.');
    }

    public function edit(Student $student)
    {
        $courses = Course::active()->orderBy('name')->get();
        return view('registrar.students.edit', compact('student', 'courses'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number,' . $student->id,
            'course_id'      => 'required|exists:courses,id',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'suffix'         => 'nullable|string|max:10',
            'birth_date'     => 'required|date',
            'gender'         => 'required|in:Male,Female',
            'email'          => 'required|email|unique:students,email,' . $student->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated,dropped',
        ]);

        $student->update($validated);

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        if ($student->enrollments()->count() > 0) {
            return redirect()->route('registrar.students.index')
                             ->with('error', 'Cannot delete student with existing enrollments.');
        }

        $student->delete();

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Student deleted successfully.');
    }
}
