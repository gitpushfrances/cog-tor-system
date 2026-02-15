<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_year_id',
        'semester_name',
        'semester_order',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function cogRecords()
    {
        return $this->hasMany(CogRecord::class);
    }

    // Helper Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFullName()
    {
        return $this->semester_name . ' - ' . $this->schoolYear->year_code;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'active')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
    }
}
