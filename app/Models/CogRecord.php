<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CogRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'semester_id',
        'generated_by',
        'document_number',
        'semester_gwa',
        'grade_data',
        'pdf_path',
        'generated_at',
    ];

    protected $casts = [
        'semester_gwa' => 'decimal:2',
        'grade_data' => 'array',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Helper Methods
    public function getDocumentTitle()
    {
        return 'Certificate of Grades - ' . $this->semester->getFullName();
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

    public function scopeBySemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('generated_at', 'desc')->limit($limit);
    }
}
