<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */    public function up(): void
    {
        // Create default roles for the tenant
        $roles = [
            'super_admin' => 'School Super Administrator with full access',
            'dept_admin' => 'Department Administrator with department-level access',
            'module_admin' => 'Module Administrator with module-specific access',
            'admin' => 'School Administrator with general administrative access',
            'teacher' => 'Teacher with academic management capabilities',
            'parent' => 'Parent with child monitoring capabilities',
            'student' => 'Student with limited access to academic content',
            'accountant' => 'Accountant with financial management access',
            'librarian' => 'Librarian with library management access',
            'staff' => 'General staff with basic access',
        ];

        // Create default permissions for the tenant
        $permissions = [
            // User management
            'manage_users' => 'Create, view, edit, and delete users',
            'view_users' => 'View users',
            
            // Role management
            'manage_roles' => 'Create, view, edit, and delete roles',
            'view_roles' => 'View roles',
            
            // Academic
            'manage_classes' => 'Create, view, edit, and delete classes',
            'view_classes' => 'View classes',
            'manage_subjects' => 'Create, view, edit, and delete subjects',
            'view_subjects' => 'View subjects',
            'manage_exams' => 'Create, view, edit, and delete exams',
            'view_exams' => 'View exams',
            
            // Finance
            'manage_finances' => 'Create, view, edit, and delete financial records',
            'view_finances' => 'View financial records',
            
            // Library
            'manage_library' => 'Create, view, edit, and delete library resources',
            'view_library' => 'View library resources',
            
            // Settings
            'manage_settings' => 'Manage school settings',
            'view_settings' => 'View school settings',
        ];

        // Create roles
        foreach ($roles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'description' => $description,
                'guard_name' => 'web'
            ]);
        }

        // Create permissions
        foreach ($permissions as $permissionName => $description) {
            Permission::create([
                'name' => $permissionName,
                'description' => $description,
                'guard_name' => 'web'
            ]);
        }

        // Assign all permissions to admin role
        $adminRole = Role::findByName('admin', 'web');
        $adminRole->givePermissionTo(Permission::all());

        // Assign teacher permissions
        $teacherRole = Role::findByName('teacher', 'web');
        $teacherRole->givePermissionTo([
            'view_users',
            'view_roles',
            'manage_classes',
            'view_classes',
            'manage_subjects',
            'view_subjects',
            'manage_exams',
            'view_exams',
            'view_library',
        ]);

        // Assign accountant permissions
        $accountantRole = Role::findByName('accountant', 'web');
        $accountantRole->givePermissionTo([
            'view_users',
            'manage_finances',
            'view_finances',
        ]);

        // Assign librarian permissions
        $librarianRole = Role::findByName('librarian', 'web');
        $librarianRole->givePermissionTo([
            'view_users',
            'manage_library',
            'view_library',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all roles and permissions
        $roles = ['admin', 'teacher', 'parent', 'student', 'accountant', 'librarian', 'staff'];
        foreach ($roles as $roleName) {
            $role = Role::findByName($roleName, 'web');
            if ($role) {
                $role->delete();
            }
        }

        // Get all permissions and delete them
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $permission->delete();
        }
    }
};
