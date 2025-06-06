<?php

namespace App\Http\Middleware\Superadmin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Domain\Superadmin\Models\Superadmin;

class SuperadminAuthMiddleware
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

        $superadmin = $accessToken->tokenable;

        if (!$superadmin instanceof Superadmin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token type'
            ], 401);
        }

        if (!$superadmin->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive'
            ], 403);
        }

        // Check if token has superadmin ability
        if (!$accessToken->can('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Attach superadmin to request
        $request->merge(['superadmin' => $superadmin]);

        return $next($request);
    }
}
