<?php

namespace App\Interfaces\Api\V1\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\SchoolInquiryRequest;
use App\Http\Resources\Superadmin\SchoolInquiryResource;
use App\Models\SchoolInquiry;
use App\Application\Services\Superadmin\SchoolManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolInquiryController extends Controller
{
    public function __construct(
        private SchoolManagementService $schoolService
    ) {}

    /**
     * Display a listing of school inquiries.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');

            $query = SchoolInquiry::query();

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereJsonContains('form_data->school_name', $search)
                      ->orWhereJsonContains('form_data->email', $search)
                      ->orWhereJsonContains('form_data->domain', $search);
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            $inquiries = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'School inquiries retrieved successfully',
                'data' => SchoolInquiryResource::collection($inquiries->items()),
                'meta' => [
                    'current_page' => $inquiries->currentPage(),
                    'last_page' => $inquiries->lastPage(),
                    'per_page' => $inquiries->perPage(),
                    'total' => $inquiries->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve school inquiries',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Store a newly created school inquiry (public endpoint).
     */
    public function store(SchoolInquiryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $inquiry = SchoolInquiry::create([
                'school_name' => $validated['school_name'],
                'school_email' => $validated['email'],
                'school_phone' => $validated['phone'],
                'school_address' => $validated['address'],
                'contact_person_name' => $validated['contact_person'] ?? 'N/A',
                'contact_person_email' => $validated['email'],
                'contact_person_phone' => $validated['phone'],
                'proposed_domain' => $validated['domain'],
                'school_tagline' => $validated['tagline'] ?? null,
                'form_data' => $validated, // Store complete form data as JSON
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'School inquiry submitted successfully',
                'data' => new SchoolInquiryResource($inquiry)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit school inquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified school inquiry.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $inquiry = SchoolInquiry::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'School inquiry retrieved successfully',
                'data' => new SchoolInquiryResource($inquiry)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'School inquiry not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified school inquiry.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $inquiry = SchoolInquiry::findOrFail($id);
            
            $request->validate([
                'status' => 'required|string|in:pending,under_review,approved,rejected,registered',
                'notes' => 'nullable|string|max:1000',
            ]);

            $inquiry->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'School inquiry updated successfully',
                'data' => new SchoolInquiryResource($inquiry->fresh())
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update school inquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified school inquiry (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $inquiry = SchoolInquiry::findOrFail($id);
            $inquiry->delete();

            return response()->json([
                'success' => true,
                'message' => 'School inquiry deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school inquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve and create school from inquiry.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $inquiry = SchoolInquiry::findOrFail($id);
            
            if ($inquiry->status === 'registered') {
                return response()->json([
                    'success' => false,
                    'message' => 'This inquiry has already been registered as a school'
                ], 422);
            }

            $request->validate([
                'subscription_plan_id' => 'required|exists:subscription_plans,id',
                'subscription_start_date' => 'required|date',
                'subscription_end_date' => 'required|date|after:subscription_start_date',
            ]);

            // Create school from inquiry
            $schoolData = array_merge(
                $inquiry->form_data,
                $request->only(['subscription_plan_id', 'subscription_start_date', 'subscription_end_date'])
            );

            $school = $this->schoolService->createSchool(
                $schoolData,
                $request->get('superadmin')
            );

            // Update inquiry status
            $inquiry->update([
                'status' => 'registered',
                'notes' => 'Approved and registered as school #' . $school->id,
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'School inquiry approved and school created successfully',
                'data' => [
                    'inquiry' => new SchoolInquiryResource($inquiry->fresh()),
                    'school_id' => $school->id
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve school inquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions on inquiries.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'action' => 'required|string|in:approve,reject,delete,archive',
                'inquiry_ids' => 'required|array',
                'inquiry_ids.*' => 'exists:school_inquiries,id',
            ]);

            $inquiries = SchoolInquiry::whereIn('id', $request->inquiry_ids)->get();
            $results = [];

            foreach ($inquiries as $inquiry) {
                switch ($request->action) {
                    case 'approve':
                        $inquiry->update(['status' => 'approved', 'reviewed_at' => now()]);
                        $results[] = "Inquiry #{$inquiry->id} approved";
                        break;
                    case 'reject':
                        $inquiry->update(['status' => 'rejected', 'reviewed_at' => now()]);
                        $results[] = "Inquiry #{$inquiry->id} rejected";
                        break;
                    case 'delete':
                        $inquiry->delete();
                        $results[] = "Inquiry #{$inquiry->id} deleted";
                        break;
                    case 'archive':
                        $inquiry->update(['status' => 'archived', 'reviewed_at' => now()]);
                        $results[] = "Inquiry #{$inquiry->id} archived";
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk action completed successfully',
                'data' => $results
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inquiry statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_inquiries' => SchoolInquiry::count(),
                'pending_inquiries' => SchoolInquiry::where('status', 'pending')->count(),
                'under_review_inquiries' => SchoolInquiry::where('status', 'under_review')->count(),
                'approved_inquiries' => SchoolInquiry::where('status', 'approved')->count(),
                'rejected_inquiries' => SchoolInquiry::where('status', 'rejected')->count(),
                'registered_inquiries' => SchoolInquiry::where('status', 'registered')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Inquiry statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve inquiry statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
