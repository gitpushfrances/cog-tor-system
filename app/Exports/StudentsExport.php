<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Student::with('course')->get();
    }

    public function headings(): array
    {
        return [
            'Student Number',
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Birth Date (YYYY-MM-DD)',
            'Gender (Male/Female)',
            'Email',
            'Phone',
            'Address',
            'Year Level',
            'Course Code',
            'Status',
        ];
    }

    public function map($student): array
    {
        return [
            $student->student_number,
            $student->first_name,
            $student->middle_name,
            $student->last_name,
            $student->suffix,
            $student->birth_date ? $student->birth_date->format('Y-m-d') : '',
            $student->gender,
            $student->email,
            $student->phone,
            $student->address,
            $student->year_level,
            $student->course->code ?? '',
            $student->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F4E79']],
            ],
        ];
    }
}
