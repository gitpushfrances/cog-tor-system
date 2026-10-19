<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use RecordsActivity;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    // Relationships
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Helper Methods
    public function majors()
    {
        return $this->hasManyThrough(Major::class, Course::class);
    }

    public function subjects()
    {
        return $this->hasManyThrough(Subject::class, Course::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
