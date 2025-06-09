<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class TenantAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the token from the request
        $bearer = $request->bearerToken();
        if (!$bearer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. No token provided.'
            ], 401);
        }

        // Find token in the database
        $accessToken = PersonalAccessToken::findToken($bearer);
        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        // Check if token has expired
        if ($accessToken->created_at->addMinutes($accessToken->expires_at ?? (60 * 24)) < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 401);
        }

        // Check if token has tenant ability
        if (!$accessToken->can('tenant')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Continue with the request
        return $next($request);
    }
}
