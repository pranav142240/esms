<?php

namespace App\Application\Services\Superadmin;

use App\Models\School;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class SchoolManagementService
{
    /**
     * Create a new school.
     */
    public function createSchool(array $data, int $superadminId): School
    {
        return DB::transaction(function () use ($data, $superadminId) {
            // Generate unique school code
            $data['school_code'] = School::generateSchoolCode();
            $data['slug'] = Str::slug($data['name']);
            $data['approved_by'] = $superadminId;
            $data['approved_at'] = now();
            $data['status'] = 'active';

            // Set subscription dates
            if (isset($data['subscription_plan_id'])) {
                $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
                $data['subscription_start_date'] = now();
                
                // Calculate end date based on billing cycle
                if ($plan->billing_cycle === 'yearly') {
                    $data['subscription_end_date'] = now()->addYear();
                } else {
                    $data['subscription_end_date'] = now()->addMonth();
                }
            }

            // Create school
            $school = School::create($data);

            // Create tenant database
            $this->createTenantDatabase($school);

            return $school->fresh();
        });
    }

    /**
     * Update school information.
     */
    public function updateSchool(School $school, array $data): School
    {
        return DB::transaction(function () use ($school, $data) {
            // Update slug if name changed
            if (isset($data['name']) && $data['name'] !== $school->name) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Handle subscription plan changes
            if (isset($data['subscription_plan_id']) && $data['subscription_plan_id'] !== $school->subscription_plan_id) {
                $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
                
                // Extend subscription based on new plan
                if ($plan->billing_cycle === 'yearly') {
                    $data['subscription_end_date'] = now()->addYear();
                } else {
                    $data['subscription_end_date'] = now()->addMonth();
                }
                
                $data['subscription_start_date'] = now();
            }

            $school->update($data);
            return $school->fresh();
        });
    }

    /**
     * Activate/Deactivate school.
     */
    public function toggleSchoolStatus(School $school): School
    {
        $newStatus = $school->status === 'active' ? 'inactive' : 'active';
        
        $school->update(['status' => $newStatus]);
        
        return $school->fresh();
    }

    /**
     * Extend school subscription.
     */
    public function extendSubscription(School $school, int $months): School
    {
        $currentEndDate = $school->subscription_end_date ?: now();
        $newEndDate = $currentEndDate->addMonths($months);
        
        $school->update([
            'subscription_end_date' => $newEndDate,
            'status' => 'active',
            'in_grace_period' => false,
            'grace_period_end_date' => null,
        ]);

        return $school->fresh();
    }

    /**
     * Get schools with filters.
     */
    public function getSchools(array $filters = [])
    {
        $query = School::with(['subscriptionPlan', 'approvedBy']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('school_code', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['subscription_plan_id'])) {
            $query->where('subscription_plan_id', $filters['subscription_plan_id']);
        }

        if (isset($filters['expired'])) {
            if ($filters['expired']) {
                $query->where('subscription_end_date', '<', now());
            } else {
                $query->where('subscription_end_date', '>=', now());
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Create tenant database for school.
     */
    private function createTenantDatabase(School $school): void
    {
        // Create tenant
        $tenant = Tenant::create([
            'id' => $school->slug,
        ]);

        // Create domain
        Domain::create([
            'domain' => $school->domain,
            'tenant_id' => $tenant->id,
        ]);

        // Update school with database name
        $school->update([
            'database_name' => $tenant->id
        ]);

        // Run tenant migrations
        $tenant->run(function () {
            \Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        });
    }
}
