<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TorRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'generated_by',
        'document_number',
        'cumulative_gwa',
        'all_grades_data',
        'pdf_path',
        'generated_at',
        'tor_type',
    ];

    protected $casts = [
        'cumulative_gwa' => 'decimal:2',
        'all_grades_data' => 'array',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Helper Methods
    public function getDocumentTitle()
    {
        $type = $this->tor_type === 'complete' ? 'Complete' : 'Partial';
        return $type . ' Transcript of Records';
    }

    public function isComplete()
    {
        return $this->tor_type === 'complete';
    }

    public function hasFile()
    {
        return !empty($this->pdf_path) && file_exists(storage_path('app/' . $this->pdf_path));
    }

    // Scopes
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeComplete($query)
    {
        return $query->where('tor_type', 'complete');
    }

    public function scopePartial($query)
    {
        return $query->where('tor_type', 'partial');
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('generated_at', 'desc')->limit($limit);
    }
}
