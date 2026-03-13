<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            AcademicStructureSeeder::class,
            SubjectSeeder::class,
            StudentSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('DATABASE SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('TEST ACCOUNTS:');
        $this->command->info('Admin:     admin@cogtor.test / password');
        $this->command->info('HOD:       hod@cogtor.test / password');
        $this->command->info('Faculty:   faculty@cogtor.test / password');
        $this->command->info('Registrar: registrar@cogtor.test / password');
        $this->command->info('');
        $this->command->info('DATA CREATED:');
        $this->command->info('- 4 Roles with permissions');
        $this->command->info('- 5 User accounts');
        $this->command->info('- 2 School years');
        $this->command->info('- 3 Semesters');
        $this->command->info('- 3 Departments');
        $this->command->info('- 5 Courses');
        $this->command->info('- 10 Subjects');
        $this->command->info('- 10 Students');
        $this->command->info('');
    }
}
