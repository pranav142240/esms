<?php

namespace App\Domain\User\Models;

use App\Domain\Shared\ValueObjects\TenantId;

class User
{
    private $id;
    private $name;
    private $email;
    private $roles = [];
    private $tenantId;

    public function __construct(string $id, string $name, string $email, TenantId $tenantId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->tenantId = $tenantId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTenantId(): TenantId
    {
        return $this->tenantId;
    }

    public function assignRole(string $role): void
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}
