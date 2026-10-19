<?php

namespace App\Support;

use App\Http\Controllers\Registrar\StudentController;
use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Bulk Add Students: template, file parsing, clean-up and row checks.
 * Rows are checked with the same rules as the Add Student form.
 */
class StudentBulkImport
{
    public const MAX_ROWS = 500;

    /** Template columns: field => heading. Headings are matched loosely (case, spaces, "*" ignored). */
    public const COLUMNS = [
        'student_number'              => 'Student Number *',
        'last_name'                   => 'Last Name *',
        'first_name'                  => 'First Name *',
        'middle_name'                 => 'Middle Name',
        'suffix'                      => 'Suffix',
        'birth_date'                  => 'Birth Date * (YYYY-MM-DD)',
        'gender'                      => 'Gender *',
        'email'                       => 'Email *',
        'phone'                       => 'Phone',
        'address'                     => 'Address',
        'place_of_birth'              => 'Place of Birth',
        'elementary_school'           => 'Elementary School',
        'elementary_graduation_year'  => 'Elementary Year Graduated',
        'high_school'                 => 'High School',
        'high_school_graduation_year' => 'High School Year Graduated',
        'course_code'                 => 'Course Code *',
        'year_level'                  => 'Year Level *',
        'section'                     => 'Section',
        'student_type'                => 'Student Type',
        'status'                      => 'Status',
    ];

    private const REQUIRED_HEADINGS = ['student_number', 'last_name', 'first_name', 'birth_date', 'gender', 'email', 'course_code', 'year_level'];

    private const NAME_FIELDS = ['first_name', 'middle_name', 'last_name'];

    private const SUFFIXES = ['jr' => 'Jr.', 'sr' => 'Sr.', 'ii' => 'II', 'iii' => 'III', 'iv' => 'IV', 'v' => 'V'];

