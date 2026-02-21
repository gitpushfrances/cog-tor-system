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
        $subjects = Subject::where('faculty_id', $user->id)->with('course')->get();
        $stats = [
            'assigned_subjects' => $subjects->count(),
            'total_grades'      => Grade::where('faculty_id', $user->id)->count(),
            'pending_grades'    => Grade::where('faculty_id', $user->id)->pending()->count(),
            'approved_grades'   => Grade::where('faculty_id', $user->id)->approvedByDean()->count(),
        ];
        $recent_grades = Grade::with(['enrollment.student', 'enrollment.subject'])
            ->where('faculty_id', $user->id)
            ->latest()->take(10)->get();
        return view('faculty.dashboard', compact('stats', 'recent_grades', 'subjects'));
    }

    public function subjects()
    {
        $user = auth()->user();
        $subjects = Subject::where('faculty_id', $user->id)
            ->with(['course', 'enrollments.student', 'enrollments.grade'])
            ->get();
        return view('faculty.subjects', compact('subjects'));
    }
}
