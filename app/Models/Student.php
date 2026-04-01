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
