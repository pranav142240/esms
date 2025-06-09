<?php

namespace App\Application\Services\Admin;

use App\Models\Admin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminProfileService
{
    /**
     * Update admin profile.
     */
    public function updateProfile(Admin $admin, array $data): Admin
    {
        // Remove sensitive fields that shouldn't be updated through profile
        unset($data['password'], $data['status'], $data['tenant_id'], $data['converted_at']);

        $admin->update($data);

        return $admin->fresh();
    }

    /**
     * Upload admin avatar.
     */
    public function uploadAvatar(Admin $admin, UploadedFile $file): Admin
    {
        // Delete old avatar if exists
        if ($admin->avatar && Storage::exists($admin->avatar)) {
            Storage::delete($admin->avatar);
        }

        // Store new avatar
        $path = $file->store('avatars/admins', 'public');

        $admin->update(['avatar' => $path]);

        return $admin->fresh();
    }

    /**
     * Get school setup status for admin.
     */
    public function getSchoolSetupStatus(Admin $admin): array
    {
        $canCreateSchool = $admin->canCreateSchool();
        $isConverted = $admin->isConverted();

        $status = [
            'admin_id' => $admin->id,
            'current_status' => $admin->status,
            'can_create_school' => $canCreateSchool,
            'is_converted' => $isConverted,
            'school_setup_available' => $canCreateSchool && !$isConverted,
            'tenant_info' => null,
        ];

        if ($isConverted && $admin->tenant) {
            $status['tenant_info'] = [
                'tenant_id' => $admin->tenant->id,
                'school_domain' => $admin->tenant->domains->first()?->domain,
                'converted_at' => $admin->converted_at,
            ];
        }

        return $status;
    }

    /**
     * Get admin activity summary.
     */
    public function getActivitySummary(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id,
            'created_at' => $admin->created_at,
            'last_login_at' => $admin->tokens()->latest()->first()?->last_used_at,
            'total_logins' => $admin->tokens()->count(),
            'status_history' => $this->getStatusHistory($admin),
        ];
    }

    /**
     * Get admin status history.
     */
    private function getStatusHistory(Admin $admin): array
    {
        // This is a simplified version. In a real application, 
        // you might want to track status changes in a separate table
        return [
            [
                'status' => $admin->status,
                'changed_at' => $admin->updated_at,
                'is_current' => true,
            ]
        ];
    }
}
