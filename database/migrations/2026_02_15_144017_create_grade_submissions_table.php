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
        Schema::create('grade_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->comment('Faculty');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->comment('Dean');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->comment('Registrar');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->enum('dean_action', ['approved_by_head_of_department', 'rejected'])->nullable();
            $table->text('dean_remarks')->nullable();
            $table->text('faculty_remarks')->nullable();
            $table->integer('resubmission_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_submissions');
    }
};
