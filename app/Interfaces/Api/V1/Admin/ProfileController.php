<?php

namespace App\Interfaces\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Application\Services\Admin\AdminProfileService;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use App\Http\Resources\Admin\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private AdminProfileService $profileService
    ) {}

    /**
     * Get admin profile.
     */
    public function show(Request $request): JsonResponse
    {
        $admin = $request->get('admin');

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => new AdminResource($admin)
        ], 200);
    }

    /**
     * Update admin profile.
     */
    public function update(AdminProfileUpdateRequest $request): JsonResponse
    {
        try {
            $admin = $request->get('admin');
            $updatedAdmin = $this->profileService->updateProfile($admin, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => new AdminResource($updatedAdmin)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Upload profile avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $admin = $request->get('admin');
            $updatedAdmin = $this->profileService->uploadAvatar($admin, $request->file('avatar'));

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'data' => new AdminResource($updatedAdmin)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get school setup status.
     */
    public function schoolSetupStatus(Request $request): JsonResponse
    {
        try {
            $admin = $request->get('admin');
            $status = $this->profileService->getSchoolSetupStatus($admin);

            return response()->json([
                'success' => true,
                'message' => 'School setup status retrieved successfully',
                'data' => $status
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
