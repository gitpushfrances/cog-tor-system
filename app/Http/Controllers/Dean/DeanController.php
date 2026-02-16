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
            'total_students' => Student::count(),
            'active_enrollments' => Enrollment::enrolled()->count(),
            'pending_grades' => GradeSubmission::pendingReview()->count(),
            'approved_grades' => GradeSubmission::approved()->count(),
        ];

        $pending_submissions = GradeSubmission::with(['grade.enrollment.student', 'grade.enrollment.subject', 'submittedBy'])
            ->pendingReview()
            ->latest()
            ->take(10)
            ->get();

        return view('dean.dashboard', compact('stats', 'pending_submissions'));
    }
}
