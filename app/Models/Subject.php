<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'faculty_id',
        'code',
        'name',
        'description',
        'units',
        'year_level',
        'semester',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFullName()
    {
        return $this->code . ' - ' . $this->name;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByFaculty($query, $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }

    public function scopeByYearLevel($query, $yearLevel)
    {
        return $query->where('year_level', $yearLevel);
    }
}
