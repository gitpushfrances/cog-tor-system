<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tor_records', function (Blueprint $table) {
            $table->text('remarks')->nullable();

            $table->string('prepared_by_name')->nullable();
            $table->string('prepared_by_title')->nullable();

            $table->string('checked_by_name')->nullable();
            $table->string('checked_by_credentials')->nullable();
            $table->string('checked_by_title')->nullable();

            $table->string('campus_admin_name')->nullable();
            $table->string('campus_admin_title')->nullable();

            $table->string('degree_awarded')->nullable();
            $table->string('major_at_graduation')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('board_resolution_no')->nullable();
            $table->date('board_regents_approval_date')->nullable();
            $table->string('nstp_serial_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tor_records', function (Blueprint $table) {
            $table->dropColumn([
                'remarks',
                'prepared_by_name', 'prepared_by_title',
                'checked_by_name', 'checked_by_credentials', 'checked_by_title',
                'campus_admin_name', 'campus_admin_title',
                'degree_awarded', 'major_at_graduation', 'graduation_date',
                'board_resolution_no', 'board_regents_approval_date', 'nstp_serial_number',
            ]);
        });
    }
};
