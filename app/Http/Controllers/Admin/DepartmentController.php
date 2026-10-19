<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['deactivate', 'destroy']);
    }

    public function index()
    {
        $departments = Department::withCount(['courses', 'majors', 'subjects'])
            ->orderBy('code')
            ->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $department = Department::create($validated);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', 'Department created successfully.');
    }

    /** One department with its courses, and each course's majors and subjects (client B6). */
    public function show(Department $department)
    {
        $semesterOrder = ['1st Semester' => 1, '2nd Semester' => 2, 'Summer' => 3];

        $courses = $department->courses()
            ->withCount('students')
            ->with([
                'majors' => fn ($q) => $q->withCount('students')->orderBy('code'),
                'subjects' => fn ($q) => $q->withCount('enrollments')->orderBy('year_level')->orderBy('code'),
            ])
            ->orderBy('code')
            ->get()
            ->each(function ($course) use ($semesterOrder) {
                $course->subjectGroups = $course->subjects
                    ->sortBy(fn ($s) => [$s->year_level, $semesterOrder[$s->semester] ?? 9, $s->code])
                    ->groupBy(fn ($s) => 'Year ' . $s->year_level . ' · ' . ($s->semester ?: 'Semester not set'));
            });

        $openCourse = (int) request('course');

        return view('admin.departments.show', compact('department', 'courses', 'openCourse'));
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', 'Department updated successfully.');
    }

    public function deactivate(Department $department)
    {
        $department->update(['status' => 'inactive']);
        return redirect()->route('admin.departments.show', $department)
            ->with('success', 'Department deactivated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->courses()->count() > 0) {
            return redirect()->route('admin.departments.show', $department)
                ->with('error', 'Cannot delete department with existing courses.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
