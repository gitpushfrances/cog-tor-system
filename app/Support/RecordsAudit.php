<?php

namespace App\Support;

use App\Models\CogRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TorRecord;
use Illuminate\Support\Collection;

/**
 * Finds data-quality problems (records:audit) and the subset that can be fixed
 * without judgement (records:fix-safe). Read-only: nothing here writes.
 */
class RecordsAudit
{
    public const SEMESTERS = ['1st Semester', '2nd Semester', 'Summer'];

    /** Canonical semester for an obvious spelling ("1st sem", "FIRST SEMESTER", "midyear"), or null if unclear. */
    public static function canonicalSemester(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (in_array($value, self::SEMESTERS, true)) {
            return $value;
        }

        $key = preg_replace('/[^a-z0-9]/', '', strtolower($value));

        return match (true) {
            (bool) preg_match('/^(1|1st|first)(sem|semester)?$/', $key)  => '1st Semester',
            (bool) preg_match('/^(2|2nd|second)(sem|semester)?$/', $key) => '2nd Semester',
            (bool) preg_match('/^(summer|summerterm|midyear|midyr)(sem|semester|term)?$/', $key) => 'Summer',
            default => null,
        };
    }

    /** Trims and collapses repeated spaces; null stays null. */
    public static function tidy(?string $value): ?string
    {
        return $value === null ? null : trim(preg_replace('/\s+/u', ' ', $value));
    }

    private static function untidy(?string $value): bool
    {
        return $value !== null && $value !== self::tidy($value);
    }

    private static function key(?string $value): string
    {
        return mb_strtolower(self::tidy((string) $value));
    }

    /** @return array<string, array{title: string, columns: array, rows: Collection, fixable: bool}> */
    public function findings(): array
    {
        $subjects = Subject::with('course')->orderBy('id')->get();
        $students = Student::with('course')->orderBy('id')->get();

        return [
            'subject_semester' => [
                'title'   => 'Subjects with a semester that is not "1st Semester", "2nd Semester" or "Summer"',
                'columns' => ['ID', 'Course', 'Code', 'Semester now', 'Safe fix'],
                'fixable' => true,
                'rows'    => $subjects->filter(fn ($s) => ! in_array($s->semester, self::SEMESTERS, true))
                    ->map(fn ($s) => [$s->id, $s->course->code ?? '?', $s->code, $s->semester ?? '(empty)', self::canonicalSemester($s->semester) ?? 'fix by hand in Admin']),
            ],
            'subject_year' => [
                'title'   => 'Subjects with a year level outside 1–5',
                'columns' => ['ID', 'Course', 'Code', 'Year level'],
                'fixable' => false,
                'rows'    => $subjects->filter(fn ($s) => $s->year_level < 1 || $s->year_level > 5)
                    ->map(fn ($s) => [$s->id, $s->course->code ?? '?', $s->code, $s->year_level]),
            ],
            'spaces' => [
                'title'   => 'Extra spaces in names or codes',
                'columns' => ['Record', 'ID', 'Field', 'Value now'],
                'fixable' => true,
                'rows'    => $this->spacingRows($subjects, $students),
            ],
            'student_duplicates' => [
                'title'   => 'Possible duplicate students (same name and birth date)',
                'columns' => ['IDs', 'Student numbers', 'Name', 'Birth date'],
                'fixable' => false,
                'rows'    => $students->groupBy(fn ($s) => self::key($s->last_name) . '|' . self::key($s->first_name) . '|' . $s->birth_date?->format('Y-m-d'))
                    ->filter(fn ($group) => $group->count() > 1)
                    ->map(fn ($group) => [
                        $group->pluck('id')->implode(', '),
                        $group->pluck('student_number')->implode(', '),
                        self::tidy($group->first()->last_name) . ', ' . self::tidy($group->first()->first_name),
                        $group->first()->birth_date?->format('Y-m-d'),
                    ])->values(),
            ],
            'student_birth' => [
                'title'   => 'Students with a birth date before 1950 or not before today',
                'columns' => ['ID', 'Student number', 'Name', 'Birth date'],
                'fixable' => false,
                'rows'    => $students->filter(fn ($s) => $s->birth_date && ($s->birth_date->lt('1950-01-01') || ! $s->birth_date->lt(today())))
                    ->map(fn ($s) => [$s->id, $s->student_number, $s->getFullName(), $s->birth_date->format('Y-m-d')]),
            ],
            'student_email' => [
                'title'   => 'Students with an invalid email address',
                'columns' => ['ID', 'Student number', 'Name', 'Email'],
                'fixable' => false,
                'rows'    => $students->filter(fn ($s) => ! filter_var($s->email, FILTER_VALIDATE_EMAIL))
                    ->map(fn ($s) => [$s->id, $s->student_number, $s->getFullName(), $s->email]),
            ],
            'student_number' => [
                'title'   => 'Student numbers that do not look like 2024-00001 or 23-1499',
                'columns' => ['ID', 'Student number', 'Name'],
                'fixable' => false,
                'rows'    => $students->filter(fn ($s) => ! preg_match('/^\d{2,4}-\d{3,6}$/', $s->student_number))
                    ->map(fn ($s) => [$s->id, $s->student_number, $s->getFullName()]),
            ],
            'student_year' => [
                'title'   => 'Students with a year level outside 1–5',
                'columns' => ['ID', 'Student number', 'Name', 'Year level'],
                'fixable' => false,
                'rows'    => $students->filter(fn ($s) => $s->year_level < 1 || $s->year_level > 5)
                    ->map(fn ($s) => [$s->id, $s->student_number, $s->getFullName(), $s->year_level]),
            ],
            'enrollment_semester' => [
                'title'   => 'Enrollments whose subject belongs to a different semester',
                'columns' => ['Enrollment ID', 'Student', 'Subject', 'Subject semester', 'Enrolled in'],
                'fixable' => false,
                'rows'    => Enrollment::with(['student', 'subject', 'semester.schoolYear'])->get()
                    ->filter(fn ($e) => $e->subject && $e->semester
                        && in_array($e->subject->semester, self::SEMESTERS, true)
                        && $e->subject->semester !== $e->semester->semester_name)
                    ->map(fn ($e) => [$e->id, $e->student?->student_number, $e->subject->code, $e->subject->semester, $e->semester->semester_name . ' ' . ($e->semester->schoolYear->year_code ?? '')]),
            ],
            'grade_range' => [
                'title'   => 'Grades outside 1.00–5.00',
                'columns' => ['Grade ID', 'Student', 'Subject', 'Grade', 'Re-exam'],
                'fixable' => false,
                'rows'    => Grade::with('enrollment.student', 'enrollment.subject')->get()
                    ->filter(fn ($g) => $g->grade < 1 || $g->grade > 5 || ($g->re_exam_grade !== null && ($g->re_exam_grade < 1 || $g->re_exam_grade > 5)))
                    ->map(fn ($g) => [$g->id, $g->enrollment?->student?->student_number, $g->enrollment?->subject?->code, $g->grade, $g->re_exam_grade]),
            ],
            'grade_submission' => [
                'title'   => 'Finalized grades without a finalized submission record',
                'columns' => ['Grade ID', 'Student', 'Subject'],
                'fixable' => false,
                'rows'    => Grade::with('enrollment.student', 'enrollment.subject')->where('status', 'finalized')
                    ->whereDoesntHave('submission', fn ($q) => $q->whereNotNull('finalized_at'))->get()
                    ->map(fn ($g) => [$g->id, $g->enrollment?->student?->student_number, $g->enrollment?->subject?->code]),
            ],
            'current_documents' => [
                'title'   => 'More than one "current" COG (per semester) or TOR for a student',
                'columns' => ['Type', 'Student ID', 'Semester ID', 'Current record IDs', 'Safe fix'],
                'fixable' => true,
                'rows'    => $this->duplicateCurrentDocuments()->map(fn ($d) => [$d['type'], $d['student_id'], $d['semester_id'] ?? '—', implode(', ', $d['ids']), 'keep #' . max($d['ids']) . ' as current']),
            ],
        ];
    }

