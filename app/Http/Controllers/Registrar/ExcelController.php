<?php

namespace App\Http\Controllers\Registrar;

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
}
