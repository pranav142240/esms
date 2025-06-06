<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\User;
use App\Domain\Shared\ValueObjects\TenantId;

interface UserRepository
{
    public function findById(string $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function save(User $user): void;
    
    public function findAllByTenant(TenantId $tenantId): array;
}
