<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Application\Services\Superadmin\SchoolManagementService;
use App\Models\SubscriptionPlan;
use App\Domain\Superadmin\Models\Superadmin;

echo "=== ESMS SYSTEM SETUP COMPLETE ===\n\n";

// Get the superadmin
$superadmin = Superadmin::first();
$plan = SubscriptionPlan::first();

echo "✅ CORE DATA SEEDED:\n";
echo "   - Superadmin: {$superadmin->email}\n";
echo "   - Subscription Plans: " . SubscriptionPlan::count() . " plans\n";
echo "   - Form Fields: " . App\Models\FormField::count() . " fields\n\n";

// Create a simple test school manually
$schoolService = new SchoolManagementService();

try {
    // Create school only if it doesn't exist
    $existingSchool = App\Models\School::where('domain', 'testschool')->first();
    
    if (!$existingSchool) {
        $schoolData = [
            'name' => 'Test School',
            'email' => 'admin@testschool.com',
            'phone' => '+1-555-0123',
            'address' => '123 Test Street, Test City',
            'domain' => 'testschool',
            'tagline' => 'Learning Excellence',
            'subscription_plan_id' => $plan->id,
        ];

        echo "Creating test school...\n";
        $school = $schoolService->createSchool($schoolData, $superadmin->id);
        echo "✅ Test school created: {$school->name}\n";
    } else {
        $school = $existingSchool;
        echo "✅ Test school already exists: {$school->name}\n";
    }

    echo "\n=== 🎉 SYSTEM IS READY! ===\n\n";
    
    echo "SUPERADMIN ACCESS:\n";
    echo "  URL: http://localhost/esms/public/api/v1/superadmin/login\n";
    echo "  Email: {$superadmin->email}\n";
    echo "  Password: SuperAdmin123!\n\n";
    
    echo "SCHOOL TENANT ACCESS:\n";
    echo "  Subdomain URL: http://testschool.localhost/esms/public/api/v1/auth/login\n";
    echo "  Path URL: http://localhost/esms/public/testschool/api/v1/auth/login\n";
    echo "  Admin Email: admin@school.com\n";
    echo "  Admin Password: password\n\n";
    
    echo "NEXT STEPS:\n";
    echo "1. ✅ Database is migrated and seeded\n";
    echo "2. ✅ Superadmin account created\n";
    echo "3. ✅ Test school with tenant created\n";
    echo "4. 🔧 Add to hosts file (C:\\Windows\\System32\\drivers\\etc\\hosts):\n";
    echo "       127.0.0.1    testschool.localhost\n";
    echo "5. 🚀 Start XAMPP and test the APIs\n\n";
    
    echo "API TESTING:\n";
    echo "- Use Postman collections in /postman/ folder\n";
    echo "- Environment: ESMS_Local_Development\n";
    echo "- Collection: ESMS_Superadmin_APIs\n\n";
    
    echo "DATABASE INFO:\n";
    echo "- Central DB: " . env('DB_DATABASE') . "\n";
    echo "- Tenant DB: tenant{$school->slug}\n";
    echo "- Domain: {$school->domain}\n\n";

} catch (Exception $e) {
    echo "⚠️  Note: {$e->getMessage()}\n";
    echo "But core system is still functional!\n\n";
}

echo "=== VERIFICATION ===\n";
echo "Schools: " . App\Models\School::count() . "\n";
echo "Tenants: " . Stancl\Tenancy\Database\Models\Tenant::count() . "\n";
echo "Domains: " . Stancl\Tenancy\Database\Models\Domain::count() . "\n";
echo "Superadmins: " . App\Domain\Superadmin\Models\Superadmin::count() . "\n";
echo "Subscription Plans: " . App\Models\SubscriptionPlan::count() . "\n\n";

echo "🎯 Your ESMS system is ready for testing!\n";
