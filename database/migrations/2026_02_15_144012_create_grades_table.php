<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('faculty_id')->constrained('users')->comment('Faculty who encoded the grade');
            $table->decimal('grade', 4, 2)->comment('Final grade — flexible, e.g. 1.00, 1.52, 2.75');
            $table->enum('status', ['saved', 'pending_head_of_department_review', 'approved_by_head_of_department', 'rejected', 'finalized'])->default('saved');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
