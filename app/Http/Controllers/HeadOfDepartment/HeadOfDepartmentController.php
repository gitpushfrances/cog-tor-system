<?php

namespace App\Http\Controllers\HeadOfDepartment;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\GradeSubmission;
use App\Models\Grade;
use Illuminate\Http\Request;

class HeadOfDepartmentController extends Controller
{
    public function index()
    {
        $departmentId = auth()->user()->department_id;

        $stats = [
            'total_students' => Student::whereHas('course', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })->count(),

            'active_enrollments' => Enrollment::whereHas('subject', function ($q) use ($departmentId) {
                $q->whereHas('course', fn($q2) => $q2->where('department_id', $departmentId));
            })->enrolled()->count(),

            'pending_grades' => GradeSubmission::whereHas('grade.enrollment.subject', function ($q) use ($departmentId) {
                $q->whereHas('course', fn($q2) => $q2->where('department_id', $departmentId));
            })->pendingReview()->count(),

            'approved_grades' => GradeSubmission::whereHas('grade.enrollment.subject', function ($q) use ($departmentId) {
                $q->whereHas('course', fn($q2) => $q2->where('department_id', $departmentId));
            })->approved()->count(),
        ];

        $pending_submissions = GradeSubmission::with([
                'grade.enrollment.student',
                'grade.enrollment.subject',
                'submittedBy'
            ])
            ->whereHas('grade.enrollment.subject', function ($q) use ($departmentId) {
                $q->whereHas('course', fn($q2) => $q2->where('department_id', $departmentId));
            })
            ->pendingReview()
            ->latest()
            ->get()
            ->groupBy(fn($s) => $s->grade->enrollment->subject_id);

        return view('head_of_department.dashboard', compact('stats', 'pending_submissions'));
    }

    public function review(GradeSubmission $submission)
    {
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

        return view('head_of_department.review', compact('submissions', 'subject', 'faculty', 'submission'));
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
                'dean_action' => 'approved_by_head_of_department',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);
            $sub->grade->update(['status' => 'approved_by_head_of_department']);
        }

        return redirect()->route('head_of_department.dashboard')
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

        return redirect()->route('head_of_department.dashboard')
            ->with('error', count($submissions) . ' grades rejected and returned to faculty.');
    }
}
