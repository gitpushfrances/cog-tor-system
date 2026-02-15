<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'faculty_id',
        'grade',
        'percentage',
        'status',
        'remarks',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function submission()
    {
        return $this->hasOne(GradeSubmission::class);
    }

    // Helper Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApprovedByDean()
    {
        return $this->status === 'approved_by_dean';
    }

    public function isFinalized()
    {
        return $this->status === 'finalized';
    }

    public function isPassing()
    {
        return $this->grade < 5.0; // Below 5.0 is passing
    }

    /**
     * Convert percentage to Philippine grade scale (1.0-5.0)
     */
    public static function convertToGrade($percentage)
    {
        if ($percentage >= 97) return 1.00;
        if ($percentage >= 94) return 1.25;
        if ($percentage >= 91) return 1.50;
        if ($percentage >= 88) return 1.75;
        if ($percentage >= 85) return 2.00;
        if ($percentage >= 82) return 2.25;
        if ($percentage >= 79) return 2.50;
        if ($percentage >= 76) return 2.75;
        if ($percentage >= 75) return 3.00;
        return 5.00; // Failed
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApprovedByDean($query)
    {
        return $query->where('status', 'approved_by_dean');
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    public function scopePassing($query)
    {
        return $query->where('grade', '<', 5.0);
    }

    public function scopeFailing($query)
    {
        return $query->where('grade', '=', 5.0);
    }
}
