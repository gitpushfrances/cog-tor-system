<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::with('schoolYear')->latest()->paginate(15);
        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        $schoolYears = SchoolYear::all();
        return view('admin.semesters.create', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'name'           => 'required|in:1st,2nd,Summer',
            'status'         => 'required|in:active,inactive',
        ]);

        if ($request->status === 'active') {
            Semester::where('status', 'active')->update(['status' => 'inactive']);
        }

        Semester::create($request->all());

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    public function edit(Semester $semester)
    {
        $schoolYears = SchoolYear::all();
        return view('admin.semesters.edit', compact('semester', 'schoolYears'));
    }

    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'name'           => 'required|in:1st,2nd,Summer',
            'status'         => 'required|in:active,inactive',
        ]);

        if ($request->status === 'active') {
            Semester::where('status', 'active')->where('id', '!=', $semester->id)->update(['status' => 'inactive']);
        }

        $semester->update($request->all());

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester updated successfully.');
    }

    public function destroy(Semester $semester)
    {
        if ($semester->enrollments()->count() > 0) {
            return redirect()->route('admin.semesters.index')
                ->with('error', 'Cannot delete semester with existing enrollments.');
        }

        $semester->delete();

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }

    public function setActive(Semester $semester)
    {
        Semester::where('status', 'active')->update(['status' => 'inactive']);
        $semester->update(['status' => 'active']);

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester set as active.');
    }
}
