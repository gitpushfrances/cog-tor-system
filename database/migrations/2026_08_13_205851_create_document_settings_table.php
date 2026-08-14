<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_settings', function (Blueprint $table) {
            $table->id();
            $table->string('registrar_name');
            $table->string('registrar_credentials')->nullable();
            $table->string('registrar_title');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_settings');
    }
};
