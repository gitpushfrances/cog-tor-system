<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Exports\MasterlistExport;
use App\Imports\MasterlistImport;
use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\Semester;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function studentTemplate()
    {
        $headers = [
            'Student Number', 'First Name', 'Middle Name', 'Last Name',
            'Suffix', 'Birth Date (YYYY-MM-DD)', 'Gender (Male/Female)',
            'Email', 'Phone', 'Address', 'Year Level', 'Course Code', 'Status',
        ];

        $firstCourseCode = Course::where('status', 'active')->value('code') ?: 'BSIT';

        $callback = function () use ($headers, $firstCourseCode) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                '# Use format: YYYY-NNNNN', 'First Name', 'Middle Name (optional)', 'Last Name',
                'Suffix (optional)', 'YYYY-MM-DD e.g. 2003-01-15', 'Male or Female',
                'Email address', '09XXXXXXXXX', 'Full address', '1 to 5 only',
                $firstCourseCode, 'active / inactive / graduated / dropped',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }

    public function exportStudents()
    {
        return Excel::download(
            new StudentsExport(null),
            'students_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $import = new StudentsImport(null);
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();
        $errors   = $import->errors();

        if ($failures->isNotEmpty() || count($errors) > 0) {
            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return redirect()->route('registrar.students.index')
                ->with('import_errors', $messages)
                ->with('warning', count($messages) . ' row(s) had errors and were skipped.');
        }

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Students imported successfully.');
    }

    /** Masterlist of one course + year level + school year, with finalized grades, in the masterlist grid layout. */
    public function exportMasterlist(Request $request)
    {
        $validator = validator($request->all(), [
            'school_year_id' => 'required|exists:school_years,id',
            'course_id'      => 'required|exists:courses,id',
            'year_level'     => 'required|integer|between:1,5',
        ]);
        if ($validator->fails()) {
            return back()->with('error', 'Masterlist export: pick a School Year, Course and Year Level.');
        }

        $schoolYear = SchoolYear::findOrFail($request->input('school_year_id'));
        $course     = Course::findOrFail($request->input('course_id'));
        $yearLevel  = (int) $request->input('year_level');

        $export = new MasterlistExport($schoolYear, $course, $yearLevel);
        if ($export->studentCount() === 0) {
            return back()->with('error', "No students are enrolled in {$course->code} Year {$yearLevel} subjects for SY {$schoolYear->year_code}. Nothing to export.");
        }

        activity('document')
            ->causedBy($request->user())
            ->event('exported')
            ->withProperties([
                'course'      => $course->code,
                'year_level'  => $yearLevel,
                'school_year' => $schoolYear->year_code,
                'students'    => $export->studentCount(),
            ])
            ->log("Exported masterlist {$course->code} Year {$yearLevel}, SY {$schoolYear->year_code}");

        return Excel::download($export, "masterlist_{$course->code}_Y{$yearLevel}_{$schoolYear->year_code}.xlsx");
    }

    public function masterlistTemplate()
    {
        return Excel::download(
            new MasterlistExport(),
            'masterlist_template.xlsx'
        );
    }

    public function importMasterlist(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $import = new MasterlistImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('registrar.encode-grades')
            ->with('import_report', [
                'imported'  => $import->getImportedCount(),
                'successes' => $import->getSuccesses(),
                'warnings'  => $import->getWarnings(),
                'errors'    => $import->getErrors(),
            ]);
    }
}
