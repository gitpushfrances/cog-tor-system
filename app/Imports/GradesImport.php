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
        // Skip rows with no percentage
        if (!isset($row['percentage_0_100']) || $row['percentage_0_100'] === '') {
            return null;
        }

        $enrollment = Enrollment::where('id', $row['enrollment_id'])
            ->where('subject_id', $this->subjectId)
            ->first();

        if (!$enrollment) return null;

        // Only allow editing pending grades
        $existing = Grade::where('enrollment_id', $enrollment->id)->first();
        if ($existing && $existing->status !== 'pending') return null;

        $gradeValue = Grade::convertToGrade((float) $row['percentage_0_100']);

        Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'faculty_id'  => $this->facultyId,
                'grade'       => $gradeValue,
                'percentage'  => (float) $row['percentage_0_100'],
                'status'      => 'pending',
                'remarks'     => isset($row['remarks']) ? trim($row['remarks']) : null,
            ]
        );

        return null; // Return null because we handle persistence manually above
    }

    public function rules(): array
    {
        return [
            'enrollment_id'    => 'required|integer|exists:enrollments,id',
            'percentage_0_100' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
