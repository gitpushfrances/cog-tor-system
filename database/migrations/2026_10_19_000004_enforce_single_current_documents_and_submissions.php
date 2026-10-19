<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database-level guarantees (they hold even under double clicks / two tabs):
 *  - at most one current COG per student + semester, and one current TOR per student;
 *  - at most one grade submission per grade.
 * Existing duplicates are cleaned first: the newest row (highest id) is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Duplicate "current" COGs: keep the newest per student + semester current.
        $keep = DB::table('cog_records')->where('is_current', true)
            ->selectRaw('MAX(id) AS id')->groupBy('student_id', 'semester_id')->pluck('id');
        DB::table('cog_records')->where('is_current', true)->whereNotIn('id', $keep)->update(['is_current' => false]);

        // Duplicate "current" TORs: keep the newest per student current.
        $keep = DB::table('tor_records')->where('is_current', true)
            ->selectRaw('MAX(id) AS id')->groupBy('student_id')->pluck('id');
        DB::table('tor_records')->where('is_current', true)->whereNotIn('id', $keep)->update(['is_current' => false]);

        // Duplicate submissions of one grade: keep the newest.
        $keep = DB::table('grade_submissions')->selectRaw('MAX(id) AS id')->groupBy('grade_id')->pluck('id');
        DB::table('grade_submissions')->whereNotIn('id', $keep)->delete();

        // NULL for non-current rows, so only current rows compete for the unique key.
        Schema::table('cog_records', function (Blueprint $table) {
            $table->string('current_key', 41)->nullable()
                ->storedAs("IF(is_current, CONCAT(student_id, '-', semester_id), NULL)");
            $table->unique('current_key', 'cog_records_one_current');
        });

        Schema::table('tor_records', function (Blueprint $table) {
            $table->unsignedBigInteger('current_key')->nullable()
                ->storedAs('IF(is_current, student_id, NULL)');
            $table->unique('current_key', 'tor_records_one_current');
        });

        Schema::table('grade_submissions', function (Blueprint $table) {
            $table->unique('grade_id', 'grade_submissions_one_per_grade');
        });
        // The unique key now serves the grade_id foreign key; a plain index left by an earlier rollback is redundant.
        if ($this->hasIndex('grade_submissions', 'grade_submissions_grade_id_index')) {
            Schema::table('grade_submissions', fn (Blueprint $table) => $table->dropIndex('grade_submissions_grade_id_index'));
        }
    }

    public function down(): void
    {
        Schema::table('grade_submissions', function (Blueprint $table) {
            // The foreign key needs an index on grade_id: add a plain one before dropping the unique one.
            if (! $this->hasIndex('grade_submissions', 'grade_submissions_grade_id_index')) {
                $table->index('grade_id', 'grade_submissions_grade_id_index');
            }
            $table->dropUnique('grade_submissions_one_per_grade');
        });

        Schema::table('tor_records', function (Blueprint $table) {
            $table->dropUnique('tor_records_one_current');
            $table->dropColumn('current_key');
        });

        Schema::table('cog_records', function (Blueprint $table) {
            $table->dropUnique('cog_records_one_current');
            $table->dropColumn('current_key');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))->contains(fn ($row) => $row->Key_name === $index);
    }
};
