<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\GradeSubmission;
use App\Models\Grade;
use Illuminate\Http\Request;

class DeanController extends Controller
{
    public function index()
    {
        $departmentId = auth()->user()->department_id;

        $stats = [
            'total_students' => Student::whereHas('course', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })->count(),
            'active_enrollments' => Enrollment::enrolled()->count(),
            'pending_grades'     => GradeSubmission::pendingReview()->count(),
            'approved_grades'    => GradeSubmission::approved()->count(),
        ];

        // Group pending submissions by subject so dashboard shows one row per subject
        $pending_submissions = GradeSubmission::with([
                'grade.enrollment.student',
                'grade.enrollment.subject',
                'submittedBy'
            ])
            ->pendingReview()
            ->latest()
            ->get()
            ->groupBy(fn($s) => $s->grade->enrollment->subject_id);

        return view('dean.dashboard', compact('stats', 'pending_submissions'));
    }

    public function review(GradeSubmission $submission)
    {
        // Load ALL submissions for the same subject + same faculty
        $subjectId   = $submission->grade->enrollment->subject_id;
        $submittedBy = $submission->submitted_by;

        $submissions = GradeSubmission::with([
                'grade.enrollment.student',
                'grade.enrollment.subject',
                'submittedBy'
            ])
            ->whereHas('grade.enrollment', fn($q) => $q->where('subject_id', $subjectId))
            ->where('submitted_by', $submittedBy)
            ->whereNull('reviewed_at')
            ->get();

        $subject = $submission->grade->enrollment->subject;
        $faculty = $submission->submittedBy;

        return view('dean.review', compact('submissions', 'subject', 'faculty', 'submission'));
    }

    public function approve(Request $request, GradeSubmission $submission)
    {
        $subjectId   = $submission->grade->enrollment->subject_id;
        $submittedBy = $submission->submitted_by;

        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->where('submitted_by', $submittedBy)
            ->whereNull('reviewed_at')
            ->get();

        foreach ($submissions as $sub) {
            $sub->update([
                'dean_action' => 'approved_by_dean',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);
            $sub->grade->update(['status' => 'approved_by_dean']);
        }

        return redirect()->route('dean.dashboard')
            ->with('success', count($submissions) . ' grades approved and forwarded to Registrar.');
    }

    public function reject(Request $request, GradeSubmission $submission)
    {
        $request->validate([
            'dean_remarks' => 'required|string|max:500',
        ]);

        $subjectId   = $submission->grade->enrollment->subject_id;
        $submittedBy = $submission->submitted_by;

        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->where('submitted_by', $submittedBy)
            ->whereNull('reviewed_at')
            ->get();

        foreach ($submissions as $sub) {
            $sub->update([
                'dean_action'  => 'rejected',
                'dean_remarks' => $request->dean_remarks,
                'reviewed_at'  => now(),
                'reviewed_by'  => auth()->id(),
            ]);
            $sub->grade->update(['status' => 'rejected']);
        }

        return redirect()->route('dean.dashboard')
            ->with('error', count($submissions) . ' grades rejected and returned to faculty.');
    }
}
