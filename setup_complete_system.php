<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Application\Services\Superadmin\SchoolManagementService;
use App\Models\SubscriptionPlan;
use App\Domain\Superadmin\Models\Superadmin;

echo "=== SETTING UP COMPLETE ESMS SYSTEM ===\n\n";

// Get the superadmin
$superadmin = Superadmin::first();
if (!$superadmin) {
    echo "❌ No superadmin found! Please run SuperadminSeeder first.\n";
    exit(1);
}

// Get a subscription plan
$plan = SubscriptionPlan::first();
if (!$plan) {
    echo "❌ No subscription plans found! Please run SubscriptionPlansSeeder first.\n";
    exit(1);
}

// Create a test school
$schoolService = new SchoolManagementService();

$schoolData = [
    'name' => 'Demo High School',
    'email' => 'admin@demohighschool.com',
    'phone' => '+1-555-0123',
    'address' => '123 Education Street, Learning City, LC 12345',
    'domain' => 'demo', // This will be the subdomain
    'tagline' => 'Excellence in Education',
    'subscription_plan_id' => $plan->id,
];

try {
    echo "Creating test school: {$schoolData['name']}\n";
    $school = $schoolService->createSchool($schoolData, $superadmin->id);
    
    echo "✅ School created successfully!\n";
    echo "   - ID: {$school->id}\n";
    echo "   - Name: {$school->name}\n";
    echo "   - Domain: {$school->domain}\n";
    echo "   - School Code: {$school->school_code}\n";
    echo "   - Database: {$school->database_name}\n";
    
    // Now seed the tenant database
    echo "\nSeeding tenant database with sample data...\n";
    
    $tenant = $school->tenant;
    if ($tenant) {
        $tenant->run(function () {
            // Run tenant seeders
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantRolesAndPermissionsSeeder',
                '--force' => true
            ]);
            echo "✅ Roles and permissions seeded\n";
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantUsersSeeder',
                '--force' => true
            ]);
            echo "✅ Admin users seeded\n";
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantClassesSeeder',
                '--force' => true
            ]);
            echo "✅ Classes seeded\n";
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantSubjectsSeeder',
                '--force' => true
            ]);
            echo "✅ Subjects seeded\n";
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantTeachersSeeder',
                '--force' => true
            ]);
            echo "✅ Teachers seeded\n";
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantBooksSeeder',
                '--force' => true
            ]);
            echo "✅ Books seeded\n";
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantNoticesSeeder',
                '--force' => true
            ]);
            echo "✅ Notices seeded\n";
        });
        
        echo "\n🎉 SETUP COMPLETE!\n\n";
        echo "=== ACCESS INFORMATION ===\n";
        echo "SUPERADMIN ACCESS:\n";
        echo "  URL: http://localhost/esms/public\n";
        echo "  Email: {$superadmin->email}\n";
        echo "  Password: password\n\n";
        
        echo "SCHOOL ADMIN ACCESS:\n";
        echo "  URL: http://demo.localhost/esms/public\n";
        echo "  Alternative: http://localhost/esms/public/demo\n";
        echo "  Email: admin@school.com\n";
        echo "  Password: password\n\n";
        
        echo "=== NEXT STEPS ===\n";
        echo "1. Add to your hosts file (C:\\Windows\\System32\\drivers\\etc\\hosts):\n";
        echo "   127.0.0.1    demo.localhost\n\n";
        echo "2. Start your web server (XAMPP)\n";
        echo "3. Test the URLs above\n\n";
        
    } else {
        echo "❌ Could not find tenant for school\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating school: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
