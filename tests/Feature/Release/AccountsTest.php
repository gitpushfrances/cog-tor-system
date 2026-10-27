<?php

namespace Tests\Feature\Release;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Phase 9a, panel recommendations: Last/First/Middle name, username sign-in, no "Pending", pop-ups at sign-in. */
class AccountsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        $this->admin = User::where('username', 'admin')->firstOrFail();
        $this->reg = User::where('username', 'registrar')->firstOrFail();
    }

    public function test_sign_in_with_username_any_case(): void
    {
        $this->post('/login', ['username' => ' Registrar ', 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($this->reg);
    }

    public function test_email_no_longer_signs_in(): void
    {
        $this->from('/login')->post('/login', ['username' => 'registrar@cogtor.test', 'password' => 'password'])
            ->assertSessionHasErrors(['username' => 'Invalid username or password.']);
        $this->assertGuest();
    }

    public function test_wrong_password_and_inactive_account_get_their_own_message(): void
    {
        $this->from('/login')->post('/login', ['username' => 'registrar', 'password' => 'wrong'])
            ->assertSessionHasErrors(['username' => 'Invalid username or password.']);

        $this->reg->update(['status' => 'inactive']);
        $this->from('/login')->post('/login', ['username' => 'registrar', 'password' => 'wrong'])
            ->assertSessionHasErrors(['username' => 'Invalid username or password.']);
        $this->from('/login')->post('/login', ['username' => 'registrar', 'password' => 'password'])
            ->assertSessionHasErrors(['username' => 'This account is inactive. Please contact the administrator.']);

        $this->assertGuest();
        $this->assertSame(0, Activity::where('event', 'login')->count(), 'an inactive account never signs in');
        $this->assertSame(3, Activity::where('event', 'failed_login')->count());
        $this->assertSame('registrar', Activity::where('event', 'failed_login')->latest('id')->first()->properties['username']);
    }

    public function test_account_deactivated_while_signed_in_is_signed_out_with_a_message(): void
    {
        $this->actingAs($this->reg);
        $this->reg->update(['status' => 'inactive']);
        $this->get(route('registrar.dashboard'))->assertRedirect(route('login'))
            ->assertSessionHas('error', 'This account is inactive. Please contact the administrator.');
        $this->assertGuest();
    }

    public function test_login_page_shows_the_problem_as_a_pop_up(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="username"', false)->assertDontSee('name="email"', false)
            ->assertDontSee('Swal.fire', false);

        $this->withSession(['errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['username' => 'This account is inactive. Please contact the administrator.']))])
            ->get('/login')->assertOk()->assertSee('Swal.fire', false)->assertSee('Account inactive')
            ->assertSee('vendor/sweetalert2/sweetalert2.all.min.js', false);
    }

    public function test_pending_status_is_gone(): void
    {
        $this->assertStringNotContainsString('pending', collect(\DB::select("SHOW COLUMNS FROM users LIKE 'status'"))->first()->Type);
        $this->actingAs($this->admin);
        $this->get(route('admin.users.create'))->assertOk()->assertDontSee('value="pending"', false);
        $this->post(route('admin.users.store'), $this->newUser(['status' => 'pending']))->assertSessionHasErrors('status');
        $this->assertFalse(\Route::has('admin.users.approve'));
    }

    public function test_admin_creates_account_with_name_parts_and_username(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('admin.users.create'))->assertOk()
            ->assertSee('name="last_name"', false)->assertSee('name="first_name"', false)->assertSee('name="middle_name"', false)
            ->assertSee('name="username"', false)->assertDontSee('name="email"', false);

        $this->post(route('admin.users.store'), $this->newUser())->assertRedirect(route('admin.users.index'));
        $user = User::where('username', 'mreyes')->firstOrFail();
        $this->assertSame(['Reyes', 'Maria', 'Lopez', 'Maria L. Reyes', null], [$user->last_name, $user->first_name, $user->middle_name, $user->name, $user->email]);
        $this->assertTrue($user->hasRole('registrar'));

        $this->get(route('admin.users.index'))->assertSee('Reyes, Maria Lopez')->assertSee('mreyes');

        // username: unique, no spaces
        $this->post(route('admin.users.store'), $this->newUser())->assertSessionHasErrors('username');
        $this->post(route('admin.users.store'), $this->newUser(['username' => 'm reyes']))->assertSessionHasErrors('username');
        $this->post(route('admin.users.store'), $this->newUser(['username' => 'other', 'last_name' => '']))->assertSessionHasErrors('last_name');

        // the new account signs in with its username
        auth()->logout();
        $this->post('/login', ['username' => 'mreyes', 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_seeded_accounts_have_usernames_and_name_parts(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['username', 'last_name', 'first_name', 'middle_name']));
        $this->assertSame('Registrar', $this->reg->last_name);
        $this->assertNull(User::where('email', 'pending@cogtor.test')->first());
    }

    private function newUser(array $override = []): array
    {
        return array_merge([
            'last_name' => 'Reyes', 'first_name' => 'Maria', 'middle_name' => 'Lopez', 'username' => 'MReyes',
            'password' => 'secret123', 'password_confirmation' => 'secret123', 'role' => 'registrar', 'status' => 'active',
        ], $override);
    }
}
