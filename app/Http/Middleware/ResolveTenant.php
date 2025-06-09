<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    protected $tenantManager;
    
    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Skip for superadmin
            if ($user->isSuperAdmin()) {
                return $next($request);
            }
            
            // Find tenant for admin user
            $tenant = Tenant::where('admin_id', $user->id)->first();
            
            if ($tenant && $tenant->isActive()) {
                // Initialize tenant connection
                $this->tenantManager->initializeTenant($tenant);
                return $next($request);
            }
            
            // For staff users, find tenant by subdomain
            $subdomain = explode('.', $request->getHost())[0];
            $tenant = Tenant::where('domain', $subdomain)->first();
            
            if ($tenant && $tenant->isActive()) {
                $this->tenantManager->initializeTenant($tenant);
                return $next($request);
            }
            
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        return $next($request);
    }
}
