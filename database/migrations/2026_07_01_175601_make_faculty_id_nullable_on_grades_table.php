<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Registrar now encodes grades directly, without going through Faculty.
     * faculty_id must accept NULL for grades entered directly by the Registrar.
     * Column type, unsigned flag, and foreign key stay exactly as they were —
     * only nullability changes.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE grades MODIFY faculty_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * Note: if any grade rows have faculty_id = NULL when this rolls back,
     * this statement will fail — those rows would need a valid faculty_id
     * assigned first.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE grades MODIFY faculty_id BIGINT UNSIGNED NOT NULL');
    }
};
