<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['destroy']);
    }

    /** Subjects are managed inside their course's department (Admin → Departments). */
    public function index()
    {
        return redirect()->route('admin.departments.index');
    }

    public function show(Subject $subject)
    {
        return redirect()->to(CourseController::departmentPage($subject->course));
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
            'code'       => [
                'required', 'string', 'max:20',
                Rule::unique('subjects')->where(fn ($q) => $q->where('course_id', $request->course_id)),
            ],
            'name'       => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'units'      => 'required|numeric|min:1|max:10',
            'year_level' => 'required|integer|min:1|max:5',
            'semester'   => 'required|in:1st Semester,2nd Semester,Summer',
            'status'     => 'required|in:active,inactive',
        ]);

        $subject = Subject::create($request->only(['code', 'name', 'course_id', 'units', 'year_level', 'semester', 'status']));

        return redirect()->to(CourseController::departmentPage($subject->course))
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
            'code'       => [
                'required', 'string', 'max:20',
                Rule::unique('subjects')->where(fn ($q) => $q->where('course_id', $request->course_id))->ignore($subject->id),
            ],
            'name'       => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'units'      => 'required|numeric|min:1|max:10',
            'year_level' => 'required|integer|min:1|max:5',
            'semester'   => 'required|in:1st Semester,2nd Semester,Summer',
            'status'     => 'required|in:active,inactive',
        ]);

        $subject->update($request->only(['code', 'name', 'course_id', 'units', 'year_level', 'semester', 'status']));

        return redirect()->to(CourseController::departmentPage($subject->fresh()->course))
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->enrollments()->count() > 0) {
            return redirect()->to(CourseController::departmentPage($subject->course))
                ->with('error', 'Cannot delete subject with existing enrollments.');
        }

        $course = $subject->course;
        $subject->forceDelete();

        return redirect()->to(CourseController::departmentPage($course))
            ->with('success', 'Subject deleted successfully.');
    }
}
