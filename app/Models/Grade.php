<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsActivity;
class Grade extends Model
{
    use RecordsActivity;
    use HasFactory;

    protected $fillable = [
    'enrollment_id',
    'faculty_id',
    'grade',
    're_exam_grade',
    'status',
    'remarks',
];

protected $casts = [
    'grade' => 'decimal:2',
    're_exam_grade' => 'decimal:2',
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

    /** TOR grading legend: 3.6-5.0 is Failed (3.1-3.5 is Conditional). */
    public const FAILING_ABOVE = 3.5;

    /**
     * Credit printed on the TOR (panel: a failed grade earns 0 credit).
     * The re-exam grade decides when there is one, otherwise the final grade.
     */
    public static function earnedCredit($units, $grade, $reExam = null): float
    {
        $decisive = $reExam ?? $grade;

        return $decisive !== null && (float) $decisive > self::FAILING_ABOVE ? 0.0 : (float) $units;
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
