<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
            'import students',
            'export students',
            'enroll students',
            'view students',

            // Grade Management
            'encode grades',
            'submit grades',
            'resubmit grades',
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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin Role
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage users',
            'approve users',
            'manage departments',
            'manage courses',
            'manage subjects',
            'manage school years',
            'manage semesters',
            'view students',
            'view grades',
            'view reports',
            'export data',
        ]);

        // Faculty Role
        $faculty = Role::firstOrCreate(['name' => 'faculty']);
        $faculty->givePermissionTo([
            'view students',
            'encode grades',
            'submit grades',
            'resubmit grades',
            'view grades',
        ]);

        // Head of Department Role
        $hod = Role::firstOrCreate(['name' => 'head_of_department']);
        $hod->givePermissionTo([
            'manage students',
            'import students',
            'export students',
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
        $registrar = Role::firstOrCreate(['name' => 'registrar']);
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

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
