<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- GRADES TABLE ---
        // Step 1: Expand ENUM to cover all old + new values safely
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('pending','saved','pending_dean_review','approved_by_dean','pending_head_of_department_review','approved_by_head_of_department','rejected','finalized') NOT NULL DEFAULT 'saved'");
        // Step 2: Migrate any legacy values to v2
        DB::table('grades')->where('status', 'pending')->update(['status' => 'pending_head_of_department_review']);
        DB::table('grades')->where('status', 'pending_dean_review')->update(['status' => 'pending_head_of_department_review']);
        DB::table('grades')->where('status', 'approved_by_dean')->update(['status' => 'approved_by_head_of_department']);
        // Step 3: Lock to v2 values only
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('saved','pending_head_of_department_review','approved_by_head_of_department','rejected','finalized') NOT NULL DEFAULT 'saved'");

        // --- GRADE_SUBMISSIONS TABLE ---
        // Step 4: Expand dean_action ENUM to cover old + new values safely
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN dean_action ENUM('approved','approved_by_dean','approved_by_head_of_department','rejected') NULL");
        // Step 5: Migrate legacy values
        DB::table('grade_submissions')->where('dean_action', 'approved')->update(['dean_action' => 'approved_by_head_of_department']);
        DB::table('grade_submissions')->where('dean_action', 'approved_by_dean')->update(['dean_action' => 'approved_by_head_of_department']);
        // Step 6: Lock to v2 values only
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN dean_action ENUM('approved_by_head_of_department','rejected') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('saved','pending_dean_review','approved_by_dean','rejected','finalized') NOT NULL DEFAULT 'saved'");
        DB::table('grades')->where('status', 'pending_head_of_department_review')->update(['status' => 'pending_dean_review']);
        DB::table('grades')->where('status', 'approved_by_head_of_department')->update(['status' => 'approved_by_dean']);
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN dean_action ENUM('approved_by_dean','rejected') NULL");
        DB::table('grade_submissions')->where('dean_action', 'approved_by_head_of_department')->update(['dean_action' => 'approved_by_dean']);
    }
};
