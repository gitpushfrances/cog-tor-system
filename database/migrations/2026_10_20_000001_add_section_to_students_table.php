<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Section of the student's current year level (e.g. "A"), used to group the in-system masterlist. Optional. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('section', 20)->nullable()->after('year_level');
            $table->index(['course_id', 'year_level', 'section'], 'students_course_year_section_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_course_year_section_index');
            $table->dropColumn('section');
        });
    }
};
