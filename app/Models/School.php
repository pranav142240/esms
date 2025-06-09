<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant;
use App\Domain\Superadmin\Models\Superadmin;

class School extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo_path',
        'domain',
        'school_code',
        'tagline',
        'subscription_plan_id',
        'status',
        'subscription_start_date',
        'subscription_end_date',
        'in_grace_period',
        'grace_period_end_date',
        'form_data',
        'approved_at',
        'approved_by',
        'database_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'subscription_start_date' => 'date',
            'subscription_end_date' => 'date',
            'grace_period_end_date' => 'date',
            'approved_at' => 'datetime',
            'in_grace_period' => 'boolean',
        ];
    }

    /**
     * Get the subscription plan for this school.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the superadmin who approved this school.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Superadmin::class, 'approved_by');
    }

    /**
     * Get the tenant associated with this school.
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'database_name');
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->subscription_end_date && $this->subscription_end_date->isPast();
    }

    /**
     * Check if school is in grace period.
     */
    public function inGracePeriod(): bool
    {
        return $this->in_grace_period && 
               $this->grace_period_end_date && 
               $this->grace_period_end_date->isFuture();
    }

    /**
     * Check if school is active and has valid subscription.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->isExpired() || $this->inGracePeriod());
    }

    /**
     * Scope for active schools.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('subscription_end_date', '<', now());
    }

    /**
     * Generate unique school code.
     */
    public static function generateSchoolCode(): string
    {
        $year = date('Y');
        $lastSchool = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastSchool ? (int)substr($lastSchool->school_code, -4) + 1 : 1;
        
        return sprintf('SCH-%s-%04d', $year, $nextNumber);
    }
}
