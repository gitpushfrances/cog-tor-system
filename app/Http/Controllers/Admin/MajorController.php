<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::with('course.department')->withCount('students')->latest()->paginate(15);
        return view('admin.majors.index', compact('majors'));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')->orderBy('name')->get();
        return view('admin.majors.create', compact('courses'));
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

        Major::create($validated);

        return redirect()->route('admin.majors.index')
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

        return redirect()->route('admin.majors.index')
            ->with('success', 'Major updated successfully.');
    }

    public function destroy(Major $major)
    {
        if ($major->students()->count() > 0) {
            return redirect()->route('admin.majors.index')
                ->with('error', 'Cannot delete major with existing students.');
        }

        $major->delete();

        return redirect()->route('admin.majors.index')
            ->with('success', 'Major deleted successfully.');
    }
}
