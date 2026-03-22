<?php

namespace App\Http\Controllers\HeadOfDepartment;

use App\Http\Controllers\Controller;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\Course;
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

        $firstCourseCode = Course::where('department_id', auth()->user()->department_id)
                                 ->where('status', 'active')
                                 ->value('code') ?: 'BSIT';

        $callback = function () use ($headers, $firstCourseCode) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Note row — explains the format, obviously not real data
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
        $departmentId = auth()->user()->department_id;

        return Excel::download(
            new StudentsExport($departmentId),
            'students_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $departmentId = auth()->user()->department_id;
        $import       = new StudentsImport($departmentId);
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

            return redirect()->route('head_of_department.students.index')
                ->with('import_errors', $messages)
                ->with('warning', count($messages) . ' row(s) had errors and were skipped.');
        }

        return redirect()->route('head_of_department.students.index')
                         ->with('success', 'Students imported successfully.');
    }
}
