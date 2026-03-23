<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class GradesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    protected $facultyId;
    protected $subjectId;

    public function __construct($facultyId, $subjectId)
    {
        $this->facultyId = $facultyId;
        $this->subjectId = $subjectId;
    }

    public function model(array $row)
    {
        // Skip rows with no grade
        if (!isset($row['grade']) || $row['grade'] === '') {
            return null;
        }

        $enrollment = Enrollment::where('id', $row['enrollment_id'])
            ->where('subject_id', $this->subjectId)
            ->first();

        if (!$enrollment) return null;

        // Only allow editing saved or rejected grades
        $existing = Grade::where('enrollment_id', $enrollment->id)->first();
        if ($existing && !in_array($existing->status, ['saved', 'rejected'])) return null;

        Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'faculty_id' => $this->facultyId,
                'grade'      => (float) $row['grade'],
                'status'     => 'saved',
                'remarks'    => isset($row['remarks']) ? trim($row['remarks']) : null,
            ]
        );

        return null;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => 'required|integer|exists:enrollments,id',
            'grade'         => 'required|numeric|min:1.00|max:5.00',
        ];
    }
}
