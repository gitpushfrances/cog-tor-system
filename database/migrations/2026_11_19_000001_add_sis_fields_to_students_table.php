<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Student Information Sheet (ESSU-ACAD-203.b) fields: column => max length.
     * All nullable at the database level; "required" is enforced by the Add/Edit form rules.
     * Elementary and Senior High names/years stay in the existing elementary_* / high_school_*
     * columns, which the TOR reads, so only their address, period and honors are added here.
     */
    private const COLUMNS = [
        // Personal
        'civil_status'          => 30,
        'home_phone'            => 30,
        // Permanent address (the existing `address` column keeps the composed single line)
        'address_street'        => 150,
        'address_barangay'      => 100,
        'address_municipality'  => 100,
        'address_province'      => 100,
        'address_zip'           => 10,
        // Parents
        'father_name'           => 150,
        'father_contact'        => 30,
        'mother_name'           => 150,
        'mother_contact'        => 30,
        // Guardian
        'guardian_name'         => 150,
        'guardian_address'      => 255,
        'guardian_contact'      => 30,
        // Person to notify in case of emergency
        'emergency_name'        => 150,
        'emergency_relation'    => 50,
        'emergency_address'     => 255,
        'emergency_contact'     => 30,
        // Past schools attended: undergraduate
        'elementary_address'    => 255,
        'elementary_period'     => 50,
        'elementary_honors'     => 150,
        'junior_high_school'    => 255,
        'junior_high_address'   => 255,
        'junior_high_period'    => 50,
        'junior_high_honors'    => 150,
        'high_school_address'   => 255,
        'high_school_period'    => 50,
        'high_school_honors'    => 150,
        // Past schools attended: graduate
        'college_school'        => 255,
        'college_address'       => 255,
        'college_period'        => 50,
        'college_honors'        => 150,
        'graduate_school'       => 255,
        'graduate_address'      => 255,
        'graduate_period'       => 50,
        'graduate_honors'       => 150,
        'post_graduate_school'  => 255,
        'post_graduate_address' => 255,
        'post_graduate_period'  => 50,
        'post_graduate_honors'  => 150,
    ];

    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            foreach (self::COLUMNS as $column => $length) {
                if (! Schema::hasColumn('students', $column)) {
                    $table->string($column, $length)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $existing = array_filter(array_keys(self::COLUMNS), fn ($c) => Schema::hasColumn('students', $c));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });

        // MariaDB keeps dropped columns as hidden leftovers, so re-running up() after a
        // rollback fails with "Row size too large" until the table is rebuilt.
        DB::statement('ALTER TABLE `students` FORCE');
    }
};
