<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::withCount('semesters')->latest()->paginate(15);
        return view('admin.school-years.index', compact('schoolYears'));
    }

    public function create()
    {
        return view('admin.school-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_start' => 'required|integer|min:2000|max:2100',
            'year_end'   => 'required|integer|min:2000|max:2100|gt:year_start',
            'status'     => 'required|in:active,inactive',
        ]);

        if ($request->status === 'active') {
            SchoolYear::where('status', 'active')->update(['status' => 'upcoming']);
        }

        SchoolYear::create([
            'year_code'  => $request->year_start . '-' . $request->year_end,
            'start_date' => $request->year_start . '-06-01',
            'end_date'   => $request->year_end . '-05-31',
            'status'     => $request->status,
        ]);

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year created successfully.');
    }

    public function edit(SchoolYear $schoolYear)
    {
        return view('admin.school-years.edit', compact('schoolYear'));
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $request->validate([
            'year_start' => 'required|integer|min:2000|max:2100',
            'year_end'   => 'required|integer|min:2000|max:2100|gt:year_start',
            'status'     => 'required|in:active,inactive',
        ]);

        if ($request->status === 'active') {
            SchoolYear::where('status', 'active')
                ->where('id', '!=', $schoolYear->id)
                ->update(['status' => 'upcoming']);
        }

        $schoolYear->update([
            'year_code'  => $request->year_start . '-' . $request->year_end,
            'start_date' => $request->year_start . '-06-01',
            'end_date'   => $request->year_end . '-05-31',
            'status'     => $request->status,
        ]);

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year updated successfully.');
    }

    public function destroy(SchoolYear $schoolYear)
    {
        if ($schoolYear->semesters()->count() > 0) {
            return redirect()->route('admin.school-years.index')
                ->with('error', 'Cannot delete school year with existing semesters.');
        }

        $schoolYear->delete();

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year deleted successfully.');
    }

    public function setActive(SchoolYear $schoolYear)
    {
        SchoolYear::where('status', 'active')->update(['status' => 'upcoming']);
        $schoolYear->update(['status' => 'active']);

        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year set as active.');
    }
}
