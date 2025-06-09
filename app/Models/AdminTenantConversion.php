<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminTenantConversion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'tenant_id',
        'old_admin_data',
        'conversion_status',
        'error_message',
        'converted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_admin_data' => 'array',
            'converted_at' => 'datetime',
        ];
    }

    /**
     * The possible conversion statuses.
     */
    const STATUS_INITIATED = 'initiated';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get all possible statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_INITIATED,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
        ];
    }

    /**
     * Get the admin relationship.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the tenant relationship.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for completed conversions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('conversion_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed conversions.
     */
    public function scopeFailed($query)
    {
        return $query->where('conversion_status', self::STATUS_FAILED);
    }

    /**
     * Check if conversion is completed.
     */
    public function isCompleted(): bool
    {
        return $this->conversion_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if conversion failed.
     */
    public function isFailed(): bool
    {
        return $this->conversion_status === self::STATUS_FAILED;
    }
}
