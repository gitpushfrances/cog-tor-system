<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename the role in the roles table
        DB::table('roles')
            ->where('name', 'dean')
            ->update(['name' => 'head_of_department']);

        // 2. Expand ENUM to allow both values during transition
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','faculty','dean','head_of_department','registrar') DEFAULT 'faculty'");

        // 3. Update existing users
        DB::table('users')
            ->where('role', 'dean')
            ->update(['role' => 'head_of_department']);

        // 4. Lock ENUM to new values only
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','faculty','head_of_department','registrar') DEFAULT 'faculty'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','faculty','dean','head_of_department','registrar') DEFAULT 'faculty'");

        DB::table('users')
            ->where('role', 'head_of_department')
            ->update(['role' => 'dean']);

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','faculty','dean','registrar') DEFAULT 'faculty'");

        DB::table('roles')
            ->where('name', 'head_of_department')
            ->update(['name' => 'dean']);
    }
};
