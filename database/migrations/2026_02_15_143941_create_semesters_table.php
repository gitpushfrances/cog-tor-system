<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->string('semester_name', 50)->comment('e.g., 1st Semester, 2nd Semester, Summer');
            $table->integer('semester_order')->comment('1, 2, 3');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'upcoming'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_year_id', 'semester_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
