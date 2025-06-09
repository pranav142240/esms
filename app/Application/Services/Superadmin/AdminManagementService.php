<?php

namespace App\Application\Services\Superadmin;

use App\Models\Admin;
use App\Models\AdminTenantConversion;
use App\Application\Services\Admin\AdminAuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminManagementService
{
    public function __construct(
        private AdminAuthService $adminAuthService
    ) {}

    /**
     * Get paginated list of admins.
     */
    public function getAdmins(array $filters = []): LengthAwarePaginator
    {
        $query = Admin::query();

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create new admin.
     */
    public function createAdmin(array $data): Admin
    {
        DB::beginTransaction();

        try {
            $temporaryPassword = $this->adminAuthService->generateTemporaryPassword();

            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($temporaryPassword),
                'status' => Admin::STATUS_PENDING,
            ]);

            // Send welcome email with credentials
            $this->adminAuthService->sendWelcomeEmail($admin, $temporaryPassword);

            DB::commit();

            return $admin;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update admin.
     */
    public function updateAdmin(Admin $admin, array $data): Admin
    {
        // Prevent updating converted admins
        if ($admin->status === Admin::STATUS_CONVERTED) {
            throw new \Exception('Cannot update converted admin. Admin is now managed as a tenant.', 422);
        }

        $admin->update($data);

        return $admin->fresh();
    }

    /**
     * Delete admin (soft delete).
     */
    public function deleteAdmin(Admin $admin): void
    {
        // Prevent deleting converted admins
        if ($admin->status === Admin::STATUS_CONVERTED) {
            throw new \Exception('Cannot delete converted admin. Admin is now managed as a tenant.', 422);
        }

        // Delete all tokens
        $admin->tokens()->delete();

        // Soft delete
        $admin->delete();
    }

    /**
     * Update admin status.
     */
    public function updateStatus(Admin $admin, string $status): Admin
    {
        // Validate status transition
        $this->validateStatusTransition($admin->status, $status);

        $admin->update(['status' => $status]);

        return $admin->fresh();
    }

    /**
     * Reset admin password.
     */
    public function resetPassword(Admin $admin): string
    {
        // Generate new temporary password
        $temporaryPassword = $this->adminAuthService->generateTemporaryPassword();

        // Update password
        $admin->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        // Delete all existing tokens
        $admin->tokens()->delete();

        // Send password reset email
        $this->adminAuthService->sendPasswordResetEmail($admin, $temporaryPassword);

        return $temporaryPassword;
    }

    /**
     * Resend credentials to admin.
     */
    public function resendCredentials(Admin $admin): void
    {
        if ($admin->status === Admin::STATUS_CONVERTED) {
            throw new \Exception('Cannot resend credentials for converted admin.', 422);
        }

        // Generate new temporary password
        $temporaryPassword = $this->adminAuthService->generateTemporaryPassword();

        // Update password
        $admin->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        // Delete all existing tokens
        $admin->tokens()->delete();

        // Send credentials
        $this->adminAuthService->sendWelcomeEmail($admin, $temporaryPassword);
    }

    /**
     * Get conversion status for admin.
     */
    public function getConversionStatus(Admin $admin): array
    {
        $conversions = $admin->conversions()->latest()->get();

        return [
            'admin_id' => $admin->id,
            'current_status' => $admin->status,
            'is_converted' => $admin->isConverted(),
            'tenant_id' => $admin->tenant_id,
            'converted_at' => $admin->converted_at,
            'conversions' => $conversions->map(function ($conversion) {
                return [
                    'id' => $conversion->id,
                    'status' => $conversion->conversion_status,
                    'error_message' => $conversion->error_message,
                    'converted_at' => $conversion->converted_at,
                ];
            }),
        ];
    }

    /**
     * Get admin statistics.
     */
    public function getAdminStatistics(): array
    {
        return [
            'total' => Admin::count(),
            'pending' => Admin::where('status', Admin::STATUS_PENDING)->count(),
            'active' => Admin::where('status', Admin::STATUS_ACTIVE)->count(),
            'setting_up' => Admin::where('status', Admin::STATUS_SETTING_UP)->count(),
            'converted' => Admin::where('status', Admin::STATUS_CONVERTED)->count(),
            'suspended' => Admin::where('status', Admin::STATUS_SUSPENDED)->count(),
            'conversions_today' => AdminTenantConversion::whereDate('created_at', today())->count(),
            'failed_conversions' => AdminTenantConversion::where('conversion_status', AdminTenantConversion::STATUS_FAILED)->count(),
        ];
    }

    /**
     * Validate status transition.
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        // Define allowed transitions
        $allowedTransitions = [
            Admin::STATUS_PENDING => [Admin::STATUS_ACTIVE, Admin::STATUS_SUSPENDED],
            Admin::STATUS_ACTIVE => [Admin::STATUS_SETTING_UP, Admin::STATUS_SUSPENDED],
            Admin::STATUS_SETTING_UP => [Admin::STATUS_ACTIVE, Admin::STATUS_CONVERTED, Admin::STATUS_SUSPENDED],
            Admin::STATUS_CONVERTED => [], // No transitions allowed from converted
            Admin::STATUS_SUSPENDED => [Admin::STATUS_ACTIVE, Admin::STATUS_PENDING],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || 
            !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new \Exception("Invalid status transition from {$currentStatus} to {$newStatus}", 422);
        }
    }
}
