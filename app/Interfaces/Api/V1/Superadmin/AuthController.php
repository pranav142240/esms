<?php

namespace App\Interfaces\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Application\Services\Superadmin\SuperadminAuthService;
use App\Http\Requests\Superadmin\SuperadminLoginRequest;
use App\Http\Resources\Superadmin\SuperadminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private SuperadminAuthService $authService
    ) {}

    /**
     * Login superadmin.
     */
    public function login(SuperadminLoginRequest $request): JsonResponse
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
                    'superadmin' => new SuperadminResource($result['superadmin']),
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
     * Get authenticated superadmin.
     */
    public function user(Request $request): JsonResponse
    {
        $superadmin = $request->get('superadmin');

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => new SuperadminResource($superadmin)
        ], 200);
    }

    /**
     * Logout superadmin.
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
     * Refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $superadmin = $request->get('superadmin');
            $result = $this->authService->refreshToken($superadmin);

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
