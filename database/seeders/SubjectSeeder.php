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
        Subject::create([
            'course_id' => $bsit->id,
            'code' => 'IT 101',
            'name' => 'Introduction to Computing',
            'description' => 'Fundamentals of computing and information technology',
            'units' => 3,
            'year_level' => 1,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bsit->id,
            'code' => 'IT 102',
            'name' => 'Computer Programming 1',
            'description' => 'Introduction to programming using Python',
            'units' => 3,
            'year_level' => 1,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bsit->id,
            'code' => 'IT 201',
            'name' => 'Data Structures and Algorithms',
            'description' => 'Fundamental data structures and algorithm analysis',
            'units' => 3,
            'year_level' => 2,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bsit->id,
            'code' => 'IT 202',
            'name' => 'Database Management Systems',
            'description' => 'Relational database design and SQL',
            'units' => 3,
            'year_level' => 2,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bsit->id,
            'code' => 'IT 301',
            'name' => 'Web Development',
            'description' => 'Modern web development technologies',
            'units' => 3,
            'year_level' => 3,
            'status' => 'active',
        ]);

        // BSCS Subjects
        Subject::create([
            'course_id' => $bscs->id,
            'code' => 'CS 101',
            'name' => 'Introduction to Computer Science',
            'description' => 'Foundations of computer science',
            'units' => 3,
            'year_level' => 1,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bscs->id,
            'code' => 'CS 102',
            'name' => 'Discrete Mathematics',
            'description' => 'Mathematical structures for computer science',
            'units' => 3,
            'year_level' => 1,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bscs->id,
            'code' => 'CS 201',
            'name' => 'Object-Oriented Programming',
            'description' => 'OOP principles using Java',
            'units' => 3,
            'year_level' => 2,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bscs->id,
            'code' => 'CS 301',
            'name' => 'Software Engineering',
            'description' => 'Software development methodologies',
            'units' => 3,
            'year_level' => 3,
            'status' => 'active',
        ]);

        Subject::create([
            'course_id' => $bscs->id,
            'code' => 'CS 401',
            'name' => 'Artificial Intelligence',
            'description' => 'Introduction to AI and machine learning',
            'units' => 3,
            'year_level' => 4,
            'status' => 'active',
        ]);

        $this->command->info('Sample subjects created successfully!');
        $this->command->info('- 5 BSIT subjects');
        $this->command->info('- 5 BSCS subjects');
    }
}
