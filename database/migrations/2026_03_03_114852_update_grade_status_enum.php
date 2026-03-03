<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- GRADES TABLE ---
        // Step 1: Expand ENUM to include all old + new values
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('pending','saved','pending_dean_review','approved_by_dean','rejected','finalized') NOT NULL DEFAULT 'pending'");

        // Step 2: Migrate old 'pending' to 'pending_dean_review'
        DB::table('grades')->where('status', 'pending')->update(['status' => 'pending_dean_review']);

        // Step 3: Lock to new values only
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('saved','pending_dean_review','approved_by_dean','rejected','finalized') NOT NULL DEFAULT 'saved'");

        // --- GRADE_SUBMISSIONS TABLE ---
        // Step 4: Expand dean_action ENUM to include old 'approved' + new 'approved_by_dean'
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN dean_action ENUM('approved','approved_by_dean','rejected') NULL");

        // Step 5: Migrate old 'approved' to 'approved_by_dean'
        DB::table('grade_submissions')->where('dean_action', 'approved')->update(['dean_action' => 'approved_by_dean']);

        // Step 6: Lock to new values only
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN dean_action ENUM('approved_by_dean','rejected') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('pending','pending_dean_review','approved_by_dean','rejected','finalized') NOT NULL DEFAULT 'pending'");
        DB::table('grades')->where('status', 'pending_dean_review')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE grades MODIFY COLUMN status ENUM('pending','approved_by_dean','finalized') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE grade_submissions MODIFY COLUMN dean_action ENUM('approved','rejected') NULL");
        DB::table('grade_submissions')->where('dean_action', 'approved_by_dean')->update(['dean_action' => 'approved']);
    }
};
