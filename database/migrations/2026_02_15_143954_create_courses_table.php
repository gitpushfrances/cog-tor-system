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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('code', 20)->comment('e.g., BSIT, BSCS');
            $table->string('name', 100)->comment('e.g., Bachelor of Science in Information Technology');
            $table->integer('years')->default(4)->comment('Program duration in years');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['department_id', 'code']);
        });

        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('code', 20)->comment('e.g., ENGLISH, MATH, AUTOMOTIVE');
            $table->string('name', 100)->comment('e.g., Major in English');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
        Schema::dropIfExists('courses');
    }
};
