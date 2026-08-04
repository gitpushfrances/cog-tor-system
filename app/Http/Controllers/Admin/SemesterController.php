<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\SchoolYear;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::with('schoolYear')->latest()->paginate(15);
        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        $schoolYears = SchoolYear::orderBy('year_code', 'desc')->get();
        return view('admin.semesters.create', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_year_id'  => 'required|exists:school_years,id',
            'semester_order'  => 'required|in:1,2,3',
            'status'          => 'required|in:active,completed,upcoming',
        ]);

        $names = ['1' => '1st Semester', '2' => '2nd Semester', '3' => 'Summer'];

        $exists = Semester::where('school_year_id', $request->school_year_id)
            ->where('semester_order', $request->semester_order)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'This semester already exists for the selected school year.');
        }

        if ($request->status === 'active') {
            DB::transaction(function () {
                $previousActiveIds = Semester::where('status', 'active')->pluck('id');
                Semester::where('status', 'active')->update(['status' => 'completed']);
                $this->completeEnrollmentsForSemesters($previousActiveIds);
            });
        }

        Semester::create([
            'school_year_id' => $request->school_year_id,
            'semester_name'  => $names[$request->semester_order],
            'semester_order' => $request->semester_order,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'status'         => $request->status,
        ]);

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    public function edit(Semester $semester)
    {
        $schoolYears = SchoolYear::orderBy('year_code', 'desc')->get();
        return view('admin.semesters.edit', compact('semester', 'schoolYears'));
    }

    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'semester_order' => 'required|in:1,2,3',
            'status'         => 'required|in:active,completed,upcoming',
        ]);

        $names = ['1' => '1st Semester', '2' => '2nd Semester', '3' => 'Summer'];

        if ($request->status === 'active') {
            DB::transaction(function () use ($semester) {
                $previousActiveIds = Semester::where('status', 'active')
                    ->where('id', '!=', $semester->id)
                    ->pluck('id');
                Semester::where('status', 'active')
                    ->where('id', '!=', $semester->id)
                    ->update(['status' => 'completed']);
                $this->completeEnrollmentsForSemesters($previousActiveIds);
            });
        }

        $semester->update([
            'school_year_id' => $request->school_year_id,
            'semester_name'  => $names[$request->semester_order],
            'semester_order' => $request->semester_order,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'status'         => $request->status,
        ]);

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester updated successfully.');
    }

    public function destroy(Semester $semester)
    {
        if ($semester->enrollments()->count() > 0) {
            return redirect()->route('admin.semesters.index')
                ->with('error', 'Cannot delete semester with existing enrollments.');
        }

        if ($semester->status === 'active') {
            $nextUpcoming = Semester::where('school_year_id', $semester->school_year_id)
                ->where('status', 'upcoming')
                ->where('semester_order', '>', $semester->semester_order)
                ->orderBy('semester_order')
                ->first();

            if (!$nextUpcoming) {
                return redirect()->route('admin.semesters.index')
                    ->with('error', 'Cannot delete the active semester: no upcoming semester found to take its place.');
            }

            DB::transaction(function () use ($semester, $nextUpcoming) {
                $nextUpcoming->update(['status' => 'active']);
                $semester->forceDelete();
            });
        } else {
            $semester->forceDelete();
        }

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }

    public function setActive(Semester $semester)
    {
        DB::transaction(function () use ($semester) {
            $previousActiveIds = Semester::where('status', 'active')
                ->where('id', '!=', $semester->id)
                ->pluck('id');

            Semester::where('status', 'active')->update(['status' => 'completed']);
            $semester->update(['status' => 'active']);

            $this->completeEnrollmentsForSemesters($previousActiveIds);
        });

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester set as active.');
    }

    private function completeEnrollmentsForSemesters($semesterIds)
    {
        if (empty($semesterIds)) {
            return;
        }

        Enrollment::whereIn('semester_id', $semesterIds)
            ->where('status', 'enrolled')
            ->update(['status' => 'completed']);
    }
}
