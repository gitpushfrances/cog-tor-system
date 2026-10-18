<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\PreviousSchoolRecord;
use App\Models\PreviousSchoolRecordPage;
use App\Models\Student;
use App\Support\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Scanned TOR/COG pages from a Transferee's or Irregular student's previous school.
 * Each record covers a year/semester range and is printed in that position on the TOR.
 */
class PreviousSchoolRecordController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['destroy', 'destroyPage']);
    }

    private const MAX_PAGES = 15;

    public function index(Student $student): JsonResponse
    {
        $records = $student->previousSchoolRecords()->with('pages')->get()
            ->sortBy(fn ($r) => $r->startKey())
            ->values()
            ->map(fn (PreviousSchoolRecord $r) => $this->present($r));

        return response()->json([
            'eligible' => $student->canHavePreviousSchoolRecords(),
            'records'  => $records,
        ]);
    }

    public function store(Request $request, Student $student): JsonResponse
    {
        abort_unless($student->canHavePreviousSchoolRecords(), 403, 'Only Transferee and Irregular students can have previous school records.');

        $validated = $request->validate([
            'school_name'     => 'required|string|max:255',
            'from_year_level' => 'required|integer|between:1,5',
            'from_semester'   => 'required|integer|in:1,2,3',
            'to_year_level'   => 'required|integer|between:1,5',
            'to_semester'     => 'required|integer|in:1,2,3',
            'images'          => 'required|array|min:1|max:' . self::MAX_PAGES,
            'images.*'        => 'image|mimes:jpg,jpeg,png|max:10240',
        ], $this->messages());

        $record = new PreviousSchoolRecord($validated);

        if ($record->endKey() < $record->startKey()) {
            return response()->json(['message' => 'The "To" year and semester cannot be earlier than the "From" year and semester.'], 422);
        }

        if (! $request->boolean('confirm_overlap') && ($count = $this->overlappingEssuGrades($student, $record)) > 0) {
            return response()->json([
                'overlap' => true,
                'message' => "This student already has {$count} finalized ESSU grade(s) in " . $record->coverageLabel() . '. Save anyway?',
            ], 409);
        }

        if (! ImageProcessor::available()) {
            return response()->json(['message' => 'The PHP GD extension is not enabled on this server.'], 422);
        }

        $record->student_id  = $student->id;
        $record->uploaded_by = $request->user()->id;

        $stored = [];
        try {
            DB::transaction(function () use ($record, $request, &$stored) {
                $record->save();
                $this->savePages($record, $request->file('images'), 0, $stored);
            });
        } catch (\RuntimeException $e) {
            Storage::disk('local')->delete($stored);

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Previous school record saved.', 'record' => $this->present($record->load('pages'))], 201);
    }

    public function addPages(Request $request, PreviousSchoolRecord $record): JsonResponse
    {
        abort_unless($record->student->canHavePreviousSchoolRecords(), 403, 'Only Transferee and Irregular students can have previous school records.');

        $existing = $record->pages()->count();

        $request->validate([
            'images'   => 'required|array|min:1|max:' . (self::MAX_PAGES - $existing),
            'images.*' => 'image|mimes:jpg,jpeg,png|max:10240',
        ], $this->messages() + ['images.max' => 'A record can have at most ' . self::MAX_PAGES . ' images.']);

        $stored = [];
        try {
            DB::transaction(function () use ($record, $request, $existing, &$stored) {
                $this->savePages($record, $request->file('images'), $existing, $stored);
            });
        } catch (\RuntimeException $e) {
            Storage::disk('local')->delete($stored);

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Images added.', 'record' => $this->present($record->load('pages'))]);
    }

    public function destroy(PreviousSchoolRecord $record): JsonResponse
    {
        abort_unless($record->student->canHavePreviousSchoolRecords(), 403, 'Only Transferee and Irregular students can have previous school records.');

        $paths = $record->pages()->pluck('path')->all();
        $record->delete();
        Storage::disk('local')->delete($paths);

        return response()->json(['message' => 'Previous school record deleted.']);
    }

    public function destroyPage(PreviousSchoolRecordPage $page): JsonResponse
    {
        $record = $page->record;
        abort_unless($record->student->canHavePreviousSchoolRecords(), 403, 'Only Transferee and Irregular students can have previous school records.');

        if ($record->pages()->count() <= 1) {
            return response()->json(['message' => 'A record needs at least one image. Delete the whole record instead.'], 422);
        }

        $page->delete();
        Storage::disk('local')->delete($page->path);

        return response()->json(['message' => 'Image removed.', 'record' => $this->present($record->load('pages'))]);
    }

    public function image(PreviousSchoolRecordPage $page)
    {
        abort_unless(Storage::disk('local')->exists($page->path), 404);

        return response()->file(Storage::disk('local')->path($page->path), ['Content-Type' => 'image/jpeg']);
    }

    /** Resize each upload and store it privately; collects stored paths so a failure can clean up. */
    private function savePages(PreviousSchoolRecord $record, array $files, int $offset, array &$stored): void
    {
        foreach (array_values($files) as $i => $file) {
            $image = ImageProcessor::fitJpeg($file->getRealPath(), 2000);

            if ($image === null) {
                throw new \RuntimeException('"' . $file->getClientOriginalName() . '" could not be read. Use JPG or PNG files.');
            }

            $path = 'previous-school/' . $record->student_id . '/' . $record->id . '-' . Str::random(12) . '.jpg';
            Storage::disk('local')->put($path, $image['jpeg']);
            $stored[] = $path;

            $record->pages()->create([
                'path'       => $path,
                'width'      => $image['width'],
                'height'     => $image['height'],
                'sort_order' => $offset + $i + 1,
            ]);
        }
    }

    private function overlappingEssuGrades(Student $student, PreviousSchoolRecord $record): int
    {
        return Enrollment::with(['subject', 'semester'])
            ->where('student_id', $student->id)
            ->whereHas('grade', fn ($q) => $q->where('status', 'finalized'))
            ->get()
            ->filter(function ($e) use ($record) {
                $key = (int) $e->subject?->year_level * 10 + (int) $e->semester?->semester_order;

                return $key >= $record->startKey() && $key <= $record->endKey();
            })
            ->count();
    }

    private function present(PreviousSchoolRecord $record): array
    {
        return [
            'id'              => $record->id,
            'school_name'     => $record->school_name,
            'coverage'        => $record->coverageLabel(),
            'from_year_level' => $record->from_year_level,
            'from_semester'   => $record->from_semester,
            'to_year_level'   => $record->to_year_level,
            'to_semester'     => $record->to_semester,
            'pages'           => $record->pages->map(fn ($p) => [
                'id'  => $p->id,
                'url' => route('registrar.previous-school-pages.image', $p),
            ])->values(),
        ];
    }

    private function messages(): array
    {
        return [
            'images.required' => 'Select at least one scanned image.',
            'images.max'      => 'Upload at most ' . self::MAX_PAGES . ' images per record.',
            'images.*.image'  => 'Each file must be an image (JPG or PNG).',
            'images.*.mimes'  => 'Each file must be a JPG or PNG image.',
            'images.*.max'    => 'Each image must not be larger than 10 MB.',
        ];
    }
}