    /** Template workbook: "Students" sheet (headings + dropdowns) and an "Instructions" sheet. */
    public static function template(): Spreadsheet
    {
        $book  = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Students');

        $col = 1;
        foreach (self::COLUMNS as $field => $heading) {
            $sheet->setCellValue([$col, 1], $heading);
            $sheet->getColumnDimensionByColumn($col)->setWidth(in_array($field, ['address', 'elementary_school', 'high_school', 'email'], true) ? 30 : 18);
            $col++;
        }
        $last = count(self::COLUMNS);
        $sheet->getStyle([1, 1, $last, 1])->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
        ]);
        $sheet->freezePane('A2');

        // Text columns stay text in Excel (keeps leading zeros of phone numbers, dashes of student numbers).
        foreach (['student_number', 'phone', 'birth_date'] as $field) {
            $letter = self::letter($field);
            $sheet->getStyle("{$letter}2:{$letter}" . (self::MAX_ROWS + 1))->getNumberFormat()->setFormatCode('@');
        }

        $lists = [
            'gender'       => 'Male,Female',
            'student_type' => 'Regular,Irregular,Transferee',
            'status'       => 'active,inactive,graduated,dropped',
            'year_level'   => '1,2,3,4,5',
            'course_code'  => Course::active()->orderBy('code')->pluck('code')->implode(','),
        ];
        foreach ($lists as $field => $list) {
            $letter = self::letter($field);
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Pick from the list')
                ->setError('Choose a value from the dropdown.')
                ->setFormula1('"' . $list . '"');
            $validation->setSqref("{$letter}2:{$letter}" . (self::MAX_ROWS + 1));
            $sheet->setDataValidation("{$letter}2:{$letter}" . (self::MAX_ROWS + 1), $validation);
        }

        $help = $book->createSheet()->setTitle('Instructions');
        $lines = [
            'BULK ADD STUDENTS — how to fill the "Students" sheet',
            '',
            '1. One student per row, starting on row 2. Do not change the headings in row 1.',
            '2. Columns marked * are required.',
            '3. Birth Date: YYYY-MM-DD (e.g. 2004-03-15) or MM/DD/YYYY.',
            '4. Gender, Course Code, Year Level, Student Type and Status have dropdowns.',
            '5. Student Type is Regular if left blank. Status is active if left blank.',
            '6. Years graduated: 4-digit year (e.g. 2016). Section is optional (e.g. A).',
            '7. Up to ' . self::MAX_ROWS . ' students per file.',
            '',
            'Nothing is saved on upload. You get a preview first: errors, possible duplicates and',
            'automatic clean-ups (extra spaces, ALL CAPS names, phone numbers missing the leading 0)',
            'are shown per row, and only the rows you keep ticked are added.',
            '',
            'Course codes: ' . $lists['course_code'],
        ];
        foreach ($lines as $i => $line) {
            $help->setCellValue('A' . ($i + 1), $line);
        }
        $help->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $help->getColumnDimension('A')->setWidth(100);

        $book->setActiveSheetIndex(0);

        return $book;
    }

    /**
     * Reads the first sheet of the upload and checks every row.
     * Returns ['error' => string] for an unusable file, or ['rows' => [...]] where each row is
     * ['line', 'data', 'errors', 'warnings', 'changes'].
     */
    public static function parse(string $path): array
    {
        try {
            $sheet = IOFactory::load($path)->getSheet(0);
        } catch (\Throwable $e) {
            return ['error' => 'The file could not be read. Use the Bulk Add template (.xlsx) or a .csv saved from it.'];
        }

        $table = $sheet->toArray(null, true, false, false);
        $headings = array_map(fn ($h) => self::headingKey((string) $h), array_shift($table) ?? []);

        $fieldByKey = [];
        foreach (self::COLUMNS as $field => $heading) {
            $fieldByKey[self::headingKey($heading)] = $field;
            $fieldByKey[self::headingKey(str_replace('_', ' ', $field))] = $field;
        }
        $columns = [];
        foreach ($headings as $index => $key) {
            if (isset($fieldByKey[$key])) {
                $columns[$index] = $fieldByKey[$key];
            }
        }
        $missing = array_diff(self::REQUIRED_HEADINGS, $columns);
        if ($missing) {
            return ['error' => 'Missing column(s): ' . implode(', ', array_map(fn ($f) => self::COLUMNS[$f], $missing)) . '. Download the template and copy your data into it.'];
        }

        $rows = [];
        foreach ($table as $i => $cells) {
            $raw = [];
            foreach ($columns as $index => $field) {
                $raw[$field] = $cells[$index] ?? null;
            }
            if (collect($raw)->every(fn ($v) => trim((string) $v) === '')) {
                continue; // blank row
            }
            $rows[] = ['line' => $i + 2, 'raw' => $raw];
        }

        if (count($rows) === 0) {
            return ['error' => 'The file has no student rows.'];
        }
        if (count($rows) > self::MAX_ROWS) {
            return ['error' => 'The file has ' . count($rows) . ' rows. The limit is ' . self::MAX_ROWS . ' per file; split it into smaller files.'];
        }

        return ['rows' => self::check(array_map(fn ($row) => self::normalize($row['line'], $row['raw']), $rows))];
    }

    /** Re-checks rows (e.g. just before saving) against the database and each other. */
    public static function check(array $rows): array
    {
        $courses = Course::active()->get()->keyBy(fn ($c) => mb_strtoupper($c->code));
        $existing = Student::query()->get(['id', 'student_number', 'first_name', 'last_name', 'birth_date'])
            ->keyBy(fn ($s) => self::personKey($s->last_name, $s->first_name, $s->birth_date?->format('Y-m-d')));

        $rules = Arr::except(StudentController::newStudentRules(), ['photo', 'major_id']);
        $seenNumbers = $seenEmails = $seenPeople = [];

        foreach ($rows as &$row) {
            $row['errors'] = $row['parse_errors'] ?? [];
            $row['warnings'] = [];
            $data = &$row['data'];

            $course = $courses->get(mb_strtoupper((string) $data['course_code']));
            $data['course_id'] = $course?->id;
            if ($data['course_code'] !== '' && ! $course) {
                $row['errors'][] = "Unknown course code \"{$data['course_code']}\".";
            }

            $validator = Validator::make($data, $rules, StudentController::validationMessages(), ['course_id' => 'course code']);
            foreach ($validator->errors()->messages() as $field => $messages) {
                if ($field === 'course_id' && $data['course_code'] !== '') {
                    continue; // already reported as unknown course code
                }
                if ($field === 'birth_date' && in_array('birth_date', $row['bad_fields'] ?? [], true)) {
                    continue; // already reported as an unreadable date
                }
                array_push($row['errors'], ...$messages);
            }

            $number = mb_strtolower((string) $data['student_number']);
            $email = mb_strtolower((string) $data['email']);
            if ($number !== '' && isset($seenNumbers[$number])) {
                $row['errors'][] = "Same student number as row {$seenNumbers[$number]}.";
            }
            if ($email !== '' && isset($seenEmails[$email])) {
                $row['errors'][] = "Same email as row {$seenEmails[$email]}.";
            }
            $seenNumbers[$number] ??= $row['line'];
            $seenEmails[$email] ??= $row['line'];

            $person = self::personKey($data['last_name'], $data['first_name'], $data['birth_date']);
            if ($match = $existing->get($person)) {
                $row['warnings'][] = "Possible duplicate: {$match->student_number} has the same name and birth date.";
            } elseif (isset($seenPeople[$person])) {
                $row['warnings'][] = "Possible duplicate of row {$seenPeople[$person]} (same name and birth date).";
            }
            $seenPeople[$person] ??= $row['line'];

            $row['errors'] = array_values(array_unique($row['errors']));
            unset($data);
        }
        unset($row);

        return $rows;
    }

    /** Cleans one row: spaces, name case, suffix, gender, dates, phone, codes. */
    private static function normalize(int $line, array $raw): array
    {
        $changes = $parseErrors = $badFields = [];
        $data = [];

        foreach (array_keys(self::COLUMNS) as $field) {
            $value = $raw[$field] ?? null;
            $data[$field] = is_string($value) ? RecordsAudit::tidy($value) : (is_null($value) ? '' : $value);
            if (is_string($value) && $value !== '' && $data[$field] !== $value) {
                $changes['spaces'] = 'extra spaces removed';
            }
        }

        foreach (self::NAME_FIELDS as $field) {
            $value = (string) $data[$field];
            if (preg_match('/\p{L}/u', $value) && ($value === mb_strtoupper($value) || $value === mb_strtolower($value))) {
                $fixed = mb_convert_case(mb_strtolower($value), MB_CASE_TITLE);
                if ($fixed !== $value) {
                    $data[$field] = $fixed;
                    $changes['case'] = 'name capitalization fixed';
                }
            }
        }

        $suffixKey = strtolower(rtrim((string) $data['suffix'], '.'));
        if (isset(self::SUFFIXES[$suffixKey]) && self::SUFFIXES[$suffixKey] !== $data['suffix']) {
            $data['suffix'] = self::SUFFIXES[$suffixKey];
            $changes['suffix'] = 'suffix written as ' . $data['suffix'];
        }

        $data['student_number'] = is_float($data['student_number']) || is_int($data['student_number'])
            ? (string) (int) $data['student_number'] : (string) $data['student_number'];
        $data['email'] = mb_strtolower((string) $data['email']);

        $gender = strtolower((string) $data['gender']);
        $data['gender'] = match (true) {
            in_array($gender, ['m', 'male'], true)   => 'Male',
            in_array($gender, ['f', 'female'], true) => 'Female',
            default                                  => $data['gender'],
        };

        $data['birth_date'] = self::date($data['birth_date']);
        if ($data['birth_date'] === false) {
            $parseErrors[] = 'Birth date "' . ($raw['birth_date'] ?? '') . '" is not a date. Use YYYY-MM-DD.';
            $badFields[] = 'birth_date';
            $data['birth_date'] = '';
        }

        $phone = preg_replace('/[^\d+]/', '', (string) $data['phone']);
        if ($phone !== '' && preg_match('/^9\d{9}$/', $phone)) {
            $phone = '0' . $phone;
            $changes['phone'] = 'leading 0 added to phone';
        }
        $data['phone'] = $phone !== '' ? $phone : (string) $data['phone'];

        if (preg_match('/^\s*(\d)/', (string) $data['year_level'], $m)) {
            $data['year_level'] = (int) $m[1];
        }
        foreach (['elementary_graduation_year', 'high_school_graduation_year'] as $field) {
            $data[$field] = $data[$field] === '' ? null : (is_numeric($data[$field]) ? (int) $data[$field] : $data[$field]);
        }

        $type = ucfirst(strtolower((string) $data['student_type']));
        $data['student_type'] = $type === '' ? 'Regular' : $type;
        $status = strtolower((string) $data['status']);
        $data['status'] = $status === '' ? 'active' : $status;
        $data['course_code'] = mb_strtoupper((string) $data['course_code']);
        $data['section'] = mb_strtoupper(trim((string) $data['section']));

        foreach (['middle_name', 'suffix', 'phone', 'address', 'place_of_birth', 'elementary_school', 'high_school'] as $field) {
            $data[$field] = $data[$field] === '' ? null : (string) $data[$field];
        }

        return ['line' => $line, 'data' => $data, 'changes' => array_values($changes), 'parse_errors' => $parseErrors, 'bad_fields' => $badFields];
    }

    /** Y-m-d string, '' when blank, false when unreadable. Accepts Excel dates, YYYY-MM-DD and MM/DD/YYYY. */
    private static function date($value): string|false
    {
        if ($value === '' || $value === null) {
            return '';
        }
        if (is_int($value) || is_float($value) || (is_string($value) && preg_match('/^\d{5}(\.\d+)?$/', $value))) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
        }
        foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'Y/m/d'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, (string) $value);
            if ($date && $date->format($format) === (string) $value) {
                return $date->format('Y-m-d');
            }
        }

        return false;
    }

    private static function personKey($last, $first, $birth): string
    {
        $key = fn ($v) => preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $v));

        return $key($last) . '|' . $key($first) . '|' . $birth;
    }

    private static function headingKey(string $heading): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(preg_replace('/\(.*?\)|\*/', '', $heading)));
    }

    private static function letter(string $field): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(array_search($field, array_keys(self::COLUMNS), true) + 1);
    }
}
