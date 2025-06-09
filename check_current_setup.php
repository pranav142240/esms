<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\School;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant;

echo "=== CURRENT ESMS TENANCY SETUP ===\n\n";

// Check Schools
echo "SCHOOLS:\n";
$schools = School::all();
foreach($schools as $school) {
    echo "- Name: {$school->name}\n";
    echo "  Domain: {$school->domain}\n";
    echo "  Database Name: {$school->database_name}\n";
    echo "  Status: {$school->status}\n\n";
}

// Check Tenant Domains
echo "TENANT DOMAINS:\n";
$domains = Domain::all();
foreach($domains as $domain) {
    echo "- Domain: {$domain->domain}\n";
    echo "  Tenant ID: {$domain->tenant_id}\n\n";
}

// Check Tenants
echo "TENANTS:\n";
$tenants = Tenant::all();
foreach($tenants as $tenant) {
    echo "- Tenant ID: {$tenant->id}\n";
    echo "  Data: " . json_encode($tenant->data) . "\n\n";
}

echo "=== SUBDOMAIN ACCESS TEST ===\n";
echo "Based on your current setup:\n";
if($schools->count() > 0) {
    $firstSchool = $schools->first();
    $schoolDomain = $firstSchool->domain;
    
    echo "If a school has domain: '{$schoolDomain}'\n";
    echo "It should be accessible via: {$schoolDomain}.localhost\n";
    echo "Or via: {$schoolDomain}.localhost.com\n\n";
    
    echo "Current middleware: InitializeTenancyByDomain\n";
    echo "This middleware will:\n";
    echo "1. Extract the subdomain from the request\n";
    echo "2. Look for a domain record matching that subdomain\n";
    echo "3. Initialize the tenant context if found\n\n";
    
    echo "ANSWER TO YOUR QUESTION:\n";
    echo "YES - If you register a new tenant with domain 'school1',\n";
    echo "it SHOULD be accessible via 'school1.localhost.com'\n";
    echo "provided that:\n";
    echo "1. DNS/hosts file points school1.localhost.com to your server\n";
    echo "2. Your web server (Apache) is configured to handle subdomains\n";
    echo "3. The domain 'school1' is stored in the domains table\n";
} else {
    echo "No schools found in database.\n";
}
