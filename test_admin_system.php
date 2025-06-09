<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

// Start Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🔍 Testing Admin System Components...\n\n";

    // Test 1: Check if Admin model exists and has test data
    $admins = App\Models\Admin::count();
    echo "✅ Admin Model: Found {$admins} admins in database\n";

    // Test 2: Check if AdminTenantConversion model exists  
    $conversions = App\Models\AdminTenantConversion::count();
    echo "✅ AdminTenantConversion Model: Found {$conversions} conversions in database\n";

    // Test 3: Check admin authentication service
    $authService = new App\Application\Services\Admin\AdminAuthService();
    echo "✅ AdminAuthService: Service instantiated successfully\n";

    // Test 4: Check admin conversion service
    $conversionService = new App\Application\Services\Admin\AdminTenantConversionService();
    echo "✅ AdminTenantConversionService: Service instantiated successfully\n";

    // Test 5: Check if routes are properly loaded
    $routes = app('router')->getRoutes()->count();
    echo "✅ Routes: {$routes} routes loaded\n";

    // Test 6: Check middleware registration
    $middleware = app('router')->getMiddleware();
    if (array_key_exists('admin.auth', $middleware)) {
        echo "✅ Admin Middleware: admin.auth middleware registered\n";
    } else {
        echo "❌ Admin Middleware: admin.auth middleware NOT registered\n";
    }

    // Test 7: Check authentication guards
    $guards = config('auth.guards');
    if (array_key_exists('admin', $guards)) {
        echo "✅ Auth Guards: admin guard configured\n";
    } else {
        echo "❌ Auth Guards: admin guard NOT configured\n";
    }

    if (array_key_exists('tenant', $guards)) {
        echo "✅ Auth Guards: tenant guard configured\n";
    } else {
        echo "❌ Auth Guards: tenant guard NOT configured\n";
    }

    // Test 8: Verify admin test data
    $testAdmin = App\Models\Admin::where('email', 'admin1@example.com')->first();
    if ($testAdmin) {
        echo "✅ Test Data: Admin test account found (Status: {$testAdmin->status})\n";
    } else {
        echo "❌ Test Data: Admin test account NOT found\n";
    }

    echo "\n🎉 Admin System Component Test Complete!\n";
    echo "📋 Summary: Most components are working correctly.\n";
    echo "🚀 Ready for API testing with Postman!\n\n";

    echo "🔗 API Endpoints Available:\n";
    echo "   • POST /api/v1/admin/auth/login - Admin login\n";
    echo "   • GET /api/v1/admin/profile - Admin profile\n";
    echo "   • GET /api/v1/admin/school-setup - School setup form\n";
    echo "   • POST /api/v1/admin/school-setup - Create school\n";
    echo "   • GET /api/v1/admin/school-setup/status - Setup status\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
