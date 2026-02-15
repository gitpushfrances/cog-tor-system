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
        Schema::create('tor_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('generated_by')->constrained('users')->comment('Registrar');
            $table->string('document_number', 50)->unique();
            $table->decimal('cumulative_gwa', 4, 2)->nullable()->comment('Overall GWA across all semesters');
            $table->json('all_grades_data')->comment('Complete academic record snapshot');
            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at');
            $table->enum('tor_type', ['partial', 'complete'])->default('complete');
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tor_records');
    }
};
