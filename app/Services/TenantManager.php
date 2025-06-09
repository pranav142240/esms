<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantManager
{
    /**
     * Create a new tenant database
     */
    public function createTenant(array $data): Tenant
    {
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Create user for school admin
            $user = \App\Models\User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => bcrypt($data['admin_password']),
            ]);
            
            // Normalize domain name
            $domain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['school_name']));
            
            // Create tenant record
            $tenant = Tenant::create([
                'name' => $data['school_name'],
                'domain' => $domain,
                'database' => 'tenant_' . $domain,
                'admin_id' => $user->id,
                'status' => 'active',
                'settings' => [
                    'timezone' => $data['timezone'] ?? 'UTC',
                    'logo' => null,
                ],
            ]);
            
            // Create the actual database
            DB::statement('CREATE DATABASE IF NOT EXISTS ' . $tenant->database);
            
            // Configure and run migrations
            $this->configureTenantConnection($tenant);
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
            
            // Commit transaction
            DB::commit();
            
            return $tenant;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Configure connection to tenant database
     */
    public function configureTenantConnection(Tenant $tenant): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $tenant->database,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        
        // Purge the tenant connection
        DB::purge('tenant');
    }
    
    /**
     * Initialize a tenant connection for the current request
     */
    public function initializeTenant(Tenant $tenant): void
    {
        $this->configureTenantConnection($tenant);
        
        // Set the tenant for the request
        app()->instance('tenant', $tenant);
        
        // Set a global tenant helper function
        if (!function_exists('tenant')) {
            function tenant($key = null) {
                $tenant = app('tenant');
                return $key ? $tenant->$key : $tenant;
            }
        }
    }
}
