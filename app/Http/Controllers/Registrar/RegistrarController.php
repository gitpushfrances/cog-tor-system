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
    public function finalizeSubject(Request $request, $subjectId)
    {
        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->approved()
            ->whereNull('finalized_at')
            ->get();

        foreach ($submissions as $sub) {
            $sub->update([
                'finalized_at' => now(),
                'finalized_by' => auth()->id(),
            ]);
            $sub->grade->update(['status' => 'finalized']);
        }

        return redirect()->route('registrar.dashboard', ['tab' => 'finalization'])
            ->with('success', count($submissions) . ' grades finalized successfully.');
    }

    public function finalize(Request $request, \App\Models\GradeSubmission $submission)
    {
        $submission->update([
            'finalized_at' => now(),
            'finalized_by' => auth()->id(),
        ]);

        $submission->grade->update(['status' => 'finalized']);

        return redirect()->route('registrar.dashboard')->with('success', 'Grade finalized successfully.');
    }

    public function unfinalizeSubject(Request $request, $subjectId)
    {
        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->whereNotNull('finalized_at')
            ->get();

        if ($submissions->isEmpty()) {
            return redirect()->back()->with('error', 'No finalized grades found for this subject.');
        }

        foreach ($submissions as $sub) {
            $sub->update([
                'finalized_at' => null,
                'finalized_by' => null,
            ]);
            $sub->grade->update(['status' => 'approved_by_head_of_department']);
        }

        return redirect()->back()
            ->with('success', count($submissions) . ' grade(s) unfinalized and returned to approved status.');
    }

    public function index(Request $request)
    {
        $stats = [
            'pending_finalization' => GradeSubmission::approved()->whereNull('finalized_at')->count(),
            'finalized_grades'     => GradeSubmission::finalized()->count(),
            'cog_generated'        => CogRecord::count(),
            'tor_generated'        => TorRecord::count(),
        ];

        // Group pending submissions by subject
        $pending_submissions = GradeSubmission::with([
                'grade.enrollment.student',
                'grade.enrollment.subject',
                'reviewedBy'
            ])
            ->approved()
            ->whereNull('finalized_at')
            ->latest()
            ->get()
            ->groupBy(fn($s) => $s->grade->enrollment->subject_id);

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
