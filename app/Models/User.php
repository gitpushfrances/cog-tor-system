<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function enrollmentsCreated()
    {
        return $this->hasMany(Enrollment::class, 'enrolled_by');
    }

    public function gradesCreated()
    {
        return $this->hasMany(Grade::class, 'faculty_id');
    }

    public function submissionsCreated()
    {
        return $this->hasMany(GradeSubmission::class, 'submitted_by');
    }

    public function submissionsReviewed()
    {
        return $this->hasMany(GradeSubmission::class, 'reviewed_by');
    }

    public function submissionsFinalized()
    {
        return $this->hasMany(GradeSubmission::class, 'finalized_by');
    }

    public function cogRecordsGenerated()
    {
        return $this->hasMany(CogRecord::class, 'generated_by');
    }

    public function torRecordsGenerated()
    {
        return $this->hasMany(TorRecord::class, 'generated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    // Role Helper Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isFaculty()
    {
        return $this->role === 'faculty';
    }

    public function isDean()
    {
        return $this->role === 'dean';
    }

    public function isRegistrar()
    {
        return $this->role === 'registrar';
    }

    // Status Helper Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeFaculty($query)
    {
        return $query->where('role', 'faculty');
    }

    public function scopeDeans($query)
    {
        return $query->where('role', 'dean');
    }

    public function scopeRegistrars($query)
    {
        return $query->where('role', 'registrar');
    }
}
