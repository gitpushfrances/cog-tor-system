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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number', 20)->unique()->comment('e.g., 2024-00001');
            $table->foreignId('course_id')->constrained('courses')->onDelete('restrict');
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('suffix', 10)->nullable()->comment('Jr., Sr., III');
            $table->date('birth_date');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->integer('year_level')->comment('1, 2, 3, 4');
            $table->enum('status', ['active', 'inactive', 'graduated', 'dropped'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'year_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
