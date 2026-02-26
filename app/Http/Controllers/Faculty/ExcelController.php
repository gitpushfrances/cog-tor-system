<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Exports\GradeTemplateExport;
use App\Imports\GradesImport;
use App\Models\Subject;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    // Download pre-filled grade template for a subject
    public function downloadTemplate(Subject $subject)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        return Excel::download(
            new GradeTemplateExport($subject),
            'grade_template_' . $subject->code . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // Upload filled grade template
    public function uploadGrades(Request $request, Subject $subject)
    {
        $user = auth()->user();
        if ($subject->faculty_id !== $user->id) abort(403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $import = new GradesImport($user->id, $subject->id);
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
            return redirect()->route('faculty.subjects.grades', $subject)
                ->with('import_errors', $messages)
                ->with('warning', count($messages) . ' row(s) had errors and were skipped.');
        }

        return redirect()->route('faculty.subjects.grades', $subject)
            ->with('success', 'Grades uploaded successfully from Excel.');
    }
}
