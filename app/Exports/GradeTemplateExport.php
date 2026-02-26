<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeTemplateExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function collection()
    {
        return Enrollment::with(['student', 'grade'])
            ->where('subject_id', $this->subject->id)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Enrollment ID',        // DO NOT EDIT
            'Student Number',       // DO NOT EDIT
            'Student Name',         // DO NOT EDIT
            'Subject Code',         // DO NOT EDIT
            'Subject Name',         // DO NOT EDIT
            'Percentage (0-100)',   // FILL THIS
            'Remarks',              // OPTIONAL
        ];
    }

    public function map($enrollment): array
    {
        return [
            $enrollment->id,
            $enrollment->student->student_number,
            $enrollment->student->getFullName(),
            $this->subject->code,
            $this->subject->name,
            $enrollment->grade ? $enrollment->grade->percentage : '',
            $enrollment->grade ? $enrollment->grade->remarks : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F4E79']],
        ]);

        // Lock columns A-E (read-only instruction via color)
        $sheet->getStyle('A2:E1000')->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF2F2F2']],
        ]);

        // Highlight fillable columns
        $sheet->getStyle('F2:G1000')->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFFFFFCC']],
        ]);

        return [];
    }

    public function title(): string
    {
        return substr($this->subject->code . ' Grades', 0, 31); // Excel sheet name max 31 chars
    }
}
