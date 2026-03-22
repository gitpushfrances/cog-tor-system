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
            ['email' => 'admin@cogtor.test'],
            [
                'name'              => 'System Administrator',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Head of Department User
        $hod = User::updateOrCreate(
            ['email' => 'hod@cogtor.test'],
            [
                'name'              => 'Head of CCS',
                'password'          => Hash::make('password'),
                'role'              => 'head_of_department',
                'status'            => 'active',
                'department_id'     => $department?->id,
                'approved_by'       => $admin->id,
                'approved_at'       => now(),
                'email_verified_at' => now(),
            ]
        );
        $hod->assignRole('head_of_department');

        // Faculty User
        $faculty = User::updateOrCreate(
            ['email' => 'faculty@cogtor.test'],
            [
                'name'              => 'Juan Dela Cruz',
                'password'          => Hash::make('password'),
                'role'              => 'faculty',
                'status'            => 'active',
                'department_id'     => $department?->id,
                'approved_by'       => $hod->id,
                'approved_at'       => now(),
                'email_verified_at' => now(),
            ]
        );
        $faculty->assignRole('faculty');

        // Registrar User
        $registrar = User::updateOrCreate(
            ['email' => 'registrar@cogtor.test'],
            [
                'name'              => 'University Registrar',
                'password'          => Hash::make('password'),
                'role'              => 'registrar',
                'status'            => 'active',
                'approved_by'       => $admin->id,
                'approved_at'       => now(),
                'email_verified_at' => now(),
            ]
        );
        $registrar->assignRole('registrar');

        // Pending Faculty (for testing approval workflow)
        $pendingFaculty = User::updateOrCreate(
            ['email' => 'pending@cogtor.test'],
            [
                'name'              => 'Maria Santos',
                'password'          => Hash::make('password'),
                'role'              => 'faculty',
                'status'            => 'pending',
                'email_verified_at' => now(),
            ]
        );
        $pendingFaculty->assignRole('faculty');

        $this->command->info('Test user accounts seeded successfully!');
        $this->command->info('Admin:     admin@cogtor.test / password');
        $this->command->info('HOD:       hod@cogtor.test / password (department: ' . ($department?->name ?? 'NONE - run AcademicStructureSeeder first') . ')');
        $this->command->info('Faculty:   faculty@cogtor.test / password');
        $this->command->info('Registrar: registrar@cogtor.test / password');
        $this->command->info('Pending:   pending@cogtor.test / password (cannot login)');
    }
}
