<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * Public self-registration is disabled: accounts are created by an Admin only.
 * No database access, so this test is safe to run on its own.
 */
class RegistrationTest extends TestCase
{
    public function test_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_registration_endpoint_is_not_available(): void
    {
        $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
    }
}
