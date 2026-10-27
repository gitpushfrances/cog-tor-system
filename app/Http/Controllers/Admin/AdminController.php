<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Department;
use App\Models\SchoolYear;
use App\Models\Semester;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'total_students' => Student::count(),
            'active_students' => Student::active()->count(),
            'total_subjects' => Subject::count(),
            'total_departments' => Department::count(),
            'current_school_year' => SchoolYear::where('status', 'active')->first(),
        ];

        $recent_users = User::with('roles')->latest()->take(5)->get();

        $activeSemester = $stats['current_school_year']
            ? Semester::where('school_year_id', $stats['current_school_year']->id)->where('status', 'active')->first()
            : null;

        $recentActivity = Activity::with(['causer', 'subject'])->latest('id')->take(10)->get()
            ->loadMorph('subject', ActivityLogController::SUBJECT_RELATIONS);

        return view('admin.dashboard', compact('stats', 'recent_users', 'activeSemester', 'recentActivity'));
    }
}
