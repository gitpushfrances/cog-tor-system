<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * On MariaDB/MySQL with explicit_defaults_for_timestamp=OFF (the XAMPP MariaDB 10.4 default), the first
 * NOT NULL TIMESTAMP column of a table silently gets "ON UPDATE CURRENT_TIMESTAMP". For cog_records and
 * tor_records that column is generated_at, so superseding a document rewrote its issue time. That broke the
 * TOR verify code of every replaced TOR (the code is bound to generated_at) and the Documents date filter.
 *
 * Fix: give generated_at an explicit default (which removes the ON UPDATE), then restore the issue time of
 * rows already rewritten from created_at (set in the same request when the document was generated).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['cog_records', 'tor_records'] as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `generated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

            DB::table($table)
                ->whereNotNull('created_at')
                ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, created_at, generated_at)) > 1')
                ->update(['generated_at' => DB::raw('created_at')]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: putting the auto-update back would only bring the bug back.
    }
};
