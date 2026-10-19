<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['destroy']);
    }

    public function index()
    {
        // Majors are managed inside their course's department (Admin → Departments).
        return redirect()->route('admin.departments.index');
    }

    public function show(Major $major)
    {
        return redirect()->to(CourseController::departmentPage($major->course));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')->orderBy('name')->get();
        $selectedCourseId = request('course_id');
        return view('admin.majors.create', compact('courses', 'selectedCourseId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('majors')->where(fn ($q) => $q->where('course_id', $request->course_id)),
            ],
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $major = Major::create($validated);

        return redirect()->to(CourseController::departmentPage($major->course))
            ->with('success', 'Major created successfully.');
    }

    public function edit(Major $major)
    {
        $courses = Course::where('status', 'active')->orderBy('name')->get();
        return view('admin.majors.edit', compact('major', 'courses'));
    }

    public function update(Request $request, Major $major)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('majors')->where(fn ($q) => $q->where('course_id', $request->course_id))->ignore($major->id),
            ],
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $major->update($validated);

        return redirect()->to(CourseController::departmentPage($major->fresh()->course))
            ->with('success', 'Major updated successfully.');
    }

    public function destroy(Major $major)
    {
        if ($major->students()->count() > 0) {
            return redirect()->to(CourseController::departmentPage($major->course))
                ->with('error', 'Cannot delete major with existing students.');
        }

        $major->delete();

        return redirect()->to(CourseController::departmentPage($major->course))
            ->with('success', 'Major deleted successfully.');
    }
}
