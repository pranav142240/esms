<?php

namespace App\Application\Services\Admin;

use App\Models\Admin;
use App\Models\AdminTenantConversion;
use App\Models\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;

class AdminTenantConversionService
{
    /**
     * Convert an admin to a tenant with school setup
     */
    public function convertAdminToTenant(Admin $admin, array $schoolData): array
    {
        // Validate conversion requirements
        if (!$this->validateConversionRequirements($admin)) {
            throw new Exception('Admin does not meet conversion requirements');
        }

        DB::beginTransaction();

        try {
            // Update admin status to setting_up
            $admin->update(['status' => 'setting_up']);

            // Create conversion tracking record
            $conversion = AdminTenantConversion::create([
                'admin_id' => $admin->id,
                'old_admin_data' => $admin->toArray(),
                'conversion_status' => 'initiated'
            ]);

            // Create tenant database
            $tenantData = $this->createTenantDatabase($schoolData);
            
            // Update conversion with tenant info
            $conversion->update([
                'tenant_id' => $tenantData['tenant_id'],
                'tenant_domain' => $tenantData['domain']
            ]);

            // Migrate admin data to tenant database
            $tenantUser = $this->migrateAdminDataToTenant($admin, $tenantData, $schoolData);

            // Mark conversion as completed
            $conversion->update([
                'conversion_status' => 'completed',
                'converted_at' => now()
            ]);            // Update admin status to converted
            $admin->update([
                'status' => 'converted',
                'tenant_id' => $tenantData['tenant_id'],
                'converted_at' => now()
            ]);

            // Send school creation success email
            $this->sendSchoolCreatedEmail($admin, $schoolData, $tenantData);

            DB::commit();

            return [
                'success' => true,
                'tenant' => $tenantData,
                'tenant_user' => $tenantUser,
                'conversion_id' => $conversion->id,
                'message' => 'Admin successfully converted to tenant'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            // Mark conversion as failed
            if (isset($conversion)) {
                $conversion->update([
                    'conversion_status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            // Reset admin status
            $admin->update(['status' => 'active']);

            Log::error('Admin to tenant conversion failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Create tenant database and setup
     */
    protected function createTenantDatabase(array $schoolData): array
    {
        // Generate unique domain
        $domain = $this->generateUniqueDomain($schoolData['school_name']);
        
        // Create tenant record
        $tenant = Tenant::create([
            'name' => $schoolData['school_name'],
            'email' => $schoolData['school_email'],
            'domain' => $domain,
            'database' => 'tenant_' . str_replace('-', '_', $domain),
            'status' => 'active',
            'subscription_plan' => $schoolData['subscription_plan'] ?? 'basic',
            'expires_at' => now()->addMonth(),
            'settings' => json_encode([
                'school_name' => $schoolData['school_name'],
                'school_email' => $schoolData['school_email'],
                'school_phone' => $schoolData['school_phone'] ?? null,
                'school_address' => $schoolData['school_address'] ?? null,
                'logo_path' => $schoolData['logo_path'] ?? null
            ])
        ]);

        // Run tenant database migrations
        $this->runTenantMigrations($tenant->database);

        return [
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'database' => $tenant->database,
            'tenant' => $tenant
        ];
    }

    /**
     * Migrate admin data to tenant database
     */
    protected function migrateAdminDataToTenant(Admin $admin, array $tenantData, array $schoolData): User
    {
        // Switch to tenant database connection
        $tenantDatabase = $tenantData['database'];
        
        // Create tenant user (converted admin)
        $tenantUser = new User();
        $tenantUser->setConnection('tenant');
        
        // Configure tenant database connection
        config(['database.connections.tenant.database' => $tenantDatabase]);
        DB::purge('tenant');

        $userData = [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => $admin->password, // Keep existing password hash
            'phone' => $admin->phone,
            'role' => 'admin',
            'is_school_owner' => true,
            'original_admin_id' => $admin->id,
            'status' => 'active',
            'email_verified_at' => now()
        ];

        $tenantUser = User::on('tenant')->create($userData);

        // Assign school owner role and permissions
        $this->assignSchoolOwnerRole($tenantUser);

        return $tenantUser;
    }

    /**
     * Run tenant database migrations
     */
    protected function runTenantMigrations(string $database): void
    {
        // Create database if it doesn't exist
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}`");

        // Set tenant database connection
        config(['database.connections.tenant.database' => $database]);
        DB::purge('tenant');

        // Run migrations on tenant database
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true
        ]);

        // Seed basic roles and permissions
        $this->seedTenantRolesAndPermissions($database);
    }

    /**
     * Seed tenant database with basic roles and permissions
     */
    protected function seedTenantRolesAndPermissions(string $database): void
    {
        config(['database.connections.tenant.database' => $database]);
        DB::purge('tenant');

        // Create basic roles
        $roles = [
            'admin' => 'School Administrator',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian'
        ];

        foreach ($roles as $name => $description) {
            DB::connection('tenant')->table('roles')->insert([
                'name' => $name,
                'guard_name' => 'tenant',
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Create basic permissions
        $permissions = [
            'manage_users',
            'manage_students',
            'manage_teachers',
            'manage_classes',
            'manage_subjects',
            'manage_exams',
            'manage_attendance',
            'manage_fees',
            'manage_library',
            'view_reports',
            'manage_settings'
        ];

        foreach ($permissions as $permission) {
            DB::connection('tenant')->table('permissions')->insert([
                'name' => $permission,
                'guard_name' => 'tenant',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Assign school owner role and permissions
     */
    protected function assignSchoolOwnerRole(User $user): void
    {
        // Get admin role
        $adminRole = DB::connection('tenant')
            ->table('roles')
            ->where('name', 'admin')
            ->first();

        if ($adminRole) {
            // Assign role to user
            DB::connection('tenant')->table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => User::class,
                'model_id' => $user->id
            ]);

            // Get all permissions and assign to admin role
            $permissions = DB::connection('tenant')->table('permissions')->get();
            
            foreach ($permissions as $permission) {
                DB::connection('tenant')->table('role_has_permissions')->insert([
                    'permission_id' => $permission->id,
                    'role_id' => $adminRole->id
                ]);
            }
        }
    }

    /**
     * Generate unique domain for tenant
     */
    protected function generateUniqueDomain(string $schoolName): string
    {
        $baseDomain = Str::slug($schoolName);
        $domain = $baseDomain;
        $counter = 1;

        while (Tenant::where('domain', $domain)->exists()) {
            $domain = $baseDomain . '-' . $counter;
            $counter++;
        }

        return $domain;
    }

    /**
     * Validate conversion requirements
     */
    public function validateConversionRequirements(Admin $admin): bool
    {
        // Check if admin is in correct status
        if (!in_array($admin->status, ['active', 'pending'])) {
            return false;
        }

        // Check if admin is not already converted
        if ($admin->status === 'converted') {
            return false;
        }

        // Check if admin has required information
        if (empty($admin->email) || empty($admin->name)) {
            return false;
        }

        return true;
    }

    /**
     * Rollback conversion
     */
    public function rollbackConversion(int $conversionId): bool
    {
        DB::beginTransaction();

        try {
            $conversion = AdminTenantConversion::findOrFail($conversionId);
            
            // Get admin
            $admin = Admin::findOrFail($conversion->admin_id);
            
            // Delete tenant if exists
            if ($conversion->tenant_id) {
                $tenant = Tenant::find($conversion->tenant_id);
                if ($tenant) {
                    // Drop tenant database
                    DB::statement("DROP DATABASE IF EXISTS `{$tenant->database}`");
                    $tenant->delete();
                }
            }

            // Reset admin status
            $admin->update([
                'status' => 'active',
                'tenant_id' => null,
                'converted_at' => null
            ]);

            // Mark conversion as rolled back
            $conversion->update([
                'conversion_status' => 'failed',
                'error_message' => 'Conversion rolled back manually'
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Conversion rollback failed', [
                'conversion_id' => $conversionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get conversion status
     */
    public function getConversionStatus(Admin $admin): array
    {
        $conversion = AdminTenantConversion::where('admin_id', $admin->id)
            ->latest()
            ->first();

        if (!$conversion) {
            return [
                'status' => 'not_started',
                'message' => 'Conversion not initiated'
            ];
        }

        return [
            'status' => $conversion->conversion_status,
            'message' => $this->getStatusMessage($conversion->conversion_status),
            'tenant_domain' => $conversion->tenant_domain,
            'error_message' => $conversion->error_message,
            'created_at' => $conversion->created_at,
            'converted_at' => $conversion->converted_at
        ];
    }    /**
     * Get status message
     */
    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            'initiated' => 'Conversion process initiated',
            'completed' => 'Successfully converted to tenant',
            'failed' => 'Conversion failed',
            default => 'Unknown status'
        };
    }

    /**
     * Send school created email notification
     */
    protected function sendSchoolCreatedEmail(Admin $admin, array $schoolData, array $tenantData): void
    {
        try {
            $tenantUrl = "http://{$tenantData['domain']}.localhost";
            Mail::to($admin->email)->send(new \App\Mail\Admin\SchoolCreatedEmail($admin, $schoolData, $tenantUrl));
        } catch (Exception $e) {
            Log::warning('Failed to send school created email', [
                'admin_id' => $admin->id,
                'tenant_id' => $tenantData['tenant_id'],
                'error' => $e->getMessage()
            ]);
        }
    }
}
