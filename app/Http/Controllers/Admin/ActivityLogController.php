<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CogRecord;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeSubmission;
use App\Models\Semester;
use App\Models\TorRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/** Admin → Activity Log: read-only history of changes, documents, backups and sign-ins. */
class ActivityLogController extends Controller
{
    /** Relations the record labels need, loaded in a few queries instead of one per row. */
    public const SUBJECT_RELATIONS = [
        Enrollment::class      => ['student', 'subject'],
        Grade::class           => ['enrollment.student', 'enrollment.subject'],
        GradeSubmission::class => ['grade.enrollment.student', 'grade.enrollment.subject'],
        Semester::class        => ['schoolYear'],
        CogRecord::class       => ['student'],
        TorRecord::class       => ['student'],
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'user'      => 'nullable|integer',
            'type'      => 'nullable|string|max:50',
            'event'     => 'nullable|string|max:50',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        $activities = Activity::query()
            ->with('causer')
            ->when($filters['user'] ?? null, fn ($q, $id) => $q->where('causer_type', User::class)->where('causer_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('log_name', $type))
            ->when($filters['event'] ?? null, fn ($q, $event) => $q->where('event', $event))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $activities->getCollection()->loadMorph('subject', self::SUBJECT_RELATIONS);

        return view('admin.activity-log.index', [
            'activities' => $activities,
            'users'      => User::orderBy('name')->get(['id', 'name']),
            'types'      => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name')->filter()->values(),
            'events'     => Activity::query()->distinct()->orderBy('event')->pluck('event')->filter()->values(),
            'filters'    => $filters,
        ]);
    }

    /** Human-readable name of the record an entry is about, even after the record was deleted. */
    public static function recordLabel(Activity $activity): string
    {
        $s = $activity->subject;

        $label = match (true) {
            $s === null                         => null,
            $s instanceof \App\Models\Student   => $s->getFullName() . ' (' . $s->student_number . ')',
            $s instanceof Enrollment            => ($s->student?->getFullName() ?? '?') . ' · ' . ($s->subject?->code ?? '?'),
            $s instanceof Grade                 => ($s->enrollment?->student?->getFullName() ?? '?') . ' · ' . ($s->enrollment?->subject?->code ?? '?'),
            $s instanceof GradeSubmission       => ($s->grade?->enrollment?->student?->getFullName() ?? '?') . ' · ' . ($s->grade?->enrollment?->subject?->code ?? '?'),
            $s instanceof Semester              => $s->semester_name . ' ' . ($s->schoolYear?->year_code ?? ''),
            $s instanceof CogRecord, $s instanceof TorRecord => $s->document_number . ' · ' . ($s->student?->getFullName() ?? '?'),
            default => $s->getAttribute('code') ?? $s->getAttribute('name') ?? $s->getAttribute('school_name')
                ?? $s->getAttribute('year_code') ?? null,
        };

        if ($label !== null) {
            return $label;
        }

        // Deleted record (or no record): use the values saved with the entry.
        $saved = $activity->properties['old'] ?? $activity->properties['attributes'] ?? [];
        foreach (['student_number', 'code', 'name', 'school_name', 'year_code', 'semester_name', 'document_number', 'email', 'file'] as $key) {
            if (! empty($saved[$key])) {
                return $saved[$key] . ($activity->subject_id ? ' (deleted)' : '');
            }
        }
        if (! empty($activity->properties['email'])) {
            return $activity->properties['email'];
        }

        return $activity->subject_id ? '#' . $activity->subject_id . ' (deleted)' : '—';
    }

    /** [field, old, new] rows for an update entry, or [field, value] rows for created/deleted entries. */
    public static function changes(Activity $activity): array
    {
        $new = $activity->properties['attributes'] ?? [];
        $old = $activity->properties['old'] ?? [];
        $format = fn ($v) => is_array($v) ? json_encode($v) : (is_bool($v) ? ($v ? 'yes' : 'no') : (string) ($v ?? '—'));

        $rows = [];
        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $field) {
            $rows[] = [str_replace('_', ' ', $field), array_key_exists($field, $old) ? $format($old[$field]) : null, array_key_exists($field, $new) ? $format($new[$field]) : null];
        }
        foreach ($activity->properties->except(['attributes', 'old'])->all() as $field => $value) {
            $rows[] = [str_replace('_', ' ', $field), null, $format($value)];
        }

        return $rows;
    }
}
