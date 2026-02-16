<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Grade;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'assigned_subjects' => Subject::count(), // Will be filtered by faculty later
            'total_grades' => Grade::where('faculty_id', $user->id)->count(),
            'pending_grades' => Grade::where('faculty_id', $user->id)->pending()->count(),
            'approved_grades' => Grade::where('faculty_id', $user->id)->approvedByDean()->count(),
        ];

        $recent_grades = Grade::with(['enrollment.student', 'enrollment.subject'])
            ->where('faculty_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('faculty.dashboard', compact('stats', 'recent_grades'));
    }
}
