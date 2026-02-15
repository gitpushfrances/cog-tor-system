<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@cogtor.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Dean User
        $dean = User::create([
            'name' => 'Dean of CCS',
            'email' => 'dean@cogtor.test',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ]);
        $dean->assignRole('dean');

        // Faculty User
        $faculty = User::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'faculty@cogtor.test',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'status' => 'active',
            'approved_by' => $dean->id,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ]);
        $faculty->assignRole('faculty');

        // Registrar User
        $registrar = User::create([
            'name' => 'University Registrar',
            'email' => 'registrar@cogtor.test',
            'password' => Hash::make('password'),
            'role' => 'registrar',
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ]);
        $registrar->assignRole('registrar');

        // Pending Faculty (for testing approval workflow)
        $pendingFaculty = User::create([
            'name' => 'Maria Santos',
            'email' => 'pending@cogtor.test',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);
        $pendingFaculty->assignRole('faculty');

        $this->command->info('Test user accounts created successfully!');
        $this->command->info('Admin: admin@cogtor.test / password');
        $this->command->info('Dean: dean@cogtor.test / password');
        $this->command->info('Faculty: faculty@cogtor.test / password');
        $this->command->info('Registrar: registrar@cogtor.test / password');
        $this->command->info('Pending: pending@cogtor.test / password (cannot login)');
    }
}
