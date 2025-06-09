<?php

namespace App\Interfaces\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Application\Services\Admin\AdminAuthService;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\AdminChangePasswordRequest;
use App\Http\Resources\Admin\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AdminAuthService $authService
    ) {}

    /**
     * Login admin.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'admin' => new AdminResource($result['admin']),
                    'token' => $result['token'],
                    'expires_at' => $result['expires_at']->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get authenticated admin.
     */
    public function user(Request $request): JsonResponse
    {
        $admin = $request->get('admin');

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => new AdminResource($admin)
        ], 200);
    }

    /**
     * Logout admin.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();
            $this->authService->logout($token);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Change password (required on first login).
     */
    public function changePassword(AdminChangePasswordRequest $request): JsonResponse
    {
        try {
            $admin = $request->get('admin');
            $this->authService->changePassword(
                $admin,
                $request->current_password,
                $request->new_password
            );

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $admin = $request->get('admin');
            $result = $this->authService->refreshToken($admin);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $result['token'],
                    'expires_at' => $result['expires_at']->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
            ], 500);
        }
    }
}
