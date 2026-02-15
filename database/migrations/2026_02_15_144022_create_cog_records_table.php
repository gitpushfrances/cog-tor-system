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
        Schema::create('cog_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('restrict');
            $table->foreignId('generated_by')->constrained('users')->comment('Registrar');
            $table->string('document_number', 50)->unique();
            $table->decimal('semester_gwa', 4, 2)->nullable()->comment('General Weighted Average');
            $table->json('grade_data')->comment('Snapshot of grades at generation time');
            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cog_records');
    }
};
