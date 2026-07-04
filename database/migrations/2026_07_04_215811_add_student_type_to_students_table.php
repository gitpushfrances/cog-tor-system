<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'student_type')) {
                $table->enum('student_type', ['Regular', 'Irregular', 'Transferee'])
                      ->default('Regular')
                      ->after('year_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'student_type')) {
                $table->dropColumn('student_type');
            }
        });
    }
};
