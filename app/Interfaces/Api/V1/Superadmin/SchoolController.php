<?php

namespace App\Interfaces\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Application\Services\Superadmin\SchoolManagementService;
use App\Http\Requests\Superadmin\SchoolCreateRequest;
use App\Http\Requests\Superadmin\SchoolUpdateRequest;
use App\Http\Resources\Superadmin\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(
        private SchoolManagementService $schoolService
    ) {}

    /**
     * Display a listing of schools.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');
            $subscriptionPlan = $request->input('subscription_plan');

            $query = School::with(['subscriptionPlan', 'approvedBy']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('domain', 'like', "%{$search}%")
                      ->orWhere('school_code', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply subscription plan filter
            if ($subscriptionPlan) {
                $query->where('subscription_plan_id', $subscriptionPlan);
            }

            $schools = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Schools retrieved successfully',
                'data' => SchoolResource::collection($schools->items()),
                'meta' => [
                    'current_page' => $schools->currentPage(),
                    'last_page' => $schools->lastPage(),
                    'per_page' => $schools->perPage(),
                    'total' => $schools->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schools',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created school.
     */
    public function store(SchoolCreateRequest $request): JsonResponse
    {        try {
            $school = $this->schoolService->createSchool(
                $request->validated(),
                $request->get('superadmin')->id
            );

            return response()->json([
                'success' => true,
                'message' => 'School created successfully',
                'data' => new SchoolResource($school)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified school.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $school = School::with(['subscriptionPlan', 'approvedBy'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'School retrieved successfully',
                'data' => new SchoolResource($school)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'School not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified school.
     */
    public function update(SchoolUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $school = School::findOrFail($id);
            $updatedSchool = $this->schoolService->updateSchool(
                $school, 
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'School updated successfully',
                'data' => new SchoolResource($updatedSchool)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified school (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $school = School::findOrFail($id);
            $school->delete();

            return response()->json([
                'success' => true,
                'message' => 'School deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend a school.
     */
    public function suspend(string $id): JsonResponse
    {
        try {
            $school = School::findOrFail($id);
            $school->update(['status' => 'suspended']);

            return response()->json([
                'success' => true,
                'message' => 'School suspended successfully',
                'data' => new SchoolResource($school->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a school.
     */
    public function activate(string $id): JsonResponse
    {
        try {
            $school = School::findOrFail($id);
            $school->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'School activated successfully',
                'data' => new SchoolResource($school->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get school statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_schools' => School::count(),
                'active_schools' => School::where('status', 'active')->count(),
                'inactive_schools' => School::where('status', 'inactive')->count(),
                'suspended_schools' => School::where('status', 'suspended')->count(),
                'expired_subscriptions' => School::whereDate('subscription_end_date', '<', now())->count(),
                'schools_in_grace_period' => School::where('in_grace_period', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
