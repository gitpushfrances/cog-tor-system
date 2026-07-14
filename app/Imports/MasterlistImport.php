<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeSubmission;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;
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

    protected array $successes = [];
    protected array $warnings = [];
    protected array $errors = [];
    protected int $importedCount = 0;
    protected int $studentsCreatedCount = 0; // always 0 now — students must pre-exist; kept for controller message compatibility

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
        // The template workbook ships with hidden lookup sheets (CourseLookup,
        // SubjectLookup) that back the dropdown validation in the Worksheet tab.
        // Laravel Excel's ToCollection runs collection() once per sheet, so without
        // this guard those lookup sheets would each report a false
        // "course code not selected" error. Only the actual data sheet has real
        // course/year/school-year values in the expected header cells.
        $courseCodeProbe = trim((string) ($rows[2][2] ?? ''));
        $isLookupSheet = $courseCodeProbe === '' && $rows->contains(function ($row) {
            return collect($row)->contains(fn ($cell) => trim((string) $cell) !== '');
        });
        if ($isLookupSheet) {
            return;
        }

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
                $this->errors[] = "No {$semesterName} found for School Year {$schoolYear} — subject \"{$code}\" column skipped.";
                continue;
            }

            // Subject must already exist in the catalog — do not auto-create.
            $subject = Subject::where([
                'course_id'  => $this->course->id,
                'code'       => $code,
                'year_level' => $this->yearLevel,
                'semester'   => $semesterName,
            ])->first();

            if (!$subject) {
                $this->errors[] = "Subject \"{$code}\" was not found for {$this->course->code} Year {$this->yearLevel}, {$semesterName} — check the code and that it's under the correct semester column. Column skipped.";
                continue;
            }

            $subjectByCol[$c] = ['subject' => $subject, 'semester' => $semester];
        }

        if (empty($subjectByCol)) {
            $this->errors[] = 'No valid subject codes found in the column headers — nothing to import.';
            return;
        }

        // --- Walk student rows, skipping MALE/FEMALE group header rows and the sample row ---
        foreach ($rows as $index => $row) {
            if ($index <= $this->headerRow || $index === $this->sampleRow) {
                continue;
            }

            $colA = strtoupper(trim((string) ($row[0] ?? '')));
            if ($colA === 'MALE' || $colA === 'FEMALE' || $colA === 'SAMPLE') {
                continue;
            }

            $studentNumber = trim((string) ($row[1] ?? ''));

            if ($studentNumber === '') {
                continue;
            }

            $student = Student::where('student_number', $studentNumber)->first();

            if (!$student) {
                $this->errors[] = 'Row ' . ($index + 1) . ": Student No. \"{$studentNumber}\" was not found. Add the student via Student Management first, then re-import — row skipped.";
                continue;
            }

            if ((int) $student->year_level !== $this->yearLevel) {
                $this->errors[] = 'Row ' . ($index + 1) . ": {$student->getFullName()} ({$studentNumber}) is Year {$student->year_level} on record, but the sheet declares Year Level {$this->yearLevel} — row skipped entirely.";
                continue;
            }

            $sheetName = trim((string) ($row[$this->nameCol] ?? ''));
            if ($sheetName !== '' && !$this->namesLikelyMatch($sheetName, $student->getFullName())) {
                $this->warnings[] = 'Row ' . ($index + 1) . ": Name \"{$sheetName}\" does not match the system record \"{$student->getFullName()}\" for Student No. {$studentNumber} — please double-check this is the right student. Grade(s) were still imported using the Student No.";
            }

            foreach ($subjectByCol as $col => $info) {
                $gradeVal = trim((string) ($row[$col] ?? ''));
                if ($gradeVal === '' || !is_numeric($gradeVal)) {
                    continue;
                }

                $enrollment = Enrollment::where([
                    'student_id'  => $student->id,
                    'subject_id'  => $info['subject']->id,
                    'semester_id' => $info['semester']->id,
                ])->first();

                if (!$enrollment) {
                    $this->errors[] = 'Row ' . ($index + 1) . ": {$student->getFullName()} ({$studentNumber}) is not enrolled in {$info['subject']->code} for {$info['semester']->semester_name} — enroll them first via Enrollment Management. Grade skipped.";
                    continue;
                }

                $this->upsertGrade($enrollment, (float) $gradeVal);
                $this->successes[] = 'Row ' . ($index + 1) . ": {$student->getFullName()} ({$studentNumber}) — {$info['subject']->code} ({$info['semester']->semester_name}): grade " . number_format((float) $gradeVal, 2) . ' imported.';
            }
        }
    }

    /**
     * Loose, format-agnostic name check — see class-level notes in prior revisions.
     * Only meant to catch a genuinely different person on the row, not exact formatting.
     */
    protected function namesLikelyMatch(string $sheetName, string $systemName): bool
    {
        $tokenize = function (string $name) {
            return collect(preg_split('/[\s,]+/', strtolower($name)))
                ->map(fn ($w) => rtrim($w, '.'))
                ->filter(fn ($w) => $w !== '');
        };

        $sheetTokens  = $tokenize($sheetName);
        $systemTokens = $tokenize($systemName);

        if ($sheetTokens->isEmpty() || $systemTokens->isEmpty()) {
            return true;
        }

        return $sheetTokens->every(fn ($t) => $systemTokens->contains($t))
            || $systemTokens->every(fn ($t) => $sheetTokens->contains($t));
    }

    protected function upsertGrade(Enrollment $enrollment, float $gradeValue): void
    {
        $registrarId = auth()->id();

        $grade = Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'faculty_id' => null,
                'grade'      => $gradeValue,
                'status'     => 'finalized',
            ]
        );

        GradeSubmission::updateOrCreate(
            ['grade_id' => $grade->id],
            [
                'submitted_by' => $registrarId,
                'reviewed_by'  => $registrarId,
                'finalized_by' => $registrarId,
                'submitted_at' => now(),
                'reviewed_at'  => now(),
                'finalized_at' => now(),
                'dean_action'  => 'approved_by_head_of_department',
                'dean_remarks' => 'Bulk import via Masterlist',
            ]
        );

        $this->importedCount++;
    }

    public function getSuccesses(): array
    {
        return $this->successes;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
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
