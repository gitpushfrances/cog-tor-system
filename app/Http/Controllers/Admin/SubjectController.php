<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['course', 'course.department', 'faculty'])->paginate(15);
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')->with('department')->get();
        $faculties = User::role('faculty')->where('status', 'active')->get();
        return view('admin.subjects.create', compact('courses', 'faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'       => 'required|string|max:20|unique:subjects,code',
            'name'       => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'faculty_id' => 'nullable|exists:users,id',
            'units'      => 'required|numeric|min:1|max:10',
            'year_level' => 'required|integer|min:1|max:5',
            'semester'   => 'required|in:1st,2nd,Summer',
            'status'     => 'required|in:active,inactive',
        ]);

        Subject::create($request->all());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $courses = Course::where('status', 'active')->with('department')->get();
        $faculties = User::role('faculty')->where('status', 'active')->get();
        return view('admin.subjects.edit', compact('subject', 'courses', 'faculties'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'code'       => 'required|string|max:20|unique:subjects,code,' . $subject->id,
            'name'       => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'faculty_id' => 'nullable|exists:users,id',
            'units'      => 'required|numeric|min:1|max:10',
            'year_level' => 'required|integer|min:1|max:5',
            'semester'   => 'required|in:1st,2nd,Summer',
            'status'     => 'required|in:active,inactive',
        ]);

        $subject->update($request->all());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->enrollments()->count() > 0) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Cannot delete subject with existing enrollments.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
