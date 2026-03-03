<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_submissions', function (Blueprint $table) {
            $table->text('faculty_remarks')->nullable()->after('dean_remarks');
            $table->integer('resubmission_count')->default(0)->after('faculty_remarks');
        });

        // Fix existing dean_action values from 'approved' to 'approved_by_dean'
        DB::table('grade_submissions')
            ->where('dean_action', 'approved')
            ->update(['dean_action' => 'approved_by_dean']);
    }

    public function down(): void
    {
        Schema::table('grade_submissions', function (Blueprint $table) {
            $table->dropColumn(['faculty_remarks', 'resubmission_count']);
        });
    }
};
