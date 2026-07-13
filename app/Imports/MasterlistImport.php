<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class MasterlistImport implements ToCollection
{
    const SUBJECT_PLACEHOLDER = '(subject code)';

    protected int $nameCol = 2;
    protected int $firstSemStartCol = 3;
    protected int $subjectsPerSemester = 9;
    protected int $secondSemStartCol;
    protected int $lastCol;
    protected int $headerRow = 7;  // 0-indexed row 8 — Subject Code row
    protected int $sampleRow = 9;  // 0-indexed row 10

    protected array $errors = [];
    protected int $importedCount = 0;
    protected int $studentsCreatedCount = 0;

    protected ?Course $course = null;
    protected ?int $yearLevel = null;
    protected ?Semester $firstSemester = null;
    protected ?Semester $secondSemester = null;

    public function __construct()
    {
        $this->secondSemStartCol = $this->firstSemStartCol + $this->subjectsPerSemester;
        $this->lastCol = $this->secondSemStartCol + $this->subjectsPerSemester - 1;
    }

    public function collection(Collection $rows)
    {
        $courseCode   = trim((string) ($rows[2][2] ?? ''));
        $yearLevelRaw = trim((string) ($rows[2][7] ?? ''));
        $schoolYear   = trim((string) ($rows[4][2] ?? ''));

        if ($courseCode === '') {
            $this->errors[] = 'Course Code (cell C3 dropdown) was not selected.';
            return;
        }
        $this->course = Course::where('code', $courseCode)->first();
        if (!$this->course) {
            $this->errors[] = "Course Code \"{$courseCode}\" not found in the system.";
            return;
        }

        $this->yearLevel = (int) $yearLevelRaw;
        if ($this->yearLevel < 1 || $this->yearLevel > 5) {
            $this->errors[] = "Year Level \"{$yearLevelRaw}\" (cell H3 dropdown) was not selected or is invalid.";
            return;
        }

        if ($schoolYear === '') {
            $this->errors[] = 'School Year (cell C5 dropdown) was not selected.';
            return;
        }
        $schoolYearModel = SchoolYear::where('year_code', $schoolYear)->first();
        if (!$schoolYearModel) {
            $this->errors[] = "School Year \"{$schoolYear}\" not found in the system.";
            return;
        }

        $this->firstSemester = Semester::where('school_year_id', $schoolYearModel->id)
            ->where('semester_name', 'like', '%1st%')->first();
        $this->secondSemester = Semester::where('school_year_id', $schoolYearModel->id)
            ->where('semester_name', 'like', '%2nd%')->first();

        $headerCells = $rows[$this->headerRow] ?? collect();
        $subjectByCol = [];

        for ($c = $this->firstSemStartCol; $c <= $this->lastCol; $c++) {
            $code = trim((string) ($headerCells[$c] ?? ''));
            if ($code === '' || stripos($code, self::SUBJECT_PLACEHOLDER) !== false) {
                continue;
            }

            $isFirstSem = $c < $this->secondSemStartCol;
            $semesterName = $isFirstSem ? '1st Semester' : '2nd Semester';
            $semester = $isFirstSem ? $this->firstSemester : $this->secondSemester;

            if (!$semester) {
                $this->errors[] = "No {$semesterName} found for School Year {$schoolYear} — subject \"{$code}\" skipped.";
                continue;
            }

            $subject = Subject::firstOrCreate(
                [
                    'course_id'  => $this->course->id,
                    'code'       => $code,
                    'year_level' => $this->yearLevel,
                    'semester'   => $semesterName,
                ],
                [
                    'name'   => $code,
                    'units'  => 3,
                    'status' => 'active',
                ]
            );

            $subjectByCol[$c] = ['subject' => $subject, 'semester' => $semester];
        }

        if (empty($subjectByCol)) {
            $this->errors[] = 'No subject codes found in the column headers — nothing to import.';
            return;
        }

        // --- Walk student rows, tracking MALE/FEMALE so we can infer gender for auto-created students ---
        $currentGender = null;

        foreach ($rows as $index => $row) {
            if ($index <= $this->headerRow || $index === $this->sampleRow) {
                continue;
            }

            $colA = strtoupper(trim((string) ($row[0] ?? '')));
            if ($colA === 'MALE') {
                $currentGender = 'Male';
                continue;
            }
            if ($colA === 'FEMALE') {
                $currentGender = 'Female';
                continue;
            }
            if ($colA === 'SAMPLE') {
                continue;
            }

            $studentNumber = trim((string) ($row[1] ?? ''));
            $studentName   = Str::title(trim((string) ($row[$this->nameCol] ?? '')));

            if ($studentNumber === '' && $studentName === '') {
                continue;
            }

            $student = null;
            if ($studentNumber !== '') {
                $student = Student::where('student_number', $studentNumber)->first();
            }
            if (!$student && $studentName !== '') {
                $student = Student::where('course_id', $this->course->id)
                    ->where('year_level', $this->yearLevel)
                    ->get()
                    ->first(fn($s) => strcasecmp($s->getFullName(), $studentName) === 0);
            }

            // --- Auto-create the student if not found, instead of skipping ---
            if (!$student) {
                if ($studentNumber === '' || $studentName === '') {
                    $this->errors[] = 'Row ' . ($index + 1) . ': missing Student No. or Name — cannot auto-create, skipped.';
                    continue;
                }

                [$firstName, $middleName, $lastName] = $this->parseName($studentName);

                $student = Student::create([
                    'student_number' => $studentNumber,
                    'course_id'      => $this->course->id,
                    'first_name'     => $firstName,
                    'middle_name'    => $middleName,
                    'last_name'      => $lastName,
                    'birth_date'     => '2000-01-01', // placeholder — Registrar should correct via Edit Student
                    'gender'         => $currentGender ?? 'Male',
                    'email'          => Str::slug($studentNumber) . '@pending.cogtor.local', // placeholder, unique
                    'year_level'     => $this->yearLevel,
                    'student_type'   => 'Regular',
                    'status'         => 'active',
                ]);

                $this->studentsCreatedCount++;
            }

            foreach ($subjectByCol as $col => $info) {
                $gradeVal = trim((string) ($row[$col] ?? ''));
                if ($gradeVal === '' || !is_numeric($gradeVal)) {
                    continue;
                }
                $this->upsertGrade($student, $info['subject'], $info['semester'], (float) $gradeVal);
            }
        }
    }

    protected function parseName(string $fullName): array
    {
        // Expected convention: "Lastname, Firstname M."
        if (str_contains($fullName, ',')) {
            [$last, $rest] = array_map('trim', explode(',', $fullName, 2));
            $parts = preg_split('/\s+/', trim($rest));
            $first = array_shift($parts) ?? $rest;
            $middle = $parts ? implode(' ', $parts) : null;
            return [$first, $middle, $last];
        }

        // Fallback: "Firstname Middle Lastname"
        $parts = preg_split('/\s+/', trim($fullName));
        $last = array_pop($parts) ?? $fullName;
        $first = array_shift($parts) ?? $last;
        $middle = $parts ? implode(' ', $parts) : null;
        return [$first, $middle, $last];
    }

    protected function upsertGrade(Student $student, Subject $subject, Semester $semester, float $gradeValue): void
    {
        $enrollment = Enrollment::firstOrCreate(
            [
                'student_id'  => $student->id,
                'subject_id'  => $subject->id,
                'semester_id' => $semester->id,
            ],
            [
                'enrolled_by'     => auth()->id(),
                'enrollment_date' => now(),
                'status'          => 'enrolled',
            ]
        );

        Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'faculty_id' => auth()->id(),
                'grade'      => $gradeValue,
                'status'     => 'finalized',
            ]
        );

        $this->importedCount++;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getStudentsCreatedCount(): int
    {
        return $this->studentsCreatedCount;
    }
}
