<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::first();

        // Admin User
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email'             => 'admin@cogtor.test',
                'first_name'        => 'System',
                'last_name'         => 'Administrator',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Registrar User
        $registrar = User::updateOrCreate(
            ['username' => 'registrar'],
            [
                'email'             => 'registrar@cogtor.test',
                'first_name'        => 'University',
                'last_name'         => 'Registrar',
                'password'          => Hash::make('password'),
                'role'              => 'registrar',
                'status'            => 'active',
                'approved_by'       => $admin->id,
                'approved_at'       => now(),
                'email_verified_at' => now(),
            ]
        );
        $registrar->assignRole('registrar');

        $this->command->info('Test user accounts seeded successfully!');
    }
}
