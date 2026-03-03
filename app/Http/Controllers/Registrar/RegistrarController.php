<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\GradeSubmission;
use App\Models\CogRecord;
use App\Models\TorRecord;
use App\Models\Student;
use Illuminate\Http\Request;

class RegistrarController extends Controller
{
    public function finalize(Request $request, \App\Models\GradeSubmission $submission)
    {
        $submission->update([
            'finalized_at' => now(),
            'finalized_by' => auth()->id(),
        ]);

        $submission->grade->update(['status' => 'finalized']);

        return redirect()->route('registrar.dashboard')->with('success', 'Grade finalized successfully.');
    }

    public function index(Request $request)
    {
        $stats = [
            'pending_finalization' => GradeSubmission::approved()->whereNull('finalized_at')->count(),
            'finalized_grades'     => GradeSubmission::finalized()->count(),
            'cog_generated'        => CogRecord::count(),
            'tor_generated'        => TorRecord::count(),
        ];

        $pending_submissions = GradeSubmission::with(['grade.enrollment.student', 'grade.enrollment.subject', 'reviewedBy'])
            ->approved()
            ->whereNull('finalized_at')
            ->latest()
            ->take(10)
            ->get();

        $search   = $request->input('search');
        $students = Student::with('course')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('student_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('registrar.dashboard', compact('stats', 'pending_submissions', 'search', 'students'));
    }
}
