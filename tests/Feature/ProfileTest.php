<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'last_name' => 'Dela Cruz',
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'username' => 'JDelaCruz',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Juan S. Dela Cruz', $user->name);
        $this->assertSame('jdelacruz', $user->username);
    }

    public function test_username_must_be_unique_and_without_spaces(): void
    {
        $other = User::factory()->create(['username' => 'taken']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['last_name' => 'A', 'first_name' => 'B', 'username' => 'taken'])
            ->assertSessionHasErrors('username');
        $this->actingAs($user)->patch('/profile', ['last_name' => 'A', 'first_name' => 'B', 'username' => 'two words'])
            ->assertSessionHasErrors('username');
    }

    public function test_users_cannot_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertStatus(405);

        $this->assertNotNull($user->fresh());
    }
}
