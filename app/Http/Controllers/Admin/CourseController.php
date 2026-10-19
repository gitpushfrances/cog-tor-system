<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['destroy']);
    }

    /** Courses are managed inside their department (Admin → Departments). */
    public function index()
    {
        return redirect()->route('admin.departments.index');
    }

    public function show(Course $course)
    {
        return redirect()->to(self::departmentPage($course));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        $selectedDepartmentId = request('department_id');
        return view('admin.courses.create', compact('departments', 'selectedDepartmentId'));
    }

    /** The department page with this course opened. */
    public static function departmentPage(Course $course): string
    {
        return route('admin.departments.show', [$course->department_id, 'course' => $course->id]) . '#course-' . $course->id;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('courses')->where(fn ($q) => $q->where('department_id', $request->department_id)),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'years' => 'required|integer|min:1|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        $course = Course::create($validated);

        return redirect()->to(self::departmentPage($course))
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        $departments = Department::where('status', 'active')->get();
        return view('admin.courses.edit', compact('course', 'departments'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('courses')->where(fn ($q) => $q->where('department_id', $request->department_id))->ignore($course->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'years' => 'required|integer|min:1|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        $course->update($validated);

        return redirect()->to(self::departmentPage($course))
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if ($course->subjects()->count() > 0 || $course->students()->count() > 0) {
            return redirect()->to(self::departmentPage($course))
                ->with('error', 'Cannot delete course with existing subjects or students.');
        }

        $course->delete();

        return redirect()->route('admin.departments.show', $course->department_id)
            ->with('success', 'Course deleted successfully.');
    }
}
