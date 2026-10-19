<?php

namespace Tests\Feature\Release;

use App\Models\{Course, Department, Enrollment, Major, Semester, Student, Subject, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTreeTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        $this->actingAs(User::where('email', 'admin@cogtor.test')->firstOrFail());
        $this->course = Course::where('code', 'BSIT')->firstOrFail();
        $this->dept = $this->course->department;
    }

    private function page(Course $c): string
    {
        return route('admin.departments.show', [$c->department_id, 'course' => $c->id]) . '#course-' . $c->id;
    }

    public function test_index_and_show(): void
    {
        $this->get(route('admin.departments.index'))->assertOk()
            ->assertSee(route('admin.departments.show', $this->dept), false)->assertSee('Subjects');
        $r = $this->get(route('admin.departments.show', [$this->dept, 'course' => $this->course->id]))->assertOk();
        $r->assertSee($this->course->name)->assertSee('IT 101')->assertSee('Year 1 · 1st Semester')
          ->assertSee('id="course-' . $this->course->id . '" style', false)
          ->assertSee(route('admin.subjects.create', ['course_id' => $this->course->id]), false)
          ->assertSee(route('admin.majors.create', ['course_id' => $this->course->id]), false)
          ->assertSee(route('admin.courses.create', ['department_id' => $this->dept->id]), false);
        $this->assertMatchesRegularExpression('/id="course-' . $this->course->id . '"[^>]*open/', $r->getContent());
    }

    public function test_old_pages_and_show_urls(): void
    {
        foreach (['courses', 'majors', 'subjects'] as $what) {
            $this->get(route("admin.$what.index"))->assertRedirect(route('admin.departments.index'));
        }
        $this->get(route('admin.courses.show', $this->course))->assertRedirect($this->page($this->course));
        $subject = Subject::where('course_id', $this->course->id)->firstOrFail();
        $this->get(route('admin.subjects.show', $subject))->assertRedirect($this->page($this->course));
        $major = Major::create(['course_id' => $this->course->id, 'code' => 'WD', 'name' => 'Web Dev', 'status' => 'active']);
        $this->get(route('admin.majors.show', $major))->assertRedirect($this->page($this->course));
    }

    public function test_create_forms_preselect_and_cancel(): void
    {
        $this->get(route('admin.courses.create', ['department_id' => $this->dept->id]))->assertOk()
            ->assertSee('value="' . $this->dept->id . '" selected', false)->assertSee(route('admin.departments.show', $this->dept), false);
        $this->get(route('admin.majors.create', ['course_id' => $this->course->id]))->assertOk()
            ->assertSee('value="' . $this->course->id . '" selected', false)->assertSee($this->page($this->course), false);
        $this->get(route('admin.subjects.create', ['course_id' => $this->course->id]))->assertOk()
            ->assertSee($this->page($this->course), false);
        $this->get(route('admin.courses.edit', $this->course))->assertOk()->assertSee($this->page($this->course), false);
    }

    public function test_saves_and_deletes_return_to_department(): void
    {
        $r = $this->post(route('admin.courses.store'), ['department_id' => $this->dept->id, 'code' => 'BSX', 'name' => 'X', 'years' => 4, 'status' => 'active']);
        $new = Course::where('code', 'BSX')->sole();
        $r->assertRedirect($this->page($new));

        $this->post(route('admin.majors.store'), ['course_id' => $new->id, 'code' => 'M1', 'name' => 'M', 'status' => 'active'])->assertRedirect($this->page($new));
        $major = Major::where('code', 'M1')->sole();
        $this->put(route('admin.majors.update', $major), ['course_id' => $new->id, 'code' => 'M1', 'name' => 'M2', 'status' => 'active'])->assertRedirect($this->page($new));

        $this->post(route('admin.subjects.store'), ['course_id' => $new->id, 'code' => 'X 1', 'name' => 'S', 'units' => 3, 'year_level' => 1, 'semester' => '1st Semester', 'status' => 'active'])
            ->assertRedirect($this->page($new));
        $subject = Subject::where('code', 'X 1')->sole();
        $this->put(route('admin.subjects.update', $subject), ['course_id' => $new->id, 'code' => 'X 1', 'name' => 'S2', 'units' => 3, 'year_level' => 1, 'semester' => '2nd Semester', 'status' => 'active'])
            ->assertRedirect($this->page($new));

        // course with a subject cannot be deleted
        $this->delete(route('admin.courses.destroy', $new), ['confirm_password' => 'password'])->assertRedirect($this->page($new))->assertSessionHas('error');
        $this->delete(route('admin.subjects.destroy', $subject), ['confirm_password' => 'password'])->assertRedirect($this->page($new))->assertSessionHas('success');
        $this->delete(route('admin.majors.destroy', $major), ['confirm_password' => 'password'])->assertRedirect($this->page($new));
        $this->delete(route('admin.courses.destroy', $new), ['confirm_password' => 'password'])->assertRedirect(route('admin.departments.show', $this->dept))->assertSessionHas('success');
        $this->assertSoftDeleted($new);

        // subject with enrollments cannot be deleted; wrong password changes nothing
        $used = Subject::where('course_id', $this->course->id)->firstOrFail();
        Enrollment::create(['student_id' => Student::value('id'), 'subject_id' => $used->id, 'semester_id' => Semester::value('id'), 'enrolled_by' => User::value('id'), 'enrollment_date' => now(), 'status' => 'enrolled']);
        $this->delete(route('admin.subjects.destroy', $used), ['confirm_password' => 'password'])->assertRedirect($this->page($this->course))->assertSessionHas('error');
        $this->from('/x')->delete(route('admin.subjects.destroy', Subject::where('course_id', $this->course->id)->where('id', '!=', $used->id)->first()), ['confirm_password' => 'wrong'])
            ->assertSessionHas('password_error');

        // moving a course to another department lands on the new department
        $other = Department::where('id', '!=', $this->dept->id)->firstOrFail();
        $this->put(route('admin.courses.update', $this->course), ['department_id' => $other->id, 'code' => 'BSIT', 'name' => $this->course->name, 'years' => 4, 'status' => 'active'])
            ->assertRedirect(route('admin.departments.show', [$other->id, 'course' => $this->course->id]) . '#course-' . $this->course->id);

        $this->post(route('admin.departments.store'), ['code' => 'NEW', 'name' => 'New Dept', 'status' => 'active'])
            ->assertRedirect(route('admin.departments.show', Department::where('code', 'NEW')->sole()));
    }

    public function test_empty_department_and_sidebar(): void
    {
        $d = Department::create(['code' => 'EMP', 'name' => 'Empty', 'status' => 'active']);
        $r = $this->get(route('admin.departments.show', $d))->assertOk()->assertSee('No courses in this department yet.');
        $html = $r->getContent();
        $this->assertStringNotContainsString(route('admin.subjects.index'), $html, 'sidebar no longer links the flat Subjects page');
        $this->assertStringNotContainsString('Academic Setup', $html);
    }
}
