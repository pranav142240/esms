<?php

namespace App\Interfaces\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\SubscriptionPlanRequest;
use App\Http\Resources\Superadmin\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $isActive = $request->input('is_active');

            $query = SubscriptionPlan::query();

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply active filter
            if ($isActive !== null) {
                $query->where('is_active', (bool) $isActive);
            }

            $plans = $query->orderBy('price')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Subscription plans retrieved successfully',
                'data' => SubscriptionPlanResource::collection($plans->items()),
                'meta' => [
                    'current_page' => $plans->currentPage(),
                    'last_page' => $plans->lastPage(),
                    'per_page' => $plans->perPage(),
                    'total' => $plans->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscription plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created subscription plan.
     */
    public function store(SubscriptionPlanRequest $request): JsonResponse
    {
        try {
            $plan = SubscriptionPlan::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan created successfully',
                'data' => new SubscriptionPlanResource($plan)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subscription plan.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan retrieved successfully',
                'data' => new SubscriptionPlanResource($plan)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified subscription plan.
     */
    public function update(SubscriptionPlanRequest $request, string $id): JsonResponse
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan updated successfully',
                'data' => new SubscriptionPlanResource($plan->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subscription plan (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            
            // Check if plan is being used by any schools
            if ($plan->schools()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subscription plan as it is being used by schools'
                ], 422);
            }

            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate/Deactivate a subscription plan.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->update(['is_active' => !$plan->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan status updated successfully',
                'data' => new SubscriptionPlanResource($plan->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription plan status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
