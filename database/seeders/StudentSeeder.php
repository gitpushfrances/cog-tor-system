<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Course;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $bsit = Course::where('code', 'BSIT')->first();
        $bscs = Course::where('code', 'BSCS')->first();

        // BSIT Students
        Student::create([
            'student_number' => '2024-00001',
            'course_id' => $bsit->id,
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'suffix' => null,
            'birth_date' => '2004-05-15',
            'gender' => 'Male',
            'email' => 'juan.delacruz@student.test',
            'phone' => '09171234567',
            'address' => 'Cebu City, Philippines',
            'year_level' => 3,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00002',
            'course_id' => $bsit->id,
            'first_name' => 'Maria',
            'middle_name' => 'Garcia',
            'last_name' => 'Santos',
            'suffix' => null,
            'birth_date' => '2005-08-20',
            'gender' => 'Female',
            'email' => 'maria.santos@student.test',
            'phone' => '09181234567',
            'address' => 'Mandaue City, Philippines',
            'year_level' => 3,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00003',
            'course_id' => $bsit->id,
            'first_name' => 'Pedro',
            'middle_name' => 'Reyes',
            'last_name' => 'Ramos',
            'suffix' => 'Jr.',
            'birth_date' => '2004-11-30',
            'gender' => 'Male',
            'email' => 'pedro.ramos@student.test',
            'phone' => '09191234567',
            'address' => 'Lapu-Lapu City, Philippines',
            'year_level' => 3,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00004',
            'course_id' => $bsit->id,
            'first_name' => 'Ana',
            'middle_name' => 'Torres',
            'last_name' => 'Gonzales',
            'suffix' => null,
            'birth_date' => '2005-02-14',
            'gender' => 'Female',
            'email' => 'ana.gonzales@student.test',
            'phone' => '09201234567',
            'address' => 'Talisay City, Philippines',
            'year_level' => 3,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00005',
            'course_id' => $bsit->id,
            'first_name' => 'Jose',
            'middle_name' => 'Cruz',
            'last_name' => 'Martinez',
            'suffix' => null,
            'birth_date' => '2004-09-10',
            'gender' => 'Male',
            'email' => 'jose.martinez@student.test',
            'phone' => '09211234567',
            'address' => 'Cebu City, Philippines',
            'year_level' => 3,
            'status' => 'active',
        ]);

        // BSCS Students
        Student::create([
            'student_number' => '2024-00006',
            'course_id' => $bscs->id,
            'first_name' => 'Luis',
            'middle_name' => 'Fernandez',
            'last_name' => 'Lopez',
            'suffix' => null,
            'birth_date' => '2003-07-05',
            'gender' => 'Male',
            'email' => 'luis.lopez@student.test',
            'phone' => '09221234567',
            'address' => 'Cebu City, Philippines',
            'year_level' => 4,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00007',
            'course_id' => $bscs->id,
            'first_name' => 'Sofia',
            'middle_name' => 'Mendoza',
            'last_name' => 'Alvarez',
            'suffix' => null,
            'birth_date' => '2003-12-18',
            'gender' => 'Female',
            'email' => 'sofia.alvarez@student.test',
            'phone' => '09231234567',
            'address' => 'Mandaue City, Philippines',
            'year_level' => 4,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00008',
            'course_id' => $bscs->id,
            'first_name' => 'Miguel',
            'middle_name' => 'Castillo',
            'last_name' => 'Morales',
            'suffix' => null,
            'birth_date' => '2003-04-22',
            'gender' => 'Male',
            'email' => 'miguel.morales@student.test',
            'phone' => '09241234567',
            'address' => 'Lapu-Lapu City, Philippines',
            'year_level' => 4,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00009',
            'course_id' => $bscs->id,
            'first_name' => 'Isabella',
            'middle_name' => 'Diaz',
            'last_name' => 'Navarro',
            'suffix' => null,
            'birth_date' => '2003-10-08',
            'gender' => 'Female',
            'email' => 'isabella.navarro@student.test',
            'phone' => '09251234567',
            'address' => 'Talisay City, Philippines',
            'year_level' => 4,
            'status' => 'active',
        ]);

        Student::create([
            'student_number' => '2024-00010',
            'course_id' => $bscs->id,
            'first_name' => 'Carlos',
            'middle_name' => 'Jimenez',
            'last_name' => 'Herrera',
            'suffix' => 'III',
            'birth_date' => '2003-06-25',
            'gender' => 'Male',
            'email' => 'carlos.herrera@student.test',
            'phone' => '09261234567',
            'address' => 'Cebu City, Philippines',
            'year_level' => 4,
            'status' => 'active',
        ]);

        $this->command->info('Sample students created successfully!');
        $this->command->info('- 5 BSIT students (3rd year)');
        $this->command->info('- 5 BSCS students (4th year)');
    }
}
