<?php

namespace Tests\Feature\Release;

use App\Models\{Course, Enrollment, Grade, SchoolYear, Semester, Student, Subject, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class GradeLockTest extends TestCase
{
    use RefreshDatabase;

    private User $reg;
    private Semester $past;
    private Semester $now;
    private Subject $subject;
    private Student $s1;
    private Student $s2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        $this->reg = User::where('email', 'registrar@cogtor.test')->firstOrFail();
        $sy = SchoolYear::create(['year_code' => '2023-2024', 'start_date' => '2023-08-01', 'end_date' => '2024-05-31', 'status' => 'completed']);
        $this->past = Semester::create(['school_year_id' => $sy->id, 'semester_name' => '1st Semester', 'semester_order' => 1,
            'start_date' => '2023-08-01', 'end_date' => '2023-12-20', 'status' => 'completed']);
        $this->now = Semester::where('semester_name', '1st Semester')->whereHas('schoolYear', fn ($q) => $q->where('status', 'active'))->firstOrFail();
        $course = Course::firstOrFail();
        $this->subject = Subject::create(['course_id' => $course->id, 'code' => 'LOCK-1', 'name' => 'Lock Test', 'units' => 3,
            'year_level' => 1, 'semester' => '1st Semester', 'status' => 'active']);
        $mk = fn ($n) => Student::create(['student_number' => $n, 'course_id' => $course->id, 'first_name' => 'T', 'last_name' => 'L' . $n,
            'birth_date' => '2004-01-01', 'gender' => 'Male', 'email' => "$n@x.test", 'year_level' => 1, 'student_type' => 'Regular', 'status' => 'active']);
        $this->s1 = $mk('26-7001');
        $this->s2 = $mk('26-7002');
        $this->actingAs($this->reg);
    }

    private function byStudent(Semester $sem, $grade, array $extra = [], ?Student $s = null)
    {
        return $this->from('/registrar/encode-grades')->post(route('registrar.encode-grades.store'), array_merge([
            'student_id' => ($s ?? $this->s1)->id, 'semester_id' => $sem->id, 'grades' => [$this->subject->id => $grade],
        ], $extra));
    }

    private function gradeOf(Student $s, Semester $sem): ?Grade
    {
        return Enrollment::where(['student_id' => $s->id, 'subject_id' => $this->subject->id, 'semester_id' => $sem->id])->first()?->grade;
    }

    private function ok(): array
    {
        return ['confirm_password' => 'password', 'override_reason' => 'INC completed, re-exam taken'];
    }

    public function test_semester_lock_flag(): void
    {
        $this->assertTrue($this->past->isGradeLocked());
        $this->assertFalse($this->now->isGradeLocked());
    }

    public function test_backlog_entry_in_past_year_needs_nothing(): void
    {
        $this->byStudent($this->past, 2.50)->assertRedirect(route('registrar.students.profile', $this->s1));
        $this->assertEquals(2.50, $this->gradeOf($this->s1, $this->past)->grade);
        $this->assertSame(0, Activity::where('event', 'override')->count());
    }

    public function test_past_year_change_needs_reason_and_password_and_is_logged(): void
    {
        $this->byStudent($this->past, 2.50);

        $this->byStudent($this->past, 1.75)->assertSessionHas('password_error', fn ($m) => str_contains($m, 'are locked'));
        $this->byStudent($this->past, 1.75, ['override_reason' => 'short', 'confirm_password' => 'password'])->assertSessionHas('password_error');
        $this->byStudent($this->past, 1.75, ['override_reason' => 'INC completed, re-exam taken', 'confirm_password' => 'wrong'])
            ->assertSessionHas('password_error', 'Incorrect password. Nothing was changed.');
        $this->assertEquals(2.50, $this->gradeOf($this->s1, $this->past)->grade);

        $this->byStudent($this->past, 2.50, [])->assertSessionMissing('password_error'); // unchanged value: no prompt

        $this->byStudent($this->past, 1.75, $this->ok())->assertSessionMissing('password_error');
        $this->assertEquals(1.75, $this->gradeOf($this->s1, $this->past)->fresh()->grade);

        $log = Activity::where('event', 'override')->sole();
        $this->assertSame('grade', $log->log_name);
        $this->assertSame($this->reg->id, $log->causer_id);
        $this->assertSame($this->gradeOf($this->s1, $this->past)->id, $log->subject_id);
        $this->assertSame('INC completed, re-exam taken', $log->properties['reason']);
        $this->assertEquals(2.5, $log->properties['old']['grade']);
        $this->assertEquals(1.75, $log->properties['attributes']['grade']);
        $this->assertSame('1st Semester 2023-2024', $log->properties['term']);
    }

    public function test_active_year_unchanged_behavior(): void
    {
        $this->byStudent($this->now, 3.00);
        $this->byStudent($this->now, 2.00)->assertSessionHas('password_error', 'Incorrect password. Nothing was changed.');
        $this->byStudent($this->now, 2.00, ['confirm_password' => 'password'])->assertSessionMissing('password_error');
        $this->assertEquals(2.00, $this->gradeOf($this->s1, $this->now)->fresh()->grade);
        $this->assertSame(0, Activity::where('event', 'override')->count());
    }

    public function test_by_subject(): void
    {
        $this->byStudent($this->past, 2.50, [], $this->s1);
        $post = fn ($grades, $extra = []) => $this->from('/x')->post(route('registrar.encode-grades.by-subject.store'), array_merge([
            'semester_id' => $this->past->id, 'subject_id' => $this->subject->id, 'grades' => $grades], $extra));

        Enrollment::create(['student_id' => $this->s2->id, 'subject_id' => $this->subject->id, 'semester_id' => $this->past->id,
            'enrolled_by' => $this->reg->id, 'enrollment_date' => '2023-08-10', 'status' => 'enrolled']);
        // s1 blank (skipped), s2 new: no prompt
        $post([$this->s1->id => '', $this->s2->id => '3.00'])->assertSessionMissing('password_error');
        $this->assertEquals(3.00, $this->gradeOf($this->s2, $this->past)->grade);
        // change s1: locked
        $post([$this->s1->id => '1.50'])->assertSessionHas('password_error', fn ($m) => str_contains($m, 'are locked'));
        $this->assertEquals(2.50, $this->gradeOf($this->s1, $this->past)->fresh()->grade);
        $post([$this->s1->id => '1.50'], $this->ok())->assertSessionMissing('password_error');
        $this->assertEquals(1.50, $this->gradeOf($this->s1, $this->past)->fresh()->grade);
        $this->assertSame(1, Activity::where('event', 'override')->count());
    }

    public function test_quick_grade_edits_in_place_and_is_locked(): void
    {
        $this->byStudent($this->past, 2.50);
        $url = route('registrar.students.quick-grade', $this->s1);
        $body = ['subject_id' => $this->subject->id, 'semester_id' => $this->past->id, 'grade' => 1.25];

        $this->postJson($url, $body)->assertStatus(422)->assertJsonPath('error', fn ($m) => str_contains($m, 'are locked'));
        $this->postJson($url, $body + $this->ok())->assertOk()->assertJsonPath('locked', true)->assertJsonPath('school_year', '2023-2024');

        $this->assertEquals(1.25, $this->gradeOf($this->s1, $this->past)->fresh()->grade);
        $this->assertNull($this->gradeOf($this->s1, $this->now), 'must not create a grade in the active year');
        $this->assertSame(1, Enrollment::where('student_id', $this->s1->id)->count());
        $this->assertSame(1, Activity::where('event', 'override')->count());

        // semester_id given but no grade there
        $this->postJson(route('registrar.students.quick-grade', $this->s2), $body + $this->ok())->assertStatus(422);

        // new grade (no semester_id) still goes to the active year
        $this->postJson(route('registrar.students.quick-grade', $this->s2), ['subject_id' => $this->subject->id, 'grade' => 2.0])->assertOk()
            ->assertJsonPath('locked', false);
        $this->assertNotNull($this->gradeOf($this->s2, $this->now));
    }

    public function test_history_reports_semester_and_lock(): void
    {
        $this->byStudent($this->past, 2.50);
        $row = collect($this->getJson(route('registrar.students.grade-history', $this->s1))->assertOk()->json('rows'))
            ->firstWhere('subject_id', $this->subject->id);
        $this->assertSame($this->past->id, $row['semester_id']);
        $this->assertTrue($row['locked']);
        $this->assertSame('2023-2024', $row['school_year']);
    }

    public function test_unfinalize_semester_scoped_to_student(): void
    {
        $this->byStudent($this->now, 2.00, [], $this->s1);
        $this->byStudent($this->now, 2.00, [], $this->s2);
        $url = route('registrar.students.semesters.unfinalize', [$this->s1, $this->now]);

        $this->from('/p')->post($url)->assertSessionHas('password_error');
        $this->from('/p')->post($url, ['confirm_password' => 'password'])->assertSessionHas('success');
        $this->assertSame('approved_by_head_of_department', $this->gradeOf($this->s1, $this->now)->fresh()->status);
        $this->assertSame('finalized', $this->gradeOf($this->s2, $this->now)->fresh()->status, 'other student untouched');
    }

    public function test_unfinalize_past_semester_needs_reason(): void
    {
        $this->byStudent($this->past, 2.00);
        $url = route('registrar.students.semesters.unfinalize', [$this->s1, $this->past]);
        $this->from('/p')->post($url, ['confirm_password' => 'password'])->assertSessionHas('password_error', fn ($m) => str_contains($m, 'are locked'));
        $this->assertSame('finalized', $this->gradeOf($this->s1, $this->past)->fresh()->status);
        $this->from('/p')->post($url, $this->ok())->assertSessionHas('success');
        $this->assertSame('approved_by_head_of_department', $this->gradeOf($this->s1, $this->past)->fresh()->status);
        $this->assertSame(1, Activity::where('event', 'override')->count());
    }

    public function test_old_subject_unfinalize_refuses_locked(): void
    {
        $this->byStudent($this->past, 2.00);
        $this->from('/p')->post(route('registrar.submissions.unfinalize-subject', $this->subject->id), ['confirm_password' => 'password'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'closed school year'));
        $this->assertSame('finalized', $this->gradeOf($this->s1, $this->past)->fresh()->status);
    }

    public function test_pages_render_lock_ui(): void
    {
        $this->byStudent($this->past, 2.50);
        $sy = $this->past->school_year_id;
        $this->get(route('registrar.encode-grades', ['school_year_id' => $sy, 'semester_id' => $this->past->id,
            'course_id' => $this->subject->course_id, 'year_level' => 1, 'student_id' => $this->s1->id]))
            ->assertOk()->assertSee('SY 2023-2024 is closed.', false)->assertSee('data-locked readonly', false);
        $this->get(route('registrar.encode-grades.by-subject', ['school_year_id' => $sy, 'semester_id' => $this->past->id, 'subject_id' => $this->subject->id]))
            ->assertOk()->assertSee('Correct past grades')->assertSee('data-locked readonly', false);
        $this->get(route('registrar.encode-grades', ['school_year_id' => $this->now->school_year_id, 'semester_id' => $this->now->id,
            'course_id' => $this->subject->course_id, 'year_level' => 1, 'student_id' => $this->s1->id]))
            ->assertOk()->assertDontSee('is closed.</strong>', false);
        $this->get(route('registrar.students.profile', $this->s1))->assertOk()
            ->assertSee(route('registrar.students.semesters.unfinalize', [$this->s1, $this->past]), false)->assertSee('data-locked="1"', false);
    }
}
