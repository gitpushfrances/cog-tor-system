<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Department;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'pending_users' => User::pending()->count(),
            'total_students' => Student::count(),
            'active_students' => Student::active()->count(),
            'total_subjects' => Subject::count(),
            'total_departments' => Department::count(),
            'current_school_year' => SchoolYear::where('status', 'active')->first(),
        ];

        $recent_users = User::with('roles')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users'));
    }
}
