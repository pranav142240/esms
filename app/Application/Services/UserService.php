<?php

namespace App\Application\Services;

use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\Shared\ValueObjects\TenantId;

class UserService
{
    private $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    public function createUser(string $name, string $email, string $password, TenantId $tenantId): User
    {
        // In a real implementation, we would:
        // 1. Check if email is already used
        // 2. Hash the password
        // 3. Generate a unique ID
        
        $id = (string) \Illuminate\Support\Str::uuid();
        
        $user = new User($id, $name, $email, $tenantId);
        
        // Assign default role
        $user->assignRole('user');
        
        $this->userRepository->save($user);
        
        return $user;
    }
    
    public function assignRole(string $userId, string $role): void
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new \Exception("User not found");
        }
        
        $user->assignRole($role);
        
        $this->userRepository->save($user);
    }
    
    public function getUsersByTenant(TenantId $tenantId): array
    {
        return $this->userRepository->findAllByTenant($tenantId);
    }
}
