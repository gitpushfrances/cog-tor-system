<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'manage users',
            'approve users',

            // Academic Structure
            'manage departments',
            'manage courses',
            'manage subjects',
            'manage school years',
            'manage semesters',

            // Student Management
            'manage students',
            'enroll students',
            'view students',

            // Grade Management
            'encode grades',
            'submit grades',
            'review grades',
            'approve grades',
            'reject grades',
            'finalize grades',
            'view grades',

            // Document Generation
            'generate cog',
            'generate tor',
            'view documents',

            // Reports
            'view reports',
            'export data',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin Role
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage users',
            'approve users',
            'manage departments',
            'manage courses',
            'manage subjects',
            'manage school years',
            'manage semesters',
            'manage students',
            'view students',
            'view grades',
            'view reports',
            'export data',
        ]);

        // Faculty Role
        $faculty = Role::create(['name' => 'faculty']);
        $faculty->givePermissionTo([
            'view students',
            'encode grades',
            'submit grades',
            'view grades',
        ]);

        // Dean Role
        $dean = Role::create(['name' => 'dean']);
        $dean->givePermissionTo([
            'manage students',
            'enroll students',
            'view students',
            'review grades',
            'approve grades',
            'reject grades',
            'view grades',
            'view reports',
            'export data',
        ]);

        // Registrar Role
        $registrar = Role::create(['name' => 'registrar']);
        $registrar->givePermissionTo([
            'view students',
            'finalize grades',
            'view grades',
            'generate cog',
            'generate tor',
            'view documents',
            'view reports',
            'export data',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
