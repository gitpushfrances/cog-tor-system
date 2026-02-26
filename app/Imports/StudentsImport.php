<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    public function model(array $row)
    {
        // Find course by code
        $course = Course::where('code', trim($row['course_code']))->first();

        if (!$course) return null; // Skip if course not found

        return new Student([
            'student_number' => trim($row['student_number']),
            'first_name'     => trim($row['first_name']),
            'middle_name'    => isset($row['middle_name']) ? trim($row['middle_name']) : null,
            'last_name'      => trim($row['last_name']),
            'suffix'         => isset($row['suffix']) ? trim($row['suffix']) : null,
            'birth_date'     => $row['birth_date_yyyy_mm_dd'],
            'gender'         => trim($row['gender_malefemale']),
            'email'          => trim($row['email']),
            'phone'          => isset($row['phone']) ? trim($row['phone']) : null,
            'address'        => isset($row['address']) ? trim($row['address']) : null,
            'year_level'     => (int) $row['year_level'],
            'course_id'      => $course->id,
            'status'         => isset($row['status']) ? trim($row['status']) : 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'student_number'     => 'required|unique:students,student_number',
            'first_name'         => 'required|string|max:50',
            'last_name'          => 'required|string|max:50',
            'birth_date_yyyy_mm_dd' => 'required|date',
            'gender_malefemale'  => 'required|in:Male,Female',
            'email'              => 'required|email|unique:students,email',
            'year_level'         => 'required|integer|min:1|max:5',
            'course_code'        => 'required|string',
            'status'             => 'nullable|in:active,inactive,graduated,dropped',
        ];
    }
}
