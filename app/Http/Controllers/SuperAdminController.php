<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    protected $tenantManager;
    
    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
        $this->middleware('auth');
        $this->middleware('superadmin');
    }
    
    /**
     * Display a dashboard for the superadmin
     */
    public function dashboard()
    {
        $tenantCount = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $recentTenants = Tenant::orderBy('created_at', 'desc')->take(5)->get();
        
        return view('superadmin.dashboard', compact('tenantCount', 'activeTenants', 'recentTenants'));
    }
    
    /**
     * List all tenants (schools)
     */
    public function tenants()
    {
        $tenants = Tenant::with('admin')->paginate(10);
        return view('superadmin.tenants.index', compact('tenants'));
    }
    
    /**
     * Show form to create a new tenant
     */
    public function createTenant()
    {
        return view('superadmin.tenants.create');
    }
    
    /**
     * Store a new tenant
     */
    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'timezone' => 'nullable|string',
        ]);
        
        try {
            $tenant = $this->tenantManager->createTenant($validated);
            return redirect()->route('superadmin.tenants')
                             ->with('success', 'School created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create school: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show tenant details
     */
    public function showTenant(Tenant $tenant)
    {
        return view('superadmin.tenants.show', compact('tenant'));
    }
    
    /**
     * Change tenant status
     */
    public function changeTenantStatus(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,suspended,inactive',
        ]);
        
        $tenant->update(['status' => $validated['status']]);
        
        return back()->with('success', 'School status updated successfully!');
    }
}
