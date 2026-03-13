<?php

namespace App\Http\Controllers\HeadOfDepartment;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class SubjectAssignmentController extends Controller
{
    public function index()
    {
        $departmentId = auth()->user()->department_id;

        $subjects = Subject::with(['course', 'faculty'])
            ->whereHas('course', fn($q) => $q->where('department_id', $departmentId))
            ->active()
            ->orderBy('course_id')
            ->orderBy('year_level')
            ->orderBy('code')
            ->paginate(20);

        $facultyList = User::active()
            ->whereHas('roles', fn($q) => $q->where('name', 'faculty'))
            ->where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        return view('head_of_department.assignments.index', compact('subjects', 'facultyList'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'faculty_id' => 'nullable|exists:users,id',
        ]);

        abort_if($subject->course->department_id !== auth()->user()->department_id, 403);

        $subject->update(['faculty_id' => $request->faculty_id ?: null]);

        return back()->with('success', 'Faculty assignment updated for ' . $subject->code . '.');
    }
}
