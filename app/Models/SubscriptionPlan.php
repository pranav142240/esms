<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'features',
        'user_limit',
        'storage_limit_gb',
        'custom_branding',
        'api_access',
        'priority_support',
        'is_active',
        'is_default',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'custom_branding' => 'boolean',
            'api_access' => 'boolean',
            'priority_support' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get schools using this plan.
     */
    public function schools()
    {
        return $this->hasMany(School::class, 'subscription_plan_id');
    }

    /**
     * Scope for active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default plan.
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }
}
