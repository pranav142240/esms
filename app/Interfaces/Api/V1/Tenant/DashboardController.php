<?php

namespace App\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // User statistics
            $usersCount = User::count();
            $usersByRole = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name as role', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get();

            // Recent users
            $recentUsers = User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'email', 'created_at']);

            // Recently logged in users
            $recentlyLoggedIn = User::whereNotNull('last_login_at')
                ->orderBy('last_login_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'email', 'last_login_at']);

            // Build statistics response
            $statistics = [
                'users' => [
                    'total' => $usersCount,
                    'by_role' => $usersByRole,
                ],
                'recent_users' => $recentUsers,
                'recently_logged_in' => $recentlyLoggedIn,
                // Add more statistics here as needed (students, classes, etc.)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
