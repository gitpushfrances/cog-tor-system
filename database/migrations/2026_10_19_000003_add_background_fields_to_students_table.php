<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FIELDS = [
        'place_of_birth',
        'elementary_school',
        'elementary_graduation_year',
        'high_school',
        'high_school_graduation_year',
    ];

    public function up(): void
    {
        // Place of birth and preliminary education belong to the student, not to each TOR.
        Schema::table('students', function (Blueprint $table) {
            $table->string('place_of_birth')->nullable()->after('address');
            $table->string('elementary_school')->nullable()->after('place_of_birth');
            $table->string('elementary_graduation_year', 20)->nullable()->after('elementary_school');
            $table->string('high_school')->nullable()->after('elementary_graduation_year');
            $table->string('high_school_graduation_year', 20)->nullable()->after('high_school');
        });

        // Backfill from each student's most recent TOR, where these values were typed before.
        $latestTorIds = DB::table('tor_records')->selectRaw('MAX(id)')->groupBy('student_id');

        DB::table('tor_records')->whereIn('id', $latestTorIds)->orderBy('id')->each(function ($tor) {
            $values = [];
            foreach (self::FIELDS as $field) {
                if (filled($tor->{$field})) {
                    $values[$field] = $tor->{$field};
                }
            }

            if ($values) {
                DB::table('students')->where('id', $tor->student_id)->update($values);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(self::FIELDS);
        });
    }
};
