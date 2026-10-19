<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use RecordsActivity;
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

    /**
     * Client rule: grades are entered or changed only in the active semester of the
     * active school year. Every other semester is closed, with no exception.
     * To work on a closed semester, Admin activates it first.
     */
    public function acceptsGrades(): bool
    {
        return $this->status === 'active' && $this->schoolYear?->status === 'active';
    }

    public function isGradeLocked(): bool
    {
        return ! $this->acceptsGrades();
    }

    public function gradesClosedMessage(): string
    {
        return "Grades for {$this->semester_name} " . ($this->schoolYear?->year_code ?? '')
            . ' are closed. Grades can be entered or changed only in the active semester of the active school year.';
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
