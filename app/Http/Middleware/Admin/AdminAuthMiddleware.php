<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Admin;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 401);
        }

        $admin = $accessToken->tokenable;

        if (!$admin instanceof Admin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token type'
            ], 401);
        }

        // Check if admin is suspended
        if ($admin->status === Admin::STATUS_SUSPENDED) {
            return response()->json([
                'success' => false,
                'message' => 'Account is suspended'
            ], 403);
        }

        // Check if admin has been converted (shouldn't access central system)
        if ($admin->status === Admin::STATUS_CONVERTED) {
            return response()->json([
                'success' => false,
                'message' => 'Account has been converted to tenant. Please use your school domain.'
            ], 403);
        }

        // Check if token has admin ability
        if (!$accessToken->can('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Attach admin to request
        $request->merge(['admin' => $admin]);

        return $next($request);
    }
}
