<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Course;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $bsit = Course::where('code', 'BSIT')->first();
        $bscs = Course::where('code', 'BSCS')->first();

        // BSIT Subjects
        Subject::updateOrCreate(
            ['code' => 'IT 101'],
            [
                'course_id' => $bsit->id,
                'name' => 'Introduction to Computing',
                'description' => 'Fundamentals of computing and information technology',
                'units' => 3,
                'year_level' => 1,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'IT 102'],
            [
                'course_id' => $bsit->id,
                'name' => 'Computer Programming 1',
                'description' => 'Introduction to programming using Python',
                'units' => 3,
                'year_level' => 1,
                'semester' => '2nd Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'IT 201'],
            [
                'course_id' => $bsit->id,
                'name' => 'Data Structures and Algorithms',
                'description' => 'Fundamental data structures and algorithm analysis',
                'units' => 3,
                'year_level' => 2,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'IT 202'],
            [
                'course_id' => $bsit->id,
                'name' => 'Database Management Systems',
                'description' => 'Relational database design and SQL',
                'units' => 3,
                'year_level' => 2,
                'semester' => '2nd Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'IT 301'],
            [
                'course_id' => $bsit->id,
                'name' => 'Web Development',
                'description' => 'Modern web development technologies',
                'units' => 3,
                'year_level' => 3,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        // BSCS Subjects
        Subject::updateOrCreate(
            ['code' => 'CS 101'],
            [
                'course_id' => $bscs->id,
                'name' => 'Introduction to Computer Science',
                'description' => 'Foundations of computer science',
                'units' => 3,
                'year_level' => 1,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'CS 102'],
            [
                'course_id' => $bscs->id,
                'name' => 'Discrete Mathematics',
                'description' => 'Mathematical structures for computer science',
                'units' => 3,
                'year_level' => 1,
                'semester' => '2nd Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'CS 201'],
            [
                'course_id' => $bscs->id,
                'name' => 'Object-Oriented Programming',
                'description' => 'OOP principles using Java',
                'units' => 3,
                'year_level' => 2,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'CS 301'],
            [
                'course_id' => $bscs->id,
                'name' => 'Software Engineering',
                'description' => 'Software development methodologies',
                'units' => 3,
                'year_level' => 3,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        Subject::updateOrCreate(
            ['code' => 'CS 401'],
            [
                'course_id' => $bscs->id,
                'name' => 'Artificial Intelligence',
                'description' => 'Introduction to AI and machine learning',
                'units' => 3,
                'year_level' => 4,
                'semester' => '1st Semester',
                'status' => 'active',
            ]
        );

        $this->command->info('Sample subjects created/updated successfully!');
        $this->command->info('- 5 BSIT subjects');
        $this->command->info('- 5 BSCS subjects');
    }
}
