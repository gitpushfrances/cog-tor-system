<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\GradeSubmission;
use Illuminate\Http\Request;

class DeanController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students'     => Student::count(),
            'active_enrollments' => Enrollment::enrolled()->count(),
            'pending_grades'     => GradeSubmission::pendingReview()->count(),
            'approved_grades'    => GradeSubmission::approved()->count(),
        ];

        $pending_submissions = GradeSubmission::with(['grade.enrollment.student', 'grade.enrollment.subject', 'submittedBy'])
            ->pendingReview()
            ->latest()
            ->take(10)
            ->get();

        return view('dean.dashboard', compact('stats', 'pending_submissions'));
    }

    public function review(GradeSubmission $submission)
    {
        $submission->load(['grade.enrollment.student', 'grade.enrollment.subject', 'submittedBy']);
        return view('dean.review', compact('submission'));
    }

    public function approve(Request $request, GradeSubmission $submission)
    {
        $submission->update([
            'dean_action' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $submission->grade->update(['status' => 'approved_by_dean']);

        return redirect()->route('dean.dashboard')->with('success', 'Grade approved and forwarded to Registrar.');
    }

    public function reject(Request $request, GradeSubmission $submission)
    {
        $request->validate([
            'dean_remarks' => 'required|string|max:500',
        ]);

        $submission->update([
            'dean_action'  => 'rejected',
            'dean_remarks' => $request->dean_remarks,
            'reviewed_at'  => now(),
            'reviewed_by'  => auth()->id(),
        ]);

        $submission->grade->update(['status' => 'pending']);

        return redirect()->route('dean.dashboard')->with('error', 'Grade returned to faculty with remarks.');
    }
}
