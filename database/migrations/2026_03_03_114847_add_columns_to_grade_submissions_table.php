<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Columns faculty_remarks and resubmission_count are now defined
        // in the base create_grade_submissions_table migration.
        // This migration is intentionally left empty after schema consolidation.
    }

    public function down(): void
    {
        // Nothing to reverse.
    }
};
