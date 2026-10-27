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

    protected $fillable = [
        'username',
        'name',
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'password',
        'role',
        'status',
        'approved_by',
        'approved_at',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at'       => 'datetime',
        'password'          => 'hashed',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'name', 'role', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** "name" (used by screens, logs and documents) is always built from the name parts: "Juan S. Dela Cruz". */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->first_name !== null || $user->last_name !== null) {
                $user->name = self::composeName($user->first_name, $user->middle_name, $user->last_name);
            }
        });
    }

    public static function composeName(?string $first, ?string $middle, ?string $last): string
    {
        $middle = trim((string) $middle);

        return trim(preg_replace('/\s+/', ' ', trim((string) $first) . ($middle !== '' ? ' ' . mb_substr($middle, 0, 1) . '.' : '') . ' ' . trim((string) $last)));
    }

    /** Usernames are stored trimmed and in lower case; sign-in ignores case. */
    public function setUsernameAttribute($value): void
    {
        $this->attributes['username'] = mb_strtolower(trim((string) $value));
    }

    // Relationships
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

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

    public function isHeadOfDepartment()
    {
        return $this->role === 'head_of_department';
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

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    public function scopeHeadsOfDepartment($query)
    {
        return $query->where('role', 'head_of_department');
    }

    public function scopeRegistrars($query)
    {
        return $query->where('role', 'registrar');
    }
}