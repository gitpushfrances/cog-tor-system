<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number',
        'course_id',
        'major_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'gender',
        'email',
        'phone',
        'address',
        'year_level',
        'student_type',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }

    public function cogRecords()
    {
        return $this->hasMany(CogRecord::class);
    }

    public function torRecords()
    {
        return $this->hasMany(TorRecord::class);
    }

    // Helper Methods
    public function getFullName()
    {
        $name = $this->first_name . ' ';

        if ($this->middle_name) {
            $name .= substr($this->middle_name, 0, 1) . '. ';
        }

        $name .= $this->last_name;

        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }

        return $name;
    }

    /**
     * "LASTNAME, First M. Suffix" — matches the format on official
     * ESSU documents (COG/TOR). Uppercase is applied via CSS in the
     * PDF template, not here, so this stays reusable elsewhere.
     */
    public function getFormalName()
    {
        $name = $this->last_name . ', ' . $this->first_name;

        if ($this->middle_name) {
            $name .= ' ' . substr($this->middle_name, 0, 1) . '.';
        }

        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }

        return $name;
    }

    public function getYearLevelWord()
    {
        return [
            1 => 'first',
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
        ][$this->year_level] ?? $this->year_level . 'th';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isGraduated()
    {
        return $this->status === 'graduated';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByYearLevel($query, $yearLevel)
    {
        return $query->where('year_level', $yearLevel);
    }
}
