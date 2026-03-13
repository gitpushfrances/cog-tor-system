<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'grade_id',
        'submitted_by',
        'reviewed_by',
        'finalized_by',
        'submitted_at',
        'reviewed_at',
        'finalized_at',
        'dean_action',
        'dean_remarks',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    // Relationships
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function finalizedBy()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    // Helper Methods
    public function isApproved()
    {
        return $this->dean_action === 'approved_by_dean';
    }

    public function isRejected()
    {
        return $this->dean_action === 'rejected';
    }

    public function isPendingReview()
    {
        return $this->submitted_at && !$this->reviewed_at;
    }

    public function isReviewed()
    {
        return $this->reviewed_at !== null;
    }

    public function isFinalized()
    {
        return $this->finalized_at !== null;
    }

    // Scopes
    public function scopePendingReview($query)
    {
        return $query->whereNotNull('submitted_at')
                    ->whereNull('reviewed_at');
    }

    public function scopeApproved($query)
    {
        return $query->where('dean_action', 'approved_by_dean');
    }

    public function scopeRejected($query)
    {
        return $query->where('dean_action', 'rejected');
    }

    public function scopeFinalized($query)
    {
        return $query->whereNotNull('finalized_at');
    }
}