    private function spacingRows(Collection $subjects, Collection $students): Collection
    {
        $rows = collect();
        $check = function (string $label, Collection $records, array $fields) use ($rows) {
            foreach ($records as $record) {
                foreach ($fields as $field) {
                    if (self::untidy($record->{$field})) {
                        $rows->push([$label, $record->id, $field, '"' . $record->{$field} . '"']);
                    }
                }
            }
        };

        $check('Student', $students, self::STUDENT_TEXT_FIELDS);
        $check('Subject', $subjects, ['code', 'name']);
        $check('Course', Course::all(), ['code', 'name']);
        $check('Department', Department::all(), ['code', 'name']);

        return $rows;
    }

    public const STUDENT_TEXT_FIELDS = ['first_name', 'middle_name', 'last_name', 'suffix', 'address', 'place_of_birth', 'elementary_school', 'high_school'];

    /** @return Collection<int, array{type: string, model: string, student_id: int, semester_id: ?int, ids: array}> */
    public function duplicateCurrentDocuments(): Collection
    {
        $cogs = CogRecord::where('is_current', true)->get(['id', 'student_id', 'semester_id'])
            ->groupBy(fn ($r) => $r->student_id . '-' . $r->semester_id)->filter(fn ($g) => $g->count() > 1)
            ->map(fn ($g) => ['type' => 'COG', 'model' => CogRecord::class, 'student_id' => $g->first()->student_id, 'semester_id' => $g->first()->semester_id, 'ids' => $g->pluck('id')->all()]);

        $tors = TorRecord::where('is_current', true)->get(['id', 'student_id'])
            ->groupBy('student_id')->filter(fn ($g) => $g->count() > 1)
            ->map(fn ($g) => ['type' => 'TOR', 'model' => TorRecord::class, 'student_id' => $g->first()->student_id, 'semester_id' => null, 'ids' => $g->pluck('id')->all()]);

        return $cogs->values()->concat($tors->values());
    }
}
