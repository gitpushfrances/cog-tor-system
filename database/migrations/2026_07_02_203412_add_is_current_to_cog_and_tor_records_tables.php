<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cog_records', function (Blueprint $table) {
            $table->boolean('is_current')->default(true)->after('generated_at');
        });

        Schema::table('tor_records', function (Blueprint $table) {
            $table->boolean('is_current')->default(true)->after('generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('cog_records', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });

        Schema::table('tor_records', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }
};
