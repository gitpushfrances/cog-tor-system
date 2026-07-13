<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MasterlistExport implements FromArray, WithEvents
{
    const SUBJECT_PLACEHOLDER = '(subject code)';

    protected int $subjectsPerSemester = 9;
    protected int $blankRowsPerGroup = 20;

    protected int $nameCol = 3; // C
    protected int $firstSemStartCol;
    protected int $secondSemStartCol;
    protected int $lastCol;

    // Lookup data lives far off to the right of the visible table, hidden, same sheet
    // (avoids using extra worksheets, which broke ToCollection import — it ran once per sheet)
    protected int $courseLookupCol = 60;  // BH
    protected int $subjectLookupCol = 65; // BM

    public function __construct()
    {
        $this->firstSemStartCol = $this->nameCol + 1;
        $this->secondSemStartCol = $this->firstSemStartCol + $this->subjectsPerSemester;
        $this->lastCol = $this->secondSemStartCol + $this->subjectsPerSemester - 1;
    }

    public function array(): array
    {
        return [];
    }

    protected function addListDropdown($sheet, string $cell, string $csvList, string $prompt): void
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setPromptTitle('Select a value');
        $validation->setPrompt($prompt);
        $validation->setErrorTitle('Invalid entry');
        $validation->setError('Please pick a value from the dropdown list.');
        $validation->setFormula1('"' . $csvList . '"');
    }

    protected function addGradeValidation($sheet, string $cell): void
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL);
        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setPromptTitle('Grade');
        $validation->setPrompt('Enter a numeric grade.');
        $validation->setErrorTitle('Invalid grade');
        $validation->setError('Grade must be a number.');
        $validation->setFormula1('0');
    }

    protected function addStudentNumberValidation($sheet, string $cell): void
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_CUSTOM);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setPromptTitle('Student No.');
        $validation->setPrompt('Digits and dashes only, e.g. 2024-00001');
        $validation->setErrorTitle('Invalid Student No.');
        $validation->setError('Student No. must contain digits and dashes only (e.g. 2024-00001).');
        $validation->setFormula1("=ISNUMBER(VALUE(SUBSTITUTE({$cell},\"-\",\"\")))");
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColLetter = Coordinate::stringFromColumnIndex($this->lastCol);

                // --- Hidden lookup data, same sheet, far off to the right ---
                $courseCodeCol = Coordinate::stringFromColumnIndex($this->courseLookupCol);
                $courseNameCol = Coordinate::stringFromColumnIndex($this->courseLookupCol + 1);
                $r = 1;
                foreach (Course::active()->pluck('name', 'code') as $code => $name) {
                    $sheet->setCellValue($courseCodeCol . $r, $code);
                    $sheet->setCellValue($courseNameCol . $r, $name);
                    $r++;
                }

                $subjCodeCol = Coordinate::stringFromColumnIndex($this->subjectLookupCol);
                $subjNameCol = Coordinate::stringFromColumnIndex($this->subjectLookupCol + 1);
                $r = 1;
                foreach (Subject::pluck('name', 'code') as $code => $name) {
                    $sheet->setCellValue($subjCodeCol . $r, $code);
                    $sheet->setCellValue($subjNameCol . $r, $name);
                    $r++;
                }

                foreach ([$courseCodeCol, $courseNameCol, $subjCodeCol, $subjNameCol] as $col) {
                    $sheet->getColumnDimension($col)->setVisible(false);
                }

                $sheet->setCellValue('A1', 'EASTERN SAMAR STATE UNIVERSITY - GUIUAN CAMPUS');
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- Fields ---
                $sheet->setCellValue('A2', 'Program:');
                $sheet->mergeCells('C2:J2');
                $sheet->setCellValue('C2', "=IFERROR(VLOOKUP(C3,{$courseCodeCol}:{$courseNameCol},2,FALSE),\"\")");
                $sheet->getStyle('C2')->getFont()->setBold(true);

                $sheet->setCellValue('A3', 'Course Code:');
                $sheet->mergeCells('C3:D3');
                $sheet->setCellValue('F3', 'Year Level:');
                $sheet->mergeCells('H3:I3');

                $sheet->setCellValue('A4', 'Course/Specialization:');
                $sheet->mergeCells('C4:F4');

                $sheet->setCellValue('A5', 'School Year:');
                $sheet->mergeCells('C5:D5');

                foreach (['A2', 'A3', 'F3', 'A4', 'A5'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }
                foreach (['C3:D3', 'H3:I3', 'C4:F4', 'C5:D5'] as $range) {
                    $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                }

                $courseCodes = Course::active()->pluck('code')->implode(',');
                $schoolYears = SchoolYear::pluck('year_code')->implode(',');

                $this->addListDropdown($sheet, 'C3', $courseCodes, 'Pick the Course Code this masterlist is for.');
                $this->addListDropdown($sheet, 'H3', '1,2,3,4,5', 'Pick the Year Level (1-5).');
                $this->addListDropdown($sheet, 'C5', $schoolYears, 'Pick the School Year this masterlist is for.');

                // --- Semester group header row ---
                $groupRow = 7;
                $firstSemLetter = Coordinate::stringFromColumnIndex($this->firstSemStartCol);
                $firstSemEndLetter = Coordinate::stringFromColumnIndex($this->firstSemStartCol + $this->subjectsPerSemester - 1);
                $secondSemLetter = Coordinate::stringFromColumnIndex($this->secondSemStartCol);
                $secondSemEndLetter = Coordinate::stringFromColumnIndex($this->lastCol);

                $sheet->setCellValue($firstSemLetter . $groupRow, 'First Semester');
                $sheet->mergeCells("{$firstSemLetter}{$groupRow}:{$firstSemEndLetter}{$groupRow}");
                $sheet->setCellValue($secondSemLetter . $groupRow, 'Second Semester');
                $sheet->mergeCells("{$secondSemLetter}{$groupRow}:{$secondSemEndLetter}{$groupRow}");

                $sheet->getStyle("{$firstSemLetter}{$groupRow}:{$secondSemEndLetter}{$groupRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // --- Column headers ---
                $headerRow1 = 8;
                $headerRow2 = 9;

                $sheet->setCellValue('A' . $headerRow1, 'No.');
                $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                $sheet->setCellValue('B' . $headerRow1, 'Student No.');
                $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
                $sheet->setCellValue('C' . $headerRow1, 'Name of Students');
                $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");
                $sheet->getStyle("A{$headerRow1}:C{$headerRow2}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                for ($c = $this->firstSemStartCol; $c <= $this->lastCol; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $sheet->setCellValue($colLetter . $headerRow1, '(subject code)');
                    $sheet->setCellValue(
                        $colLetter . $headerRow2,
                        "=IFERROR(VLOOKUP({$colLetter}{$headerRow1},{$subjCodeCol}:{$subjNameCol},2,FALSE),\"(subject name)\")"
                    );
                }

                $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$headerRow2}")->applyFromArray([
                    'font'      => ['italic' => true, 'color' => ['argb' => 'FF999999']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("A{$headerRow1}:C{$headerRow2}")->applyFromArray([
                    'font' => ['bold' => true, 'italic' => false, 'color' => ['argb' => 'FF000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
                ]);

                // --- Sample row ---
                $sampleRow = $headerRow2 + 1;
                $sheet->setCellValue('A' . $sampleRow, 'SAMPLE');
                $sheet->setCellValue('B' . $sampleRow, '2024-00001');
                $sheet->setCellValue('C' . $sampleRow, 'Dela Cruz, Juan S.');
                $firstDataCol = Coordinate::stringFromColumnIndex($this->firstSemStartCol);
                $sheet->setCellValue($firstDataCol . $sampleRow, '1.75');

                $sheet->getStyle("A{$sampleRow}:{$lastColLetter}{$sampleRow}")->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF999999']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9E6']],
                ]);

                // --- Student rows: MALE then FEMALE ---
                $gridStartRow = $groupRow;
                $row = $sampleRow + 1;

                $writeGroup = function (string $label) use ($sheet, $lastColLetter, &$row) {
                    $sheet->setCellValue('A' . $row, $label);
                    $sheet->mergeCells("A{$row}:{$lastColLetter}{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
                    ]);
                    $row++;

                    $dataStart = $row;
                    for ($i = 1; $i <= $this->blankRowsPerGroup; $i++) {
                        $sheet->setCellValue('A' . $row, "=IF(B{$row}<>\"\",COUNTA(\$B\${$dataStart}:B{$row}),\"\")");
                        $this->addStudentNumberValidation($sheet, 'B' . $row);

                        for ($c = $this->firstSemStartCol; $c <= $this->lastCol; $c++) {
                            $colLetter = Coordinate::stringFromColumnIndex($c);
                            $this->addGradeValidation($sheet, $colLetter . $row);
                        }

                        $row++;
                    }
                };

                $writeGroup('MALE');
                $writeGroup('FEMALE');

                $gridEndRow = $row - 1;

                $sheet->getStyle("A{$gridStartRow}:{$lastColLetter}{$gridEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF999999'],
                        ],
                    ],
                ]);

                $sheet->getStyle("{$firstSemEndLetter}{$gridStartRow}:{$firstSemEndLetter}{$gridEndRow}")->applyFromArray([
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(16);
                $sheet->getColumnDimension('C')->setWidth(24);
                for ($c = $this->firstSemStartCol; $c <= $this->lastCol; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $sheet->getColumnDimension($colLetter)->setWidth(13);
                }
            },
        ];
    }
}
