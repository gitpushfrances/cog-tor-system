<?php
namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\GradeSubmission;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Subject $subject)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) {
            abort(403, 'You are not assigned to this subject.');
        }
        $enrollments = Enrollment::where('subject_id', $subject->id)
            ->with(['student', 'grade.submission'])
            ->get();

        // Get latest submission for this subject to show status/remarks
        $latestSubmission = GradeSubmission::whereHas('grade.enrollment', function ($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->latest()->first();

        return view('faculty.grades.index', compact('subject', 'enrollments', 'latestSubmission'));
    }

    public function store(Request $request, Subject $subject)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        $request->validate([
            'grades'                 => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.grade'         => 'required|numeric|min:1.00|max:5.00',
        ]);

        foreach ($request->grades as $item) {
            Grade::updateOrCreate(
                ['enrollment_id' => $item['enrollment_id']],
                [
                    'faculty_id' => $user->id,
                    'grade'      => $item['grade'],
                    'status'     => 'saved',
                    'remarks'    => $item['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('faculty.subjects.grades', $subject)
            ->with('success', 'Grades saved successfully.');
    }

    public function edit(Subject $subject, Grade $grade)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        // Allow edit only on saved or rejected grades
        if (!in_array($grade->status, ['saved', 'rejected'])) {
            return redirect()->route('faculty.subjects.grades', $subject)
                ->with('error', 'Only saved or rejected grades can be edited.');
        }

        $enrollment = $grade->enrollment()->with('student')->first();
        return view('faculty.grades.edit', compact('subject', 'grade', 'enrollment'));
    }

    public function update(Request $request, Subject $subject, Grade $grade)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        if (!in_array($grade->status, ['saved', 'rejected'])) {
            return redirect()->route('faculty.subjects.grades', $subject)
                ->with('error', 'Only saved or rejected grades can be edited.');
        }

        $request->validate([
            'grade'   => 'required|numeric|min:1.00|max:5.00',
            'remarks' => 'nullable|string|max:500',
        ]);

        $grade->update([
            'grade'   => $request->grade,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('faculty.subjects.grades', $subject)
            ->with('success', 'Grade updated successfully.');
    }

    public function submit(Request $request, Subject $subject)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        $grades = Grade::whereHas('enrollment', function ($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->where('faculty_id', $user->id)->where('status', 'saved')->get(); // ✅ was 'pending'

        if ($grades->isEmpty()) {
            return redirect()->route('faculty.subjects.grades', $subject)
                ->with('error', 'No saved grades to submit.');
        }

        foreach ($grades as $grade) {
            GradeSubmission::updateOrCreate(
                ['grade_id' => $grade->id],
                [
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'dean_action'  => null,
                    'dean_remarks' => null,
                    'reviewed_at'  => null,
                    'reviewed_by'  => null,
                ]
            );
            $grade->update(['status' => 'pending_head_of_department_review']);
        }

        return redirect()->route('faculty.subjects.grades', $subject)
            ->with('success', 'Grades submitted to Head of Department for approval.');
    }

    public function resubmit(Request $request, Subject $subject)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        $request->validate([
            'faculty_remarks' => 'required|string|max:500',
        ]);

        // Get all rejected grades for this subject
        $grades = Grade::whereHas('enrollment', function ($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->where('faculty_id', $user->id)->where('status', 'rejected')->get();

        if ($grades->isEmpty()) {
            return redirect()->route('faculty.subjects.grades', $subject)
                ->with('error', 'No rejected grades to resubmit.');
        }

        foreach ($grades as $grade) {
            // Update submission — save faculty remarks, increment count, reset hod action
            $submission = $grade->submission;
            if ($submission) {
                $submission->update([
                    'faculty_remarks'    => $request->faculty_remarks,
                    'resubmission_count' => $submission->resubmission_count + 1,
                    'dean_action'        => null,
                    'dean_remarks'       => null,
                    'reviewed_at'        => null,
                    'reviewed_by'        => null,
                    'submitted_at'       => now(),
                ]);
            }
            $grade->update(['status' => 'pending_head_of_department_review']);
        }

        return redirect()->route('faculty.subjects.grades', $subject)
            ->with('success', 'Grades resubmitted to Head of Department for review.');
    }
}
