<?php

namespace App\Domain\Superadmin\Repositories;

use App\Domain\Superadmin\Models\Superadmin;
use App\Domain\Superadmin\Repositories\SuperadminRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SuperadminRepository implements SuperadminRepositoryInterface
{
    public function findByEmail(string $email): ?Superadmin
    {
        return Superadmin::where('email', $email)->first();
    }

    public function findById(int $id): ?Superadmin
    {
        return Superadmin::find($id);
    }

    public function create(array $data): Superadmin
    {
        return Superadmin::create($data);
    }

    public function update(Superadmin $superadmin, array $data): bool
    {
        return $superadmin->update($data);
    }

    public function delete(Superadmin $superadmin): bool
    {
        return $superadmin->delete();
    }

    public function getActive(): Collection
    {
        return Superadmin::where('is_active', true)->get();
    }
}
