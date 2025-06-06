<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Models\User as DomainUser;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\Shared\ValueObjects\TenantId;
use App\Models\User as EloquentUser;
use Stancl\Tenancy\Tenancy;

class EloquentUserRepository implements UserRepository
{
    private $tenancy;

    public function __construct(Tenancy $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    public function findById(string $id): ?DomainUser
    {
        $user = EloquentUser::find($id);
        
        if (!$user) {
            return null;
        }
        
        return $this->mapToDomainEntity($user);
    }
    
    public function findByEmail(string $email): ?DomainUser
    {
        $user = EloquentUser::where('email', $email)->first();
        
        if (!$user) {
            return null;
        }
        
        return $this->mapToDomainEntity($user);
    }
    
    public function save(DomainUser $user): void
    {
        $eloquentUser = EloquentUser::findOrNew($user->getId());
        
        $eloquentUser->name = $user->getName();
        $eloquentUser->email = $user->getEmail();
        // In a real implementation, we'd handle roles through Spatie's HasRoles trait
        
        $eloquentUser->save();
    }
    
    public function findAllByTenant(TenantId $tenantId): array
    {
        // We're using Stancl Tenancy which automatically scopes queries to the current tenant
        // In this implementation, we assume the tenant is already set in the context
        
        $users = EloquentUser::all();
        
        return $users->map(function ($user) {
            return $this->mapToDomainEntity($user);
        })->toArray();
    }
    
    private function mapToDomainEntity(EloquentUser $eloquentUser): DomainUser
    {
        $tenantId = new TenantId($this->tenancy->tenant ? $this->tenancy->tenant->id : 'central');
        
        $user = new DomainUser(
            $eloquentUser->id,
            $eloquentUser->name,
            $eloquentUser->email,
            $tenantId
        );
        
        // In a real implementation, we would load roles from Spatie's Permission
        foreach ($eloquentUser->getRoleNames() as $role) {
            $user->assignRole($role);
        }
        
        return $user;
    }
}
