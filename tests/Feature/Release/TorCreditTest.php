<?php

namespace Tests\Feature\Release;

use App\Models\{Enrollment, Grade, Semester, Student, Subject, TorRecord, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 9b, panel recommendation: on the TOR a failed grade earns 0 credit. */
class TorCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_rule(): void
    {
        $this->assertSame(3.0, Grade::earnedCredit(3, 1.00));
        $this->assertSame(3.0, Grade::earnedCredit(3, 3.00));
        $this->assertSame(3.0, Grade::earnedCredit(3, 3.50), 'conditional (3.1-3.5) is not a failed grade');
        $this->assertSame(0.0, Grade::earnedCredit(3, 3.51));
        $this->assertSame(0.0, Grade::earnedCredit(3, 3.60));
        $this->assertSame(0.0, Grade::earnedCredit(3, 5.00));
        $this->assertSame(3.0, Grade::earnedCredit(3, 5.00, 2.75), 'passed the re-exam');
        $this->assertSame(0.0, Grade::earnedCredit(3, 3.25, 5.00), 'failed the re-exam');
        $this->assertSame(2.0, Grade::earnedCredit(2, null));
    }

    public function test_tor_prints_zero_credit_for_failed_subjects(): void
    {
        $this->withoutVite();
        $this->seed();
        $reg = User::where('username', 'registrar')->firstOrFail();
        $student = Student::firstOrFail();
        $sem = Semester::orderBy('id')->firstOrFail();
        $mk = function (string $code, float $grade, ?float $reExam) use ($student, $sem, $reg) {
            $subject = Subject::create(['course_id' => $student->course_id, 'code' => $code, 'name' => "Credit $code", 'units' => 3,
                'year_level' => 1, 'semester' => $sem->semester_name, 'status' => 'active']);
            $e = Enrollment::create(['student_id' => $student->id, 'subject_id' => $subject->id, 'semester_id' => $sem->id,
                'enrolled_by' => $reg->id, 'enrollment_date' => now(), 'status' => 'enrolled']);
            Grade::create(['enrollment_id' => $e->id, 'grade' => $grade, 're_exam_grade' => $reExam, 'status' => 'finalized']);
        };
        $mk('CR-PASS', 2.00, null);
        $mk('CR-FAIL', 5.00, null);
        $mk('CR-REEX', 5.00, 2.75);

        $fields = ['prepared_by_name' => 'A', 'prepared_by_title' => 'Clerk', 'checked_by_name' => 'B', 'checked_by_title' => 'Registrar',
            'campus_admin_name' => 'C', 'campus_admin_title' => 'Admin'];
        $this->actingAs($reg);
        $html = $this->postJson(route('registrar.students.tor.preview', $student), $fields)->assertOk()->json('html');

        $row = function (string $code) use ($html) {
            $this->assertMatchesRegularExpression('#' . $code . '</td>.*?</tr>#s', $html);
            preg_match('#' . $code . '</td>.*?</tr>#s', $html, $m);
            preg_match_all('#<td class="col-credit">([^<]*)</td>#', $m[0], $c);

            return trim($c[1][0]);
        };
        $this->assertSame('3.0', $row('CR-PASS'));
        $this->assertSame('0.0', $row('CR-FAIL'));
        $this->assertSame('3.0', $row('CR-REEX'));

        // The stored TOR snapshot carries the same credit.
        $this->post(route('registrar.students.tor.generate', $student), $fields)->assertOk();
        $subjects = collect(TorRecord::latest('id')->first()->all_grades_data)->flatMap(fn ($y) => collect($y['semesters'])->flatMap(fn ($s) => $s['subjects']));
        $this->assertSame(0.0, (float) $subjects->firstWhere('course_code', 'CR-FAIL')['credit']);
    }
}
