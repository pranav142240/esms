<?php

namespace App\Interfaces\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Application\Services\Superadmin\AdminManagementService;
use App\Http\Requests\Superadmin\CreateAdminRequest;
use App\Http\Requests\Superadmin\UpdateAdminRequest;
use App\Http\Resources\Superadmin\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private AdminManagementService $adminService
    ) {}

    /**
     * Display a listing of admins.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'search', 'per_page']);
            $admins = $this->adminService->getAdmins($filters);

            return response()->json([
                'success' => true,
                'message' => 'Admins retrieved successfully',
                'data' => AdminResource::collection($admins->items()),
                'meta' => [
                    'current_page' => $admins->currentPage(),
                    'per_page' => $admins->perPage(),
                    'total' => $admins->total(),
                    'last_page' => $admins->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new admin.
     */
    public function store(CreateAdminRequest $request): JsonResponse
    {
        try {
            $admin = $this->adminService->createAdmin($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully',
                'data' => new AdminResource($admin)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified admin.
     */
    public function show(Admin $admin): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin retrieved successfully',
            'data' => new AdminResource($admin->load(['tenant', 'conversions']))
        ], 200);
    }

    /**
     * Update the specified admin.
     */
    public function update(UpdateAdminRequest $request, Admin $admin): JsonResponse
    {
        try {
            $updatedAdmin = $this->adminService->updateAdmin($admin, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
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
     * Remove the specified admin (soft delete).
     */
    public function destroy(Admin $admin): JsonResponse
    {
        try {
            $this->adminService->deleteAdmin($admin);

            return response()->json([
                'success' => true,
                'message' => 'Admin deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Update admin status.
     */
    public function updateStatus(Request $request, Admin $admin): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Admin::getStatuses())
        ]);

        try {
            $updatedAdmin = $this->adminService->updateStatus($admin, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Admin status updated successfully',
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
     * Reset admin password.
     */
    public function resetPassword(Admin $admin): JsonResponse
    {
        try {
            $newPassword = $this->adminService->resetPassword($admin);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
                'data' => [
                    'admin_id' => $admin->id,
                    'temporary_password' => $newPassword,
                    'message' => 'Admin has been notified via email'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend credentials to admin.
     */
    public function resendCredentials(Admin $admin): JsonResponse
    {
        try {
            $this->adminService->resendCredentials($admin);

            return response()->json([
                'success' => true,
                'message' => 'Credentials sent successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversion status for admin.
     */
    public function conversionStatus(Admin $admin): JsonResponse
    {
        try {
            $status = $this->adminService->getConversionStatus($admin);

            return response()->json([
                'success' => true,
                'message' => 'Conversion status retrieved successfully',
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
