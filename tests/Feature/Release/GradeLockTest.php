<?php

namespace Tests\Feature\Release;

use App\Models\{Course, Enrollment, Grade, SchoolYear, Semester, Student, Subject, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Client rule: grades are entered or changed only in the active semester of the active
 * school year. Every other semester is closed, with no exception.
 * Seeded: SY 2024-2025 active; 1st Semester completed, 2nd Semester active, Summer upcoming.
 */
class GradeLockTest extends TestCase
{
    use RefreshDatabase;

    private User $reg;
    private Semester $now;      // 2nd Semester, active SY: open
    private Semester $sameYear; // 1st Semester, active SY: closed
    private Semester $past;     // 1st Semester, completed SY: closed
    private Subject $subject;
    private Subject $firstSemSubject;
    private Student $s1;
    private Student $s2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        $this->reg = User::where('email', 'registrar@cogtor.test')->firstOrFail();
        $this->now = Semester::where('status', 'active')->firstOrFail();
        $this->sameYear = Semester::where('school_year_id', $this->now->school_year_id)->where('semester_name', '1st Semester')->firstOrFail();
        $sy = SchoolYear::create(['year_code' => '2023-2024', 'start_date' => '2023-08-01', 'end_date' => '2024-05-31', 'status' => 'completed']);
        $this->past = Semester::create(['school_year_id' => $sy->id, 'semester_name' => '1st Semester', 'semester_order' => 1,
            'start_date' => '2023-08-01', 'end_date' => '2023-12-20', 'status' => 'completed']);
        $course = Course::firstOrFail();
        $mkSubject = fn ($code, $sem) => Subject::create(['course_id' => $course->id, 'code' => $code, 'name' => "Lock $code", 'units' => 3,
            'year_level' => 1, 'semester' => $sem, 'status' => 'active']);
        $this->subject = $mkSubject('LOCK-2', '2nd Semester');
        $this->firstSemSubject = $mkSubject('LOCK-1', '1st Semester');
        $mk = fn ($n) => Student::create(['student_number' => $n, 'course_id' => $course->id, 'first_name' => 'T', 'last_name' => 'L' . $n,
            'birth_date' => '2004-01-01', 'gender' => 'Male', 'email' => "$n@x.test", 'year_level' => 1, 'student_type' => 'Regular', 'status' => 'active']);
        $this->s1 = $mk('26-7001');
        $this->s2 = $mk('26-7002');
        $this->actingAs($this->reg);
    }

    private function byStudent(Semester $sem, $grade, array $extra = [], ?Student $s = null, ?Subject $subject = null)
    {
        return $this->from('/registrar/encode-grades')->post(route('registrar.encode-grades.store'), array_merge([
            'student_id' => ($s ?? $this->s1)->id, 'semester_id' => $sem->id, 'grades' => [($subject ?? $this->subject)->id => $grade],
        ], $extra));
    }

    /** A grade that already exists in a semester (recorded while that semester was open). */
    private function existing(Student $s, Semester $sem, float $grade, ?Subject $subject = null, string $status = 'finalized'): Grade
    {
        $e = Enrollment::create(['student_id' => $s->id, 'subject_id' => ($subject ?? $this->firstSemSubject)->id, 'semester_id' => $sem->id,
            'enrolled_by' => $this->reg->id, 'enrollment_date' => now(), 'status' => 'enrolled']);

        return Grade::create(['enrollment_id' => $e->id, 'grade' => $grade, 'status' => $status]);
    }

    private function gradeOf(Student $s, Semester $sem, ?Subject $subject = null): ?Grade
    {
        return Enrollment::where(['student_id' => $s->id, 'subject_id' => ($subject ?? $this->subject)->id, 'semester_id' => $sem->id])->first()?->grade;
    }

    public function test_only_the_active_semester_of_the_active_year_accepts_grades(): void
    {
        $this->assertTrue($this->now->acceptsGrades());
        $this->assertFalse($this->sameYear->acceptsGrades(), 'other semester of the active year is closed');
        $this->assertFalse($this->past->acceptsGrades());
        $this->assertTrue($this->past->isGradeLocked());

        $this->now->schoolYear->update(['status' => 'completed']);
        $this->assertFalse($this->now->fresh()->acceptsGrades(), 'active semester of a closed year is closed');
    }

    public function test_new_grade_in_closed_semester_is_refused(): void
    {
        foreach ([$this->past, $this->sameYear] as $sem) {
            $this->byStudent($sem, 2.50, [], null, $this->firstSemSubject)
                ->assertSessionHas('error', fn ($m) => str_contains($m, 'are closed'));
            $this->assertNull($this->gradeOf($this->s1, $sem, $this->firstSemSubject));
        }
        $this->assertSame(0, Enrollment::where('student_id', $this->s1->id)->count(), 'no enrollment created either');
    }

    public function test_change_in_closed_semester_is_refused_even_with_password(): void
    {
        $this->existing($this->s1, $this->past, 2.50);
        $this->byStudent($this->past, 1.75, ['confirm_password' => 'password', 'override_reason' => 'INC completed, re-exam taken'], null, $this->firstSemSubject)
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'are closed'));
        $this->assertEquals(2.50, $this->gradeOf($this->s1, $this->past, $this->firstSemSubject)->fresh()->grade);
    }

    public function test_active_semester_works_as_before(): void
    {
        $this->byStudent($this->now, 3.00)->assertRedirect(route('registrar.students.profile', $this->s1));
        $this->byStudent($this->now, 2.00)->assertSessionHas('password_error', 'Incorrect password. Nothing was changed.');
        $this->byStudent($this->now, 2.00, ['confirm_password' => 'password'])->assertSessionMissing('password_error');
        $this->assertEquals(2.00, $this->gradeOf($this->s1, $this->now)->fresh()->grade);
    }

    public function test_by_subject(): void
    {
        $post = fn (Semester $sem, Subject $subject, $grades, $extra = []) => $this->from('/x')->post(route('registrar.encode-grades.by-subject.store'),
            array_merge(['semester_id' => $sem->id, 'subject_id' => $subject->id, 'grades' => $grades], $extra));

        $this->existing($this->s1, $this->past, 2.50);
        $post($this->past, $this->firstSemSubject, [$this->s1->id => '1.50'], ['confirm_password' => 'password'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'are closed'));
        $this->assertEquals(2.50, $this->gradeOf($this->s1, $this->past, $this->firstSemSubject)->fresh()->grade);

        Enrollment::create(['student_id' => $this->s2->id, 'subject_id' => $this->subject->id, 'semester_id' => $this->now->id,
            'enrolled_by' => $this->reg->id, 'enrollment_date' => now(), 'status' => 'enrolled']);
        $post($this->now, $this->subject, [$this->s2->id => '3.00'])->assertSessionHas('success');
        $this->assertEquals(3.00, $this->gradeOf($this->s2, $this->now)->grade);
    }

    public function test_quick_grade(): void
    {
        $url = fn (Student $s) => route('registrar.students.quick-grade', $s);

        // Edit an existing grade of a closed semester: refused.
        $this->existing($this->s1, $this->past, 2.50);
        $this->postJson($url($this->s1), ['subject_id' => $this->firstSemSubject->id, 'semester_id' => $this->past->id, 'grade' => 1.25, 'confirm_password' => 'password'])
            ->assertStatus(422)->assertJsonPath('error', fn ($m) => str_contains($m, 'are closed'));
        $this->assertEquals(2.50, $this->gradeOf($this->s1, $this->past, $this->firstSemSubject)->fresh()->grade);

        // New grade for a 1st Semester subject while the 2nd Semester is active: refused.
        $this->postJson($url($this->s2), ['subject_id' => $this->firstSemSubject->id, 'grade' => 2.0])->assertStatus(422);
        $this->assertSame(0, Enrollment::where('student_id', $this->s2->id)->count());

        // New grade for a subject of the active semester: saved there.
        $this->postJson($url($this->s2), ['subject_id' => $this->subject->id, 'grade' => 2.0])->assertOk()->assertJsonPath('locked', false);
        $this->assertNotNull($this->gradeOf($this->s2, $this->now));
    }

    public function test_history_reports_lock_and_what_can_be_encoded(): void
    {
        $this->existing($this->s1, $this->past, 2.50);
        $json = $this->getJson(route('registrar.students.grade-history', $this->s1))->assertOk();
        $rows = collect($json->json('rows'))->keyBy('subject_id');

        $this->assertTrue($rows[$this->firstSemSubject->id]['locked']);
        $this->assertFalse($rows[$this->firstSemSubject->id]['can_encode']);
        $this->assertTrue($rows[$this->subject->id]['can_encode'], 'subject of the active semester');
        $this->assertSame('2nd Semester 2024-2025', $json->json('open_semester'));
    }

    public function test_unfinalize(): void
    {
        $this->byStudent($this->now, 2.00, [], $this->s1);
        $this->byStudent($this->now, 2.00, [], $this->s2);
        $url = route('registrar.students.semesters.unfinalize', [$this->s1, $this->now]);
        $this->from('/p')->post($url)->assertSessionHas('password_error');
        $this->from('/p')->post($url, ['confirm_password' => 'password'])->assertSessionHas('success');
        $this->assertSame('approved_by_head_of_department', $this->gradeOf($this->s1, $this->now)->fresh()->status);
        $this->assertSame('finalized', $this->gradeOf($this->s2, $this->now)->fresh()->status, 'other student untouched');

        $this->existing($this->s1, $this->past, 2.00);
        $this->from('/p')->post(route('registrar.students.semesters.unfinalize', [$this->s1, $this->past]), ['confirm_password' => 'password'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'are closed'));
        $this->assertSame('finalized', $this->gradeOf($this->s1, $this->past, $this->firstSemSubject)->fresh()->status);
    }

    public function test_pages_show_closed_semesters_as_view_only(): void
    {
        $this->existing($this->s1, $this->past, 2.50);
        $sy = $this->past->school_year_id;

        $this->get(route('registrar.encode-grades', ['school_year_id' => $sy, 'semester_id' => $this->past->id,
            'course_id' => $this->firstSemSubject->course_id, 'year_level' => 1, 'student_id' => $this->s1->id]))
            ->assertOk()->assertSee('1st Semester 2023-2024 is closed.', false)->assertSee('disabled', false)->assertDontSee('onclick="confirmSave()"', false);
        $this->get(route('registrar.encode-grades.by-subject', ['school_year_id' => $sy, 'semester_id' => $this->past->id, 'subject_id' => $this->firstSemSubject->id]))
            ->assertOk()->assertSee('is closed.', false)->assertDontSee('onclick="confirmSubjectSave()"', false);
        $this->get(route('registrar.encode-grades', ['school_year_id' => $this->now->school_year_id, 'semester_id' => $this->now->id,
            'course_id' => $this->subject->course_id, 'year_level' => 1, 'student_id' => $this->s1->id]))
            ->assertOk()->assertDontSee('is closed.</strong>', false)->assertSee('onclick="confirmSave()"', false);
        $this->get(route('registrar.students.profile', $this->s1))->assertOk()
            ->assertDontSee(route('registrar.students.semesters.unfinalize', [$this->s1, $this->past]), false);
    }
}
