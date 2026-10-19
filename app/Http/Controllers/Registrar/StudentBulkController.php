<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\StudentBulkImport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Bulk Add Students: template → upload → preview (nothing saved) → add the ticked rows.
 * The checked rows wait in the session between preview and save.
 */
class StudentBulkController extends Controller
{
    private const SESSION_KEY = 'student_bulk';

    public function create(Request $request)
    {
        $preview = null;
        if ($request->filled('preview')) {
            $stored = session(self::SESSION_KEY);
            if ($stored && $stored['token'] === $request->input('preview')) {
                $preview = $stored;
            }
        }

        return view('registrar.students.bulk', compact('preview'));
    }

    public function template()
    {
        $book = StudentBulkImport::template();

        return response()->streamDownload(
            fn () => (new Xlsx($book))->save('php://output'),
            'bulk_add_students_template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:2048',
        ], [
            'file.mimes' => 'Upload the template as .xlsx, .xls or .csv.',
            'file.max'   => 'The file must not be larger than 2 MB.',
        ]);

        $result = StudentBulkImport::parse($request->file('file')->getRealPath());
        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        $token = Str::random(16);
        session([self::SESSION_KEY => [
            'token' => $token,
            'file'  => $request->file('file')->getClientOriginalName(),
            'rows'  => $result['rows'],
        ]]);

        return redirect()->route('registrar.students.bulk', ['preview' => $token]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'  => 'required|string',
            'rows'   => 'required|array|min:1',
            'rows.*' => 'integer',
        ], ['rows.required' => 'Tick at least one row to add.']);

        $stored = session(self::SESSION_KEY);
        if (! $stored || $stored['token'] !== $request->input('token')) {
            return redirect()->route('registrar.students.bulk')->with('error', 'This preview has expired. Upload the file again.');
        }

        $wanted = array_map('intval', $request->input('rows'));
        $selected = array_values(array_filter($stored['rows'], fn ($row) => in_array($row['line'], $wanted, true)));

        // Check again against the database right before saving (someone may have added a student meanwhile).
        $checked = StudentBulkImport::check($selected);
        $added = [];
        $skipped = [];

        try {
            DB::transaction(function () use ($checked, &$added, &$skipped) {
                foreach ($checked as $row) {
                    if ($row['errors']) {
                        $skipped[] = "Row {$row['line']}: " . $row['errors'][0];
                        continue;
                    }
                    $data = Arr::only($row['data'], (new Student())->getFillable());
                    $added[] = Student::create($data)->student_number;
                }
            });
        } catch (\Throwable $e) {
            Log::error('Bulk Add Students failed, transaction rolled back', ['error' => $e->getMessage()]);

            return redirect()->route('registrar.students.bulk', ['preview' => $stored['token']])
                ->with('error', 'Something went wrong while saving. No students were added. Please try again.');
        }

        session()->forget(self::SESSION_KEY);

        if ($added) {
            activity('student')
                ->causedBy($request->user())
                ->event('bulk_added')
                ->withProperties(['file' => $stored['file'], 'added' => count($added), 'skipped' => count($skipped)])
                ->log('Bulk added ' . count($added) . ' student(s) from ' . $stored['file']);
        }

        return redirect()->route('registrar.students.index')
            ->with($added ? 'success' : 'warning', count($added) . ' student(s) added.' . ($skipped ? ' ' . count($skipped) . ' skipped.' : ''))
            ->with('bulk_skipped', $skipped);
    }
}
