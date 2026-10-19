<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Support\RecordsAudit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Masterlist grid: one course + year level + school year, subjects across
 * (grouped by semester), students down (MALE then FEMALE).
 *
 * - new MasterlistExport()                          → blank template (import is disabled; kept for reference)
 * - new MasterlistExport($schoolYear, $course, $yr[, $section]) → the real masterlist with finalized grades (one section, or all)
 */
class MasterlistExport implements FromArray, WithEvents, WithTitle
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

    /** Real-export data (null in template mode). */
    protected ?array $data = null;

    public function __construct(?SchoolYear $schoolYear = null, ?Course $course = null, ?int $yearLevel = null, ?string $section = null)
    {
        if ($schoolYear && $course && $yearLevel) {
            $this->data = $this->loadData($schoolYear, $course, $yearLevel, $section);
        }

        $this->firstSemStartCol = $this->nameCol + 1;
        $this->secondSemStartCol = $this->firstSemStartCol + $this->subjectsPerSemester;
        $this->lastCol = $this->secondSemStartCol + $this->subjectsPerSemester - 1;
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return $this->data ? 'Masterlist' : 'Worksheet';
    }

    /** Number of students in the real export (0 in template mode). */
    public function studentCount(): int
    {
        return $this->data ? $this->data['male']->count() + $this->data['female']->count() : 0;
    }

    /** Subjects of the course + year, per semester (the curriculum columns), and every enrolled student's finalized grades. */
    protected function loadData(SchoolYear $schoolYear, Course $course, int $yearLevel, ?string $section = null): array
    {
        $subjects = Subject::where('course_id', $course->id)
            ->where('year_level', $yearLevel)
            ->whereIn('semester', RecordsAudit::SEMESTERS)
            ->active()
            ->orderBy('code')
            ->get();

        $groups = collect(RecordsAudit::SEMESTERS)
            ->map(fn ($name) => ['label' => $name, 'subjects' => $subjects->where('semester', $name)->values()])
            ->filter(fn ($group) => $group['subjects']->isNotEmpty())
            ->values();

        $semesterIds = Semester::where('school_year_id', $schoolYear->id)->pluck('id', 'semester_name');

        // Every non-dropped enrollment in those subjects, in the matching semester of this school year
        // (includes irregular students from other courses).
        $enrollments = Enrollment::with(['student', 'grade'])
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->whereIn('semester_id', $semesterIds->values())
            ->where('status', '!=', 'dropped')
            ->get()
            ->filter(fn ($e) => $e->student && $semesterIds->get($subjects->firstWhere('id', $e->subject_id)?->semester) === $e->semester_id)
            ->filter(fn ($e) => $section === null || $e->student->section === $section);

        $rows = $enrollments->groupBy('student_id')->map(function (Collection $items) {
            $student = $items->first()->student;

            return [
                'student' => $student,
                'name'    => $this->displayName($student),
                'grades'  => $items->mapWithKeys(fn ($e) => [$e->subject_id => $e->grade?->status === 'finalized' ? $e->grade : null]),
            ];
        })->sortBy(fn ($row) => mb_strtolower($row['student']->last_name . ' ' . $row['student']->first_name))->values();

        return [
            'schoolYear' => $schoolYear,
            'course'     => $course,
            'yearLevel'  => $yearLevel,
            'section'    => $section,
            'groups'     => $groups,
            'male'       => $rows->filter(fn ($row) => $row['student']->gender === 'Male')->values(),
            'female'     => $rows->filter(fn ($row) => $row['student']->gender !== 'Male')->values(),
        ];
    }

    /** "Dela Cruz, Juan S." */
    protected function displayName($student): string
    {
        return trim($student->last_name . ', ' . $student->first_name
            . ($student->suffix ? ' ' . $student->suffix : '')
            . ($student->middle_name ? ' ' . mb_substr($student->middle_name, 0, 1) . '.' : ''));
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

                $this->data ? $this->writeMasterlist($sheet) : $this->writeTemplate($sheet);
            },
        ];
    }

    /** Title row + Program / Course Code / Year Level / Course-Specialization / School Year labels. */
    protected function writeHeader($sheet, string $lastColLetter): void
    {
        $sheet->setCellValue('A1', 'EASTERN SAMAR STATE UNIVERSITY - GUIUAN CAMPUS');
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Program:');
        $sheet->mergeCells('C2:J2');
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
    }

    /** Semester group row (7) and the two column-header rows (8 = code, 9 = name). $groups: [label, firstCol, lastCol]. */
    protected function writeColumnHeaders($sheet, array $groups, string $lastColLetter): void
    {
        $groupRow = 7;
        foreach ($groups as [$label, $start, $end]) {
            $startLetter = Coordinate::stringFromColumnIndex($start);
            $endLetter = Coordinate::stringFromColumnIndex($end);
            $sheet->setCellValue($startLetter . $groupRow, $label);
            if ($end > $start) {
                $sheet->mergeCells("{$startLetter}{$groupRow}:{$endLetter}{$groupRow}");
            }
        }

        $firstLetter = Coordinate::stringFromColumnIndex($groups[0][1]);
        $sheet->getStyle("{$firstLetter}{$groupRow}:{$lastColLetter}{$groupRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headerRow1 = 8;
        $headerRow2 = 9;

        $sheet->setCellValue('A' . $headerRow1, 'No.');
        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
        $sheet->setCellValue('B' . $headerRow1, 'Student No.');
        $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
        $sheet->setCellValue('C' . $headerRow1, 'Name of Students');
        $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");
        $sheet->getStyle("A{$headerRow1}:C{$headerRow2}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    /** MALE / FEMALE label row spanning the grid. */
    protected function writeGroupLabel($sheet, int $row, string $label, string $lastColLetter): void
    {
        $sheet->setCellValue('A' . $row, $label);
        $sheet->mergeCells("A{$row}:{$lastColLetter}{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
        ]);
    }

    /** Thin grid borders, a medium line after each semester group but the last, and column widths. */
    protected function writeGridFrame($sheet, array $groups, int $gridStartRow, int $gridEndRow, string $lastColLetter, int $nameWidth): void
    {
        $sheet->getStyle("A{$gridStartRow}:{$lastColLetter}{$gridEndRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF999999'],
                ],
            ],
        ]);

        foreach (array_slice($groups, 0, -1) as [, , $end]) {
            $endLetter = Coordinate::stringFromColumnIndex($end);
            $sheet->getStyle("{$endLetter}{$gridStartRow}:{$endLetter}{$gridEndRow}")->applyFromArray([
                'borders' => [
                    'right' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth($nameWidth);
        for ($c = $this->nameCol + 1; $c <= Coordinate::columnIndexFromString($lastColLetter); $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(13);
        }
    }

    protected function writeTemplate($sheet): void
    {
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

        $this->writeHeader($sheet, $lastColLetter);
        $sheet->setCellValue('C2', "=IFERROR(VLOOKUP(C3,{$courseCodeCol}:{$courseNameCol},2,FALSE),\"\")");

        $courseCodes = Course::active()->pluck('code')->implode(',');
        $schoolYears = SchoolYear::pluck('year_code')->implode(',');

        $this->addListDropdown($sheet, 'C3', $courseCodes, 'Pick the Course Code this masterlist is for.');
        $this->addListDropdown($sheet, 'H3', '1,2,3,4,5', 'Pick the Year Level (1-5).');
        $this->addListDropdown($sheet, 'C5', $schoolYears, 'Pick the School Year this masterlist is for.');

        $groups = [
            ['First Semester', $this->firstSemStartCol, $this->firstSemStartCol + $this->subjectsPerSemester - 1],
            ['Second Semester', $this->secondSemStartCol, $this->lastCol],
        ];
        $this->writeColumnHeaders($sheet, $groups, $lastColLetter);

        $groupRow = 7;
        $headerRow1 = 8;
        $headerRow2 = 9;

        for ($c = $this->firstSemStartCol; $c <= $this->lastCol; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $sheet->setCellValue($colLetter . $headerRow1, self::SUBJECT_PLACEHOLDER);
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
        $row = $sampleRow + 1;

        foreach (['MALE', 'FEMALE'] as $label) {
            $this->writeGroupLabel($sheet, $row, $label, $lastColLetter);
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
        }

        $this->writeGridFrame($sheet, $groups, $groupRow, $row - 1, $lastColLetter, 24);
    }

    protected function writeMasterlist($sheet): void
    {
        $data = $this->data;

        // Column layout: one block per semester that has subjects.
        $groups = [];
        $subjectColumns = []; // column index => Subject
        $col = $this->nameCol + 1;
        foreach ($data['groups'] as $group) {
            $start = $col;
            foreach ($group['subjects'] as $subject) {
                $subjectColumns[$col++] = $subject;
            }
            $groups[] = [$group['label'], $start, $col - 1];
        }
        $lastCol = max($col - 1, $this->nameCol + 1);
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

        $this->writeHeader($sheet, $lastColLetter);
        $sheet->setCellValue('C2', $data['course']->name);
        $sheet->setCellValue('C3', $data['course']->code);
        $sheet->setCellValue('H3', $data['yearLevel'] . ($data['section'] ? ' - Section ' . $data['section'] : ''));
        $sheet->setCellValue('C5', $data['schoolYear']->year_code);
        $sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $this->writeColumnHeaders($sheet, $groups, $lastColLetter);

        $headerRow1 = 8;
        $headerRow2 = 9;
        foreach ($subjectColumns as $c => $subject) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $sheet->setCellValue($letter . $headerRow1, $subject->code);
            $sheet->setCellValue($letter . $headerRow2, $subject->name);
        }

        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$headerRow2}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
        ]);
        $sheet->getStyle("D{$headerRow2}:{$lastColLetter}{$headerRow2}")->getFont()->setBold(false)->setSize(9);

        $row = $headerRow2 + 1;
        foreach (['MALE' => $data['male'], 'FEMALE' => $data['female']] as $label => $students) {
            $this->writeGroupLabel($sheet, $row, $label, $lastColLetter);
            $row++;

            foreach ($students->values() as $i => $entry) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValueExplicit('B' . $row, $entry['student']->student_number, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $entry['name']);

                foreach ($subjectColumns as $c => $subject) {
                    $grade = $entry['grades']->get($subject->id);
                    if (! $grade) {
                        continue;
                    }
                    $cell = Coordinate::stringFromColumnIndex($c) . $row;
                    if ($grade->re_exam_grade !== null) {
                        $sheet->setCellValueExplicit($cell, sprintf('%.2f (%.2f)', $grade->grade, $grade->re_exam_grade), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($cell, (float) $grade->grade);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('0.00');
                    }
                }
                $row++;
            }
        }
        $gridEndRow = $row - 1;

        $sheet->getStyle("A10:A{$gridEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D10:{$lastColLetter}{$gridEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->writeGridFrame($sheet, $groups, 7, $gridEndRow, $lastColLetter, 30);

        $note = $gridEndRow + 2;
        $sheet->setCellValue('A' . $note, 'Finalized grades only. Blank = no finalized grade. 5.00 (3.00) = grade (re-exam grade).');
        $sheet->setCellValue('A' . ($note + 1), 'Exported ' . now()->format('M d, Y g:i A') . ' by ' . (auth()->user()->name ?? 'system') . '.');
        $sheet->getStyle("A{$note}:A" . ($note + 1))->getFont()->setItalic(true)->setSize(9)->getColor()->setARGB('FF6B7280');

        $sheet->freezePane('D10');
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
    }
}
