<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Scanned records (TOR/COG) from a student's previous school, placed on the
        // ESSU TOR by the year level and semester they cover.
        Schema::create('previous_school_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('school_name');
            $table->unsignedTinyInteger('from_year_level');
            $table->unsignedTinyInteger('from_semester')->comment('1 = 1st, 2 = 2nd, 3 = Summer');
            $table->unsignedTinyInteger('to_year_level');
            $table->unsignedTinyInteger('to_semester')->comment('1 = 1st, 2 = 2nd, 3 = Summer');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('previous_school_record_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('previous_school_record_id')->constrained('previous_school_records')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('previous_school_record_pages');
        Schema::dropIfExists('previous_school_records');
    }
};
