<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;

class Student extends Model
{
    use RecordsActivity {
        getActivitylogOptions as protected baseActivitylogOptions;
    }
    use HasFactory;

    /** Civil status choices on the Student Information Sheet. */
    public const CIVIL_STATUSES = ['Single', 'Married', 'Widowed', 'Legally Separated', 'Annulled'];

    /**
     * Student Information Sheet (ESSU-ACAD-203.b) fields: column => max length.
     * Required on Add/Edit ("N/A" or "None" is accepted) and kept out of the activity log values.
     * Elementary and Senior High school names/years are the existing elementary_* / high_school_* columns.
     */
    public const SIS_FIELDS = [
        'civil_status'          => 30,
        'home_phone'            => 30,
        'address_street'        => 150,
        'address_barangay'      => 100,
        'address_municipality'  => 100,
        'address_province'      => 100,
        'address_zip'           => 10,
        'father_name'           => 150,
        'father_contact'        => 30,
        'mother_name'           => 150,
        'mother_contact'        => 30,
        'guardian_name'         => 150,
        'guardian_address'      => 255,
        'guardian_contact'      => 30,
        'emergency_name'        => 150,
        'emergency_relation'    => 50,
        'emergency_address'     => 255,
        'emergency_contact'     => 30,
        'elementary_address'    => 255,
        'elementary_period'     => 50,
        'elementary_honors'     => 150,
        'junior_high_school'    => 255,
        'junior_high_address'   => 255,
        'junior_high_period'    => 50,
        'junior_high_honors'    => 150,
        'high_school_address'   => 255,
        'high_school_period'    => 50,
        'high_school_honors'    => 150,
        'college_school'        => 255,
        'college_address'       => 255,
        'college_period'        => 50,
        'college_honors'        => 150,
        'graduate_school'       => 255,
        'graduate_address'      => 255,
        'graduate_period'       => 50,
        'graduate_honors'       => 150,
        'post_graduate_school'  => 255,
        'post_graduate_address' => 255,
        'post_graduate_period'  => 50,
        'post_graduate_honors'  => 150,
    ];

    protected $fillable = [
        'student_number',
        'course_id',
        'major_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'gender',
        'email',
        'phone',
        'address',
        'place_of_birth',
        'elementary_school',
        'elementary_graduation_year',
        'high_school',
        'high_school_graduation_year',
        'year_level',
        'section',
        'student_type',
        'status',
        'civil_status',
        'home_phone',
        'address_street',
        'address_barangay',
        'address_municipality',
        'address_province',
        'address_zip',
        'father_name',
        'father_contact',
        'mother_name',
        'mother_contact',
        'guardian_name',
        'guardian_address',
        'guardian_contact',
        'emergency_name',
        'emergency_relation',
        'emergency_address',
        'emergency_contact',
        'elementary_address',
        'elementary_period',
        'elementary_honors',
        'junior_high_school',
        'junior_high_address',
        'junior_high_period',
        'junior_high_honors',
        'high_school_address',
        'high_school_period',
        'high_school_honors',
        'college_school',
        'college_address',
        'college_period',
        'college_honors',
        'graduate_school',
        'graduate_address',
        'graduate_period',
        'graduate_honors',
        'post_graduate_school',
        'post_graduate_address',
        'post_graduate_period',
        'post_graduate_honors',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /** Family, contact and school-history details are personal data: never written to the activity log. */
    public function getActivitylogOptions(): LogOptions
    {
        return $this->baseActivitylogOptions()->logExcept(array_keys(self::SIS_FIELDS));
    }

    /** Sections are stored trimmed and in capitals ("a " -> "A"); blank means no section. */
    public function setSectionAttribute($value): void
    {
        $value = trim((string) $value);
        $this->attributes['section'] = $value === '' ? null : mb_strtoupper($value);
    }

    /**
     * 2x2 ID picture as a data URI (used by the TOR PDF, its preview and the TOR modal).
     * Stored privately under storage/app; never served from public/.
     */
    public function photoDataUri(): ?string
    {
        if (! $this->photo_path || ! Storage::disk('local')->exists($this->photo_path)) {
            return null;
        }

        return 'data:image/jpeg;base64,' . base64_encode(Storage::disk('local')->get($this->photo_path));
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }

    public function cogRecords()
    {
        return $this->hasMany(CogRecord::class);
    }

    public function previousSchoolRecords()
    {
        return $this->hasMany(PreviousSchoolRecord::class);
    }

    /** Only Transferee and Irregular students may have scanned records from a previous school. */
    public function canHavePreviousSchoolRecords(): bool
    {
        return in_array($this->student_type, ['Transferee', 'Irregular'], true);
    }

    public function torRecords()
    {
        return $this->hasMany(TorRecord::class);
    }

    // Helper Methods
    public function getFullName()
    {
        $name = $this->first_name . ' ';

        if ($this->middle_name) {
            $name .= substr($this->middle_name, 0, 1) . '. ';
        }

        $name .= $this->last_name;

        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }

        return $name;
    }

    /**
     * "LASTNAME, First M. Suffix" — matches the format on official
     * ESSU documents (COG/TOR). Uppercase is applied via CSS in the
     * PDF template, not here, so this stays reusable elsewhere.
     */
    public function getFormalName()
    {
        $name = $this->last_name . ', ' . $this->first_name;

        if ($this->middle_name) {
            $name .= ' ' . substr($this->middle_name, 0, 1) . '.';
        }

        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }

        return $name;
    }

    public function getYearLevelWord()
    {
        return [
            1 => 'first',
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
        ][$this->year_level] ?? $this->year_level . 'th';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isGraduated()
    {
        return $this->status === 'graduated';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByYearLevel($query, $yearLevel)
    {
        return $query->where('year_level', $yearLevel);
    }
}
