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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('faculty_id')->constrained('users')->comment('Faculty who encoded the grade');
            $table->decimal('grade', 4, 2)->comment('Final grade (e.g., 1.00, 1.25, 5.00)');
            $table->decimal('percentage', 5, 2)->nullable()->comment('Percentage score before conversion');
            $table->enum('status', ['pending', 'approved_by_dean', 'finalized'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['enrollment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
