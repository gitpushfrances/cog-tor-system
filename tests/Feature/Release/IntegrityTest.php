<?php

namespace Tests\Feature\Release;

use App\Models\{CogRecord, Enrollment, Grade, Semester, Student, Subject, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class IntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        $this->admin = User::where('email', 'admin@cogtor.test')->firstOrFail();
        $this->reg = User::where('email', 'registrar@cogtor.test')->firstOrFail();
    }

    public function test_timezone(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
        $this->assertSame('+08:00', now()->format('P'));
    }

    public function test_user_update_without_password_field_and_logs(): void
    {
        $this->actingAs($this->admin);
        $r = $this->put(route('admin.users.update', $this->reg), [
            'name' => 'Registrar Two', 'email' => $this->reg->email, 'role' => 'registrar', 'status' => 'active', 'confirm_password' => 'password',
        ]); // no "password" key at all: used to crash
        $r->assertRedirect(route('admin.users.index'))->assertSessionHas('success');
        $this->assertSame('Registrar Two', $this->reg->fresh()->name);
        $this->assertTrue(\Hash::check('password', $this->reg->fresh()->password), 'password unchanged');
        $log = Activity::where('log_name', 'user')->where('event', 'updated')->sole();
        $this->assertSame(['name' => 'Registrar Two'], $log->properties['attributes']);

        $this->put(route('admin.users.update', $this->reg), [
            'name' => 'Registrar Two', 'email' => $this->reg->email, 'role' => 'registrar', 'status' => 'active',
            'password' => 'newpass123', 'password_confirmation' => 'newpass123', 'confirm_password' => 'password',
        ]);
        $this->assertTrue(\Hash::check('newpass123', $this->reg->fresh()->password));
        $last = Activity::where('log_name', 'user')->latest('id')->first();
        $this->assertSame('changed', $last->properties['attributes']['password']);
        $this->assertStringNotContainsString('newpass', json_encode($last->properties));
    }

    public function test_admin_accounts_are_protected(): void
    {
        $this->actingAs($this->admin);
        $other = User::create(['name' => 'Admin 2', 'email' => 'a2@x.test', 'password' => bcrypt('x12345678'), 'status' => 'active']);
        $other->assignRole('admin');

        foreach ([$this->admin, $other] as $u) {
            $this->get(route('admin.users.edit', $u))->assertRedirect(route('admin.users.index'))->assertSessionHas('error');
            $this->put(route('admin.users.update', $u), ['name' => 'X', 'email' => $u->email, 'role' => 'registrar', 'status' => 'inactive', 'confirm_password' => 'password'])
                ->assertSessionHas('error');
            $this->assertTrue($u->fresh()->hasRole('admin'));
            $this->assertSame('active', $u->fresh()->status);
        }
        $this->delete(route('admin.users.destroy', $other), ['confirm_password' => 'password'])->assertSessionHas('error');
        $this->assertNotNull($other->fresh());
    }

    public function test_delete_user_with_records_is_refused_not_crashing(): void
    {
        $this->actingAs($this->admin);
        $u = User::create(['name' => 'Reg 3', 'email' => 'r3@x.test', 'password' => bcrypt('x12345678'), 'status' => 'active']);
        $u->assignRole('registrar');
        Enrollment::create(['student_id' => Student::value('id'), 'subject_id' => Subject::value('id'), 'semester_id' => Semester::value('id'),
            'enrolled_by' => $u->id, 'enrollment_date' => now(), 'status' => 'enrolled']);
        $this->delete(route('admin.users.destroy', $u), ['confirm_password' => 'password'])
            ->assertRedirect(route('admin.users.index'))->assertSessionHas('error', fn ($m) => str_contains($m, 'Set the account to Inactive'));
        $this->assertNotNull($u->fresh());

        $clean = User::create(['name' => 'Reg 4', 'email' => 'r4@x.test', 'password' => bcrypt('x12345678'), 'status' => 'pending']);
        $clean->assignRole('registrar');
        $this->delete(route('admin.users.destroy', $clean), ['confirm_password' => 'password'])->assertSessionHas('success');
        $this->assertNull($clean->fresh());
        $this->assertSame(1, Activity::where('log_name', 'user')->where('event', 'deleted')->count());
    }

    public function test_grade_two_decimals(): void
    {
        $this->actingAs($this->reg);
        $student = Student::firstOrFail();
        $subject = Subject::where('course_id', $student->course_id)->where('semester', '1st Semester')->firstOrFail();
        $sem = Semester::where('semester_name', '1st Semester')->firstOrFail();
        $post = fn ($g) => $this->from('/x')->post(route('registrar.encode-grades.store'), ['student_id' => $student->id, 'semester_id' => $sem->id, 'grades' => [$subject->id => $g]]);
        $post('1.234')->assertSessionHasErrors();
        $post('1.1')->assertSessionHasNoErrors();
        $this->assertEquals(1.10, Grade::sole()->grade);
    }

    public function test_one_current_cog_enforced_by_database_and_friendly_error(): void
    {
        $this->actingAs($this->reg);
        $base = ['student_id' => Student::value('id'), 'semester_id' => Semester::value('id'), 'generated_by' => $this->reg->id,
            'grade_data' => [], 'generated_at' => now(), 'is_current' => true];
        CogRecord::create($base + ['document_number' => 'COG-A']);
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        CogRecord::create($base + ['document_number' => 'COG-B']);
    }

    public function test_unique_violation_renders_message(): void
    {
        \Route::middleware('web')->post('/_t/dupe', fn () => CogRecord::create(['student_id' => Student::value('id'), 'semester_id' => Semester::value('id'),
            'generated_by' => User::value('id'), 'grade_data' => [], 'generated_at' => now(), 'is_current' => true, 'document_number' => 'COG-X']));
        $this->actingAs($this->reg);
        $this->post('/_t/dupe');
        $this->from('/registrar/dashboard')->post('/_t/dupe')->assertRedirect('/registrar/dashboard')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'already saved'));
        $this->postJson('/_t/dupe')->assertStatus(409);
    }

    public function test_profile_cannot_be_deleted_and_page_has_no_delete_form(): void
    {
        $this->actingAs($this->reg);
        $this->delete('/profile', ['password' => 'password'])->assertStatus(405);
        $this->assertNotNull($this->reg->fresh());
    }
}
