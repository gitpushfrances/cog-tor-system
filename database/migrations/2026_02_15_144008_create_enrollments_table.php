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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('restrict');
            $table->foreignId('enrolled_by')->constrained('users')->comment('Dean who enrolled the student');
            $table->date('enrollment_date');
            $table->enum('status', ['enrolled', 'dropped', 'completed'])->default('enrolled');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'subject_id', 'semester_id']);
            $table->index(['semester_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
