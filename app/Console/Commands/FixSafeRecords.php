<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Support\RecordsAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Applies only fixes that need no judgement: semester spellings, extra spaces,
 * duplicate "current" documents. Never merges, deletes or renames people.
 * Dry run by default; --apply writes and logs every change.
 */
class FixSafeRecords extends Command
{
    protected $signature = 'records:fix-safe {--apply : Write the changes (default is a dry run)}';

    protected $description = 'Fix unambiguous data problems (semester spellings, extra spaces, duplicate current COG/TOR)';

    public function handle(RecordsAudit $audit): int
    {
        [$changes, $skipped] = $this->plan($audit);
        $documents = $audit->duplicateCurrentDocuments();

        if (empty($changes) && $documents->isEmpty()) {
            $this->info('Nothing to fix.');
            $this->reportSkipped($skipped);

            return self::SUCCESS;
        }

        if ($changes) {
            $this->table(['Record', 'ID', 'Field', 'From', 'To'], array_map(
                fn ($c) => [class_basename($c['model']), $c['record']->id, $c['field'], '"' . $c['from'] . '"', '"' . $c['to'] . '"'],
                $changes
            ));
        }
        foreach ($documents as $d) {
            $keep = max($d['ids']);
            $this->line("{$d['type']} for student #{$d['student_id']}" . ($d['semester_id'] ? " (semester #{$d['semester_id']})" : '') . ": keep #{$keep} current, mark " . implode(', ', array_diff($d['ids'], [$keep])) . ' as superseded');
        }
        $this->reportSkipped($skipped);

        $count = count($changes) + $documents->count();

        if (! $this->option('apply')) {
            $this->newLine();
            $this->warn("Dry run: {$count} change(s) listed, nothing written. Run again with --apply to write them.");

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes, $documents) {
            // Eloquent saves, so each change lands in the activity log with old → new values.
            foreach ($changes as $c) {
                $c['record']->{$c['field']} = $c['to'];
                $c['record']->save();
            }

            foreach ($documents as $d) {
                $keep = max($d['ids']);
                $d['model']::whereIn('id', array_diff($d['ids'], [$keep]))->update(['is_current' => false]);
                activity('document')->event('updated')
                    ->withProperties(['type' => $d['type'], 'student_id' => $d['student_id'], 'kept_current' => $keep, 'superseded' => array_values(array_diff($d['ids'], [$keep]))])
                    ->log('Fixed duplicate current ' . $d['type']);
            }

            activity('maintenance')->event('updated')
                ->withProperties(['field_changes' => count($changes), 'documents_fixed' => $documents->count()])
                ->log('Ran records:fix-safe');
        });

        $this->info("Applied {$count} change(s). Each one is in Admin → Activity Log.");

        return self::SUCCESS;
    }

    /** @return array{0: array, 1: array} planned changes and skipped items with the reason */
    private function plan(RecordsAudit $audit): array
    {
        $changes = [];
        $skipped = [];

        foreach (Subject::orderBy('id')->get() as $subject) {
            $canonical = RecordsAudit::canonicalSemester($subject->semester);
            if ($canonical !== null && $canonical !== $subject->semester) {
                $changes[] = ['model' => Subject::class, 'record' => $subject, 'field' => 'semester', 'from' => $subject->semester, 'to' => $canonical];
            }
        }

        foreach (Student::orderBy('id')->get() as $student) {
            foreach (RecordsAudit::STUDENT_TEXT_FIELDS as $field) {
                $this->tidyField($changes, $student, $field);
            }
        }

        // Codes and names that must stay unique: skip the fix if it would collide.
        foreach (Subject::orderBy('id')->get() as $subject) {
            $this->tidyField($changes, $subject, 'name');
            $this->tidyUnique($changes, $skipped, $subject, 'code', fn ($v) => Subject::where('course_id', $subject->course_id)->where('code', $v)->whereKeyNot($subject->id)->exists());
        }
        foreach (Course::orderBy('id')->get() as $course) {
            $this->tidyField($changes, $course, 'name');
            $this->tidyUnique($changes, $skipped, $course, 'code', fn ($v) => Course::where('department_id', $course->department_id)->where('code', $v)->whereKeyNot($course->id)->exists());
        }
        foreach (Department::orderBy('id')->get() as $department) {
            $this->tidyField($changes, $department, 'name');
            $this->tidyUnique($changes, $skipped, $department, 'code', fn ($v) => Department::where('code', $v)->whereKeyNot($department->id)->exists());
        }

        return [$changes, $skipped];
    }

    private function tidyField(array &$changes, $record, string $field): void
    {
        $value = $record->{$field};
        $tidy = RecordsAudit::tidy($value);
        if ($value !== null && $tidy !== $value) {
            $changes[] = ['model' => get_class($record), 'record' => $record, 'field' => $field, 'from' => $value, 'to' => $tidy];
        }
    }

    private function tidyUnique(array &$changes, array &$skipped, $record, string $field, callable $collides): void
    {
        $value = $record->{$field};
        $tidy = RecordsAudit::tidy($value);
        if ($value === null || $tidy === $value) {
            return;
        }
        if ($collides($tidy)) {
            $skipped[] = class_basename($record) . " #{$record->id} {$field} \"{$value}\": \"{$tidy}\" already exists — fix by hand";

            return;
        }
        $changes[] = ['model' => get_class($record), 'record' => $record, 'field' => $field, 'from' => $value, 'to' => $tidy];
    }

    private function reportSkipped(array $skipped): void
    {
        foreach ($skipped as $line) {
            $this->warn('Skipped: ' . $line);
        }
    }
}
