<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'admin_id',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the admin user associated with this tenant
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Determine if the tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
