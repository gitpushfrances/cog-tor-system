<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subjects')->where('id', 1)->update(['faculty_id' => 3]);

        $semester = DB::table('semesters')->first();
        $students = DB::table('students')->pluck('id');

        foreach ($students as $studentId) {
            DB::table('enrollments')->insertOrIgnore([
                'student_id'      => $studentId,
                'subject_id'      => 1,
                'semester_id'     => $semester->id,
                'enrolled_by'     => 1,
                'enrollment_date' => now()->toDateString(),
                'status'          => 'enrolled',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        echo "Done! Subject 1 assigned to faculty ID 3, 10 students enrolled.\n";
    }
}
