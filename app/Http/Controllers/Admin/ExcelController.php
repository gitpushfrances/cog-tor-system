<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    // Download blank student import template
    public function studentTemplate()
    {
        $headers = [
            'Student Number',
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Birth Date (YYYY-MM-DD)',
            'Gender (Male/Female)',
            'Email',
            'Phone',
            'Address',
            'Year Level',
            'Course Code',
            'Status',
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Sample row
            fputcsv($file, [
                '2024-00001', 'Juan', 'Santos', 'Dela Cruz', '', '2004-05-15',
                'Male', 'juan@example.com', '09171234567', 'Cebu City', '1', 'BSIT', 'active'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }

    // Export all students to Excel
    public function exportStudents()
    {
        return Excel::download(new StudentsExport, 'students_' . now()->format('Y-m-d') . '.xlsx');
    }

    // Import students from uploaded Excel/CSV
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $import = new StudentsImport;
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
            return redirect()->route('admin.students.index')
                ->with('import_errors', $messages)
                ->with('warning', count($messages) . ' row(s) had errors and were skipped.');
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Students imported successfully.');
    }
}
