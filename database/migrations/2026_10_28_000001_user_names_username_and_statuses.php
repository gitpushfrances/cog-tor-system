<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Panel recommendations (final defense):
 *  - user name in Last / First / Middle name fields ("name" is kept, filled from them, for existing screens and logs);
 *  - sign in with a username instead of an email address (email becomes optional and is no longer used);
 *  - account status "pending" is replaced by "inactive".
 * Existing accounts get a username from their email ("registrar@cogtor.test" -> "registrar") and their
 * name split on the last space; Admin can correct both under Users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('id');
            $table->string('last_name', 100)->nullable()->after('name');
            $table->string('first_name', 100)->nullable()->after('last_name');
            $table->string('middle_name', 100)->nullable()->after('first_name');
        });

        $taken = [];
        foreach (DB::table('users')->orderBy('id')->get(['id', 'name', 'email']) as $user) {
            $base = strtolower(preg_replace('/[^A-Za-z0-9._-]/', '', strstr((string) $user->email, '@', true) ?: ''));
            $base = strlen($base) >= 3 ? substr($base, 0, 45) : 'user' . $user->id;
            $username = $base;
            for ($n = 2; in_array($username, $taken, true); $n++) {
                $username = $base . $n;
            }
            $taken[] = $username;

            $name  = trim(preg_replace('/\s+/', ' ', (string) $user->name));
            $space = strrpos($name, ' ');

            DB::table('users')->where('id', $user->id)->update([
                'username'   => $username,
                'first_name' => $space === false ? $name : substr($name, 0, $space),
                'last_name'  => $space === false ? $name : substr($name, $space + 1),
            ]);
        }

        DB::table('users')->where('status', 'pending')->update(['status' => 'inactive']);

        DB::statement('ALTER TABLE `users` MODIFY `username` VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE `users` ADD UNIQUE `users_username_unique` (`username`)');
        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL');
        DB::statement("ALTER TABLE `users` MODIFY `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `status` ENUM('active','inactive','pending') NOT NULL DEFAULT 'active'");
        DB::table('users')->whereNull('email')->update(['email' => DB::raw("CONCAT(`username`, '@local.invalid')")]);
        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn(['username', 'last_name', 'first_name', 'middle_name']);
        });
    }
};
