<?php

namespace Tests\Feature\Release;

use App\Models\{Course, Enrollment, Grade, Semester, Student, Subject, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 8b: student section + in-system masterlist with Grade / Re-Ex entry. */
class MasterlistTest extends TestCase
{
    use RefreshDatabase;

    private User $reg;
    private Semester $open;
    private Semester $closed;
    private Course $course;
    private Subject $a;
    private Subject $b;
    private Student $m1;
    private Student $f1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        $this->reg = User::where('email', 'registrar@cogtor.test')->firstOrFail();
        $this->open = Semester::where('status', 'active')->firstOrFail();
        $this->closed = Semester::where('school_year_id', $this->open->school_year_id)->where('semester_name', '1st Semester')->firstOrFail();
        $this->course = Course::firstOrFail();
        $mkSubject = fn ($code) => Subject::create(['course_id' => $this->course->id, 'code' => $code, 'name' => "Masterlist $code", 'units' => 3,
            'year_level' => 4, 'semester' => $this->open->semester_name, 'status' => 'active']);
        $this->a = $mkSubject('ML-A');
        $this->b = $mkSubject('ML-B');
        $mk = fn ($n, $g, $sec) => Student::create(['student_number' => $n, 'course_id' => $this->course->id, 'first_name' => 'T', 'last_name' => 'Z' . $n,
            'birth_date' => '2003-01-01', 'gender' => $g, 'email' => "$n@x.test", 'year_level' => 4, 'section' => $sec, 'student_type' => 'Regular', 'status' => 'active']);
        $this->m1 = $mk('26-8001', 'Male', 'a ');
        $this->f1 = $mk('26-8002', 'Female', 'B');
        foreach ([$this->m1, $this->f1] as $s) {
            $this->enroll($s, $this->a, $this->open);
        }
        $this->enroll($this->m1, $this->b, $this->open); // f1 not enrolled in B
        $this->actingAs($this->reg);
    }

    private function enroll(Student $s, Subject $subject, Semester $sem): Enrollment
    {
        return Enrollment::create(['student_id' => $s->id, 'subject_id' => $subject->id, 'semester_id' => $sem->id,
            'enrolled_by' => $this->reg->id, 'enrollment_date' => now(), 'status' => 'enrolled']);
    }

    private function enrollmentId(Student $s, Subject $subject): int
    {
        return Enrollment::where(['student_id' => $s->id, 'subject_id' => $subject->id, 'semester_id' => $this->open->id])->value('id');
    }

    private function page(array $q = [])
    {
        return $this->get(route('registrar.encode-grades.masterlist', $q + ['course_id' => $this->course->id, 'year_level' => 4]));
    }

    private function save(array $grades, array $extra = [])
    {
        return $this->from(route('registrar.encode-grades.masterlist'))->post(route('registrar.encode-grades.masterlist.store'),
            array_merge(['semester_id' => $this->open->id, 'grades' => $grades, 'expected' => count($grades)], $extra));
    }

    public function test_section_is_stored_in_capitals_and_validated(): void
    {
        $this->assertSame('A', $this->m1->fresh()->section);
        Student::whereKey($this->f1->id)->first()->update(['section' => '  ']);
        $this->assertNull($this->f1->fresh()->section);

        $this->from('/x')->put(route('registrar.students.update', $this->m1), [
            'student_number' => $this->m1->student_number, 'course_id' => $this->course->id, 'first_name' => 'T', 'last_name' => 'Z',
            'birth_date' => '2003-01-01', 'gender' => 'Male', 'email' => $this->m1->email, 'year_level' => 4, 'student_type' => 'Regular',
            'status' => 'active', 'section' => 'A<b>',
        ])->assertSessionHasErrors('section');
    }

    public function test_page_shows_grid_split_by_gender_and_filters_by_section(): void
    {
        $this->page()->assertOk()
            ->assertSee('ML-A')->assertSee('ML-B')->assertSee('MALE (1)')->assertSee('FEMALE (1)')
            ->assertSee('name="grades[' . $this->enrollmentId($this->m1, $this->b) . ']"', false)
            ->assertSee('Not enrolled in ML-B', false)
            ->assertSee('Section A')->assertSee('Section B')
            ->assertSee('onclick="saveMasterlist()"', false);

        $this->page(['section' => 'b'])->assertOk()->assertSee('FEMALE (1)')->assertDontSee('">MALE (', false);
        $this->page(['semester_id' => $this->closed->id])->assertOk()->assertSee('has no Year 4 subjects', false);
    }

    public function test_save_new_grades_and_re_exam(): void
    {
        $ma = $this->enrollmentId($this->m1, $this->a);
        $fa = $this->enrollmentId($this->f1, $this->a);
        $this->save([$ma => '1.75', $fa => '3.00'], ['re_exam' => [$fa => '2.25']])->assertSessionHas('success', '2 grade(s) saved and finalized.');

        $this->assertEquals(1.75, Enrollment::find($ma)->grade->grade);
        $fGrade = Enrollment::find($fa)->grade;
        $this->assertEquals(3.00, $fGrade->grade);
        $this->assertEquals(2.25, $fGrade->re_exam_grade);
        $this->assertSame('finalized', $fGrade->status);
        $this->assertNotNull($fGrade->submission?->finalized_at);
    }

    public function test_changing_a_saved_grade_needs_password_and_unchanged_cells_are_not_resaved(): void
    {
        $ma = $this->enrollmentId($this->m1, $this->a);
        $mb = $this->enrollmentId($this->m1, $this->b);
        $this->save([$ma => '2.00', $mb => '2.50']);

        $this->save([$ma => '1.50'])->assertSessionHas('password_error');
        $this->assertEquals(2.00, Enrollment::find($ma)->grade->fresh()->grade);
        $this->save([$ma => '1.50'], ['confirm_password' => 'password'])->assertSessionHas('success', '1 grade(s) saved and finalized.');
        $this->assertEquals(1.50, Enrollment::find($ma)->grade->fresh()->grade);

        $this->save([$mb => '2.50'])->assertSessionHas('success', '0 grade(s) saved and finalized.');
    }

    public function test_refusals(): void
    {
        $ma = $this->enrollmentId($this->m1, $this->a);

        // closed semester
        $this->save([$ma => '1.00'], ['semester_id' => $this->closed->id])->assertSessionHas('error', fn ($m) => str_contains($m, 'are closed'));
        // the form was cut short
        $this->save([$ma => '1.00'], ['expected' => 2])->assertSessionHas('error', fn ($m) => str_contains($m, 'Nothing was saved') || str_contains($m, 'nothing was saved'));
        // re-exam without grade
        $this->save([$ma => ''], ['re_exam' => [$ma => '2.00']])->assertSessionHas('error', fn ($m) => str_contains($m, 're-exam'));
        // enrollment of another semester
        $other = $this->enroll($this->f1, $this->b, $this->closed);
        $this->save([$other->id => '1.00'])->assertSessionHas('error', fn ($m) => str_contains($m, 'no longer match'));
        // three decimals
        $this->save([$ma => '1.255'])->assertSessionHasErrors('grades.' . $ma);

        $this->assertSame(0, Grade::count());
    }

    public function test_closed_semester_grid_is_view_only(): void
    {
        $this->a->update(['semester' => '1st Semester']);
        $this->enroll($this->m1, $this->a, $this->closed);
        $this->page(['semester_id' => $this->closed->id])->assertOk()
            ->assertSee('is closed.', false)->assertDontSee('onclick="saveMasterlist()"', false);
    }

    public function test_excel_export_by_section(): void
    {
        $this->save([$this->enrollmentId($this->m1, $this->a) => '1.75', $this->enrollmentId($this->f1, $this->a) => '2.00']);
        $sy = $this->open->school_year_id;
        $this->get(route('registrar.excel.masterlist-export', ['school_year_id' => $sy, 'course_id' => $this->course->id, 'year_level' => 4, 'section' => 'a']))
            ->assertOk()->assertDownload('masterlist_' . $this->course->code . '_Y4_A_' . $this->open->schoolYear->year_code . '.xlsx');
        $this->from('/x')->get(route('registrar.excel.masterlist-export', ['school_year_id' => $sy, 'course_id' => $this->course->id, 'year_level' => 4, 'section' => 'Z']))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Section Z'));
    }

    public function test_bulk_template_has_section_column(): void
    {
        $this->assertSame('Section', \App\Support\StudentBulkImport::COLUMNS['section']);
        $this->get(route('registrar.students.bulk.template'))->assertOk();
    }
}
