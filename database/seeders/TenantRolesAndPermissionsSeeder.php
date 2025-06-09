<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed roles and permissions for tenant.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'manage_users', 'create_users', 'edit_users', 'delete_users', 'view_users',
            
            // Academic Management
            'manage_students', 'create_students', 'edit_students', 'delete_students', 'view_students',
            'manage_classes', 'create_classes', 'edit_classes', 'delete_classes', 'view_classes',
            'manage_subjects', 'create_subjects', 'edit_subjects', 'delete_subjects', 'view_subjects',
            'manage_teachers', 'create_teachers', 'edit_teachers', 'delete_teachers', 'view_teachers',
            
            // Examination Management
            'manage_exams', 'create_exams', 'edit_exams', 'delete_exams', 'view_exams',
            'manage_grades', 'create_grades', 'edit_grades', 'view_grades',
            
            // Financial Management
            'manage_fees', 'create_fees', 'edit_fees', 'delete_fees', 'view_fees',
            'manage_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses', 'view_expenses',
            
            // Library Management
            'manage_books', 'create_books', 'edit_books', 'delete_books', 'view_books',
            'manage_book_issues', 'issue_books', 'return_books', 'view_book_issues',
            
            // Attendance Management
            'manage_attendance', 'mark_attendance', 'view_attendance', 'edit_attendance',
            
            // Notice Management
            'manage_notices', 'create_notices', 'edit_notices', 'delete_notices', 'view_notices', 'publish_notices',
            
            // Reports and Analytics
            'view_reports', 'export_reports', 'view_analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // Admin gets all permissions

        $teacherRole = Role::create(['name' => 'teacher']);
        $teacherRole->givePermissionTo([
            'view_students', 'edit_students',
            'view_classes', 'view_subjects',
            'manage_exams', 'create_exams', 'edit_exams', 'view_exams',
            'manage_grades', 'create_grades', 'edit_grades', 'view_grades',
            'manage_attendance', 'mark_attendance', 'view_attendance',
            'view_notices',
            'view_reports'
        ]);

        $accountantRole = Role::create(['name' => 'accountant']);
        $accountantRole->givePermissionTo([
            'view_students',
            'manage_fees', 'create_fees', 'edit_fees', 'view_fees',
            'manage_expenses', 'create_expenses', 'edit_expenses', 'view_expenses',
            'view_reports', 'export_reports'
        ]);

        $librarianRole = Role::create(['name' => 'librarian']);
        $librarianRole->givePermissionTo([
            'view_students',
            'manage_books', 'create_books', 'edit_books', 'view_books',
            'manage_book_issues', 'issue_books', 'return_books', 'view_book_issues',
            'view_reports'
        ]);

        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view_classes', 'view_subjects',
            'view_exams', 'view_grades',
            'view_attendance',
            'view_notices',
            'view_books'
        ]);

        $parentRole = Role::create(['name' => 'parent']);
        $parentRole->givePermissionTo([
            'view_students', // Only their children
            'view_classes', 'view_subjects',
            'view_exams', 'view_grades',
            'view_attendance',
            'view_notices',
            'view_fees'
        ]);
    }
}
