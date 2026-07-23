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
        $courses = Course::with(['department', 'subjects' => function ($query) {
            $query->orderBy('year_level')->orderBy('semester')->orderBy('code');
        }])->orderBy('code')->get();

        $courses->each(function ($course) {
            $course->groupedSubjects = $course->subjects->groupBy(['year_level', 'semester']);
        });

        return view('admin.subjects.index', compact('courses'));
    }

    public function create(Request $request)
    {
        $courses = Course::where('status', 'active')->with('department')->get();
        $selectedCourseId = $request->query('course_id');
        return view('admin.subjects.create', compact('courses', 'selectedCourseId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'       => 'required|string|max:20|unique:subjects,code',
            'name'       => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'units'      => 'required|numeric|min:1|max:10',
            'year_level' => 'required|integer|min:1|max:5',
            'semester'   => 'required|in:1st Semester,2nd Semester,Summer',
            'status'     => 'required|in:active,inactive',
        ]);

        Subject::create($request->only(['code', 'name', 'course_id', 'units', 'year_level', 'semester', 'status']));

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $courses = Course::where('status', 'active')->with('department')->get();
        return view('admin.subjects.edit', compact('subject', 'courses'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'code'       => 'required|string|max:20|unique:subjects,code,' . $subject->id,
            'name'       => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'units'      => 'required|numeric|min:1|max:10',
            'year_level' => 'required|integer|min:1|max:5',
            'semester'   => 'required|in:1st Semester,2nd Semester,Summer',
            'status'     => 'required|in:active,inactive',
        ]);

        $subject->update($request->only(['code', 'name', 'course_id', 'units', 'year_level', 'semester', 'status']));

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
