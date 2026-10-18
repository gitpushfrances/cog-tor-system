<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsActivity;

class PreviousSchoolRecord extends Model
{
    use RecordsActivity;

    public const SEMESTERS = [1 => '1st Semester', 2 => '2nd Semester', 3 => 'Summer'];

    protected $fillable = [
        'student_id',
        'school_name',
        'from_year_level',
        'from_semester',
        'to_year_level',
        'to_semester',
        'uploaded_by',
    ];

    protected $casts = [
        'from_year_level' => 'integer',
        'from_semester'   => 'integer',
        'to_year_level'   => 'integer',
        'to_semester'     => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function pages()
    {
        return $this->hasMany(PreviousSchoolRecordPage::class)->orderBy('sort_order');
    }

    /** Chronological position on the TOR: year level first, then semester (1st, 2nd, Summer). */
    public function startKey(): int
    {
        return $this->from_year_level * 10 + $this->from_semester;
    }

    public function endKey(): int
    {
        return $this->to_year_level * 10 + $this->to_semester;
    }

    /** e.g. "2nd Year, 1st Semester – 2nd Semester" or "2nd Year 2nd Semester – 3rd Year 1st Semester". */
    public function coverageLabel(): string
    {
        $from = self::SEMESTERS[$this->from_semester] ?? '';
        $to   = self::SEMESTERS[$this->to_semester] ?? '';

        if ($this->from_year_level === $this->to_year_level) {
            return self::yearLabel($this->from_year_level) . ', ' . ($from === $to ? $from : "{$from} – {$to}");
        }

        return self::yearLabel($this->from_year_level) . " {$from} – " . self::yearLabel($this->to_year_level) . " {$to}";
    }

    public static function yearLabel(int $level): string
    {
        $suffix = match ($level) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };

        return "{$level}{$suffix} Year";
    }
}
