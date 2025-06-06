<?php

namespace App\Domain\Superadmin\Repositories;

use App\Domain\Superadmin\Models\Superadmin;
use Illuminate\Database\Eloquent\Collection;

interface SuperadminRepositoryInterface
{
    public function findByEmail(string $email): ?Superadmin;
    public function findById(int $id): ?Superadmin;
    public function create(array $data): Superadmin;
    public function update(Superadmin $superadmin, array $data): bool;
    public function delete(Superadmin $superadmin): bool;
    public function getActive(): Collection;
}
