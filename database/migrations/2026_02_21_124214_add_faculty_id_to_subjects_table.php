<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('course_id')
                  ->constrained('users')->onDelete('set null')
                  ->comment('Faculty assigned to teach this subject');
            $table->string('semester')->nullable()->after('year_level')
                  ->comment('1st, 2nd, Summer');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropColumn(['faculty_id', 'semester']);
        });
    }
};
