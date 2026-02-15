<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Department;
use App\Models\Course;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create School Years
        $sy2024 = SchoolYear::create([
            'year_code' => '2024-2025',
            'start_date' => '2024-08-01',
            'end_date' => '2025-05-31',
            'status' => 'active',
        ]);

        $sy2025 = SchoolYear::create([
            'year_code' => '2025-2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-05-31',
            'status' => 'upcoming',
        ]);

        // Create Semesters for 2024-2025
        Semester::create([
            'school_year_id' => $sy2024->id,
            'semester_name' => '1st Semester',
            'semester_order' => 1,
            'start_date' => '2024-08-01',
            'end_date' => '2024-12-20',
            'status' => 'completed',
        ]);

        Semester::create([
            'school_year_id' => $sy2024->id,
            'semester_name' => '2nd Semester',
            'semester_order' => 2,
            'start_date' => '2025-01-06',
            'end_date' => '2025-05-31',
            'status' => 'active',
        ]);

        Semester::create([
            'school_year_id' => $sy2024->id,
            'semester_name' => 'Summer',
            'semester_order' => 3,
            'start_date' => '2025-06-01',
            'end_date' => '2025-07-31',
            'status' => 'upcoming',
        ]);

        // Create Departments
        $ccs = Department::create([
            'code' => 'CCS',
            'name' => 'College of Computer Studies',
            'description' => 'Information Technology and Computer Science programs',
            'status' => 'active',
        ]);

        $coe = Department::create([
            'code' => 'COE',
            'name' => 'College of Engineering',
            'description' => 'Engineering programs',
            'status' => 'active',
        ]);

        $coed = Department::create([
            'code' => 'COED',
            'name' => 'College of Education',
            'description' => 'Education programs',
            'status' => 'active',
        ]);

        // Create Courses under CCS
        Course::create([
            'department_id' => $ccs->id,
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'years' => 4,
            'status' => 'active',
        ]);

        Course::create([
            'department_id' => $ccs->id,
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
            'years' => 4,
            'status' => 'active',
        ]);

        // Create Courses under COE
        Course::create([
            'department_id' => $coe->id,
            'code' => 'BSCpE',
            'name' => 'Bachelor of Science in Computer Engineering',
            'years' => 5,
            'status' => 'active',
        ]);

        Course::create([
            'department_id' => $coe->id,
            'code' => 'BSEE',
            'name' => 'Bachelor of Science in Electrical Engineering',
            'years' => 5,
            'status' => 'active',
        ]);

        // Create Courses under COED
        Course::create([
            'department_id' => $coed->id,
            'code' => 'BSED',
            'name' => 'Bachelor of Secondary Education',
            'years' => 4,
            'status' => 'active',
        ]);

        $this->command->info('Academic structure created successfully!');
        $this->command->info('- 2 School Years');
        $this->command->info('- 3 Semesters');
        $this->command->info('- 3 Departments');
        $this->command->info('- 5 Courses');
    }
}
