<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tor_records', function (Blueprint $table) {
            $table->string('place_of_birth')->nullable();
            $table->string('elementary_school')->nullable();
            $table->string('elementary_graduation_year')->nullable();
            $table->string('high_school')->nullable();
            $table->string('high_school_graduation_year')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tor_records', function (Blueprint $table) {
            $table->dropColumn([
                'place_of_birth',
                'elementary_school',
                'elementary_graduation_year',
                'high_school',
                'high_school_graduation_year',
            ]);
        });
    }
};
