<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'tenant_id',
        'converted_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'converted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The possible admin statuses.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SETTING_UP = 'setting_up';
    const STATUS_CONVERTED = 'converted';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Get all possible statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_SETTING_UP,
            self::STATUS_CONVERTED,
            self::STATUS_SUSPENDED,
        ];
    }

    /**
     * Check if admin is converted to tenant.
     */
    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED && !is_null($this->tenant_id);
    }

    /**
     * Check if admin can create school.
     */
    public function canCreateSchool(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_SETTING_UP]);
    }

    /**
     * Get the tenant relationship.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the conversion history.
     */
    public function conversions()
    {
        return $this->hasMany(AdminTenantConversion::class);
    }

    /**
     * Scope for active admins.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for pending admins.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for converted admins.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    /**
     * Scope for non-converted admins.
     */
    public function scopeNotConverted($query)
    {
        return $query->where('status', '!=', self::STATUS_CONVERTED);
    }
}
