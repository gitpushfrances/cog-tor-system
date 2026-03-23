<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
    'enrollment_id',
    'faculty_id',
    'grade',
    'status',
    'remarks',
];

protected $casts = [
    'grade' => 'decimal:2',
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
        return $this->status === 'pending_head_of_department_review';
    }

    public function isApprovedByHeadOfDepartment()
    {
        return $this->status === 'approved_by_head_of_department';
    }

    public function isFinalized()
    {
        return $this->status === 'finalized';
    }

    public function isPassing()
    {
        return $this->grade < 5.0; // Below 5.0 is passing
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending_head_of_department_review');
    }

    public function scopeApprovedByHeadOfDepartment($query)
    {
        return $query->where('status', 'approved_by_head_of_department');
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
