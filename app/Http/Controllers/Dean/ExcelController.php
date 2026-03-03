<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\Course;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    // Download blank student import template (CSV)
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

        // Get course codes scoped to Dean's department
        $courseCodes = Course::where('department_id', auth()->user()->department_id)
                             ->where('status', 'active')
                             ->pluck('code')
                             ->implode(' / ');

        $callback = function () use ($headers, $courseCodes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Sample row showing valid course codes for this department
            fputcsv($file, [
                '2024-00001', 'Juan', 'Santos', 'Dela Cruz', '',
                '2004-05-15', 'Male', 'juan@example.com', '09171234567',
                'Guiuan, Eastern Samar', '1', $courseCodes ?: 'BSIT', 'active'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }

    // Export student roster scoped to Dean's department
    public function exportStudents()
    {
        $departmentId = auth()->user()->department_id;

        return Excel::download(
            new StudentsExport($departmentId),
            'students_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // Import students — validates course belongs to Dean's department
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $departmentId = auth()->user()->department_id;
        $import = new StudentsImport($departmentId);
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

            return redirect()->route('dean.students.index')
                ->with('import_errors', $messages)
                ->with('warning', count($messages) . ' row(s) had errors and were skipped.');
        }

        return redirect()->route('dean.students.index')
            ->with('success', 'Students imported successfully.');
    }
}
