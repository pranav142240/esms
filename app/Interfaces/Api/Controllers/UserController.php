<?php

namespace App\Interfaces\Api\Controllers;

use App\Application\Services\UserService;
use App\Domain\Shared\ValueObjects\TenantId;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    private $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        // Get current tenant ID from the tenancy system
        $tenantId = new TenantId(tenant('id'));
        
        $users = $this->userService->getUsersByTenant($tenantId);
        
        return response()->json([
            'users' => $users
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
        
        // Get current tenant ID from the tenancy system
        $tenantId = new TenantId(tenant('id'));
        
        $user = $this->userService->createUser(
            $request->name,
            $request->email,
            $request->password,
            $tenantId
        );
        
        return response()->json([
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ], 201);
    }
    
    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|string|in:admin,manager,user',
        ]);
        
        $this->userService->assignRole($userId, $request->role);
        
        return response()->json([
            'message' => 'Role assigned successfully'
        ]);
    }
}
