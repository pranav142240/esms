<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\NoticeRequest;
use App\Http\Resources\Tenant\NoticeResource;
use App\Models\Tenant\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    /**
     * Display a listing of notices.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notice::with(['createdBy'])->withCount(['comments']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = [
            'id', 'title', 'type', 'priority', 'target_audience', 'is_published',
            'published_at', 'expires_at', 'view_count', 'created_at', 'updated_at'
        ];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Special sorting for urgent notices
        if ($request->boolean('prioritize_urgent')) {
            $query->orderBy('is_urgent', 'desc')
                  ->orderBy('priority', 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $notices = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Notices retrieved successfully',
            'data' => NoticeResource::collection($notices),
            'meta' => [
                'current_page' => $notices->currentPage(),
                'last_page' => $notices->lastPage(),
                'per_page' => $notices->perPage(),
                'total' => $notices->total(),
                'filters_applied' => $this->getAppliedFilters($request),
                'sort' => ['by' => $sortBy, 'order' => $sortOrder]
            ]
        ]);
    }

    /**
     * Store a newly created notice.
     */
    public function store(NoticeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('notices/attachments', 'public');
                $data['attachment_path'] = $path;
                $data['attachment_name'] = $file->getClientOriginalName();
            }

            $notice = Notice::create($data);

            DB::commit();

            $notice->load(['createdBy']);

            return response()->json([
                'status' => 'success',
                'message' => 'Notice created successfully',
                'data' => new NoticeResource($notice)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if it exists
            if (isset($data['attachment_path'])) {
                Storage::disk('public')->delete($data['attachment_path']);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create notice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified notice.
     */
    public function show(Request $request, Notice $notice): JsonResponse
    {
        $notice->load(['createdBy', 'comments.user', 'attachments']);

        // Increment view count if not the creator
        if (auth()->id() !== $notice->created_by) {
            $notice->incrementViewCount();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notice retrieved successfully',
            'data' => (new NoticeResource($notice))->additional([
                'detailed' => true
            ])
        ]);
    }

    /**
     * Update the specified notice.
     */
    public function update(NoticeRequest $request, Notice $notice): JsonResponse
    {
        // Check if user can edit this notice
        if ($notice->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to edit this notice'
            ], 403);
        }

        DB::beginTransaction();
        
        try {
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($notice->attachment_path) {
                    Storage::disk('public')->delete($notice->attachment_path);
                }

                $file = $request->file('attachment');
                $path = $file->store('notices/attachments', 'public');
                $data['attachment_path'] = $path;
                $data['attachment_name'] = $file->getClientOriginalName();
            }

            $notice->update($data);

            DB::commit();

            $notice->load(['createdBy']);

            return response()->json([
                'status' => 'success',
                'message' => 'Notice updated successfully',
                'data' => new NoticeResource($notice)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if it exists
            if (isset($data['attachment_path'])) {
                Storage::disk('public')->delete($data['attachment_path']);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update notice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified notice.
     */
    public function destroy(Notice $notice): JsonResponse
    {
        // Check if user can delete this notice
        if ($notice->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this notice'
            ], 403);
        }

        // Delete attachment file if exists
        if ($notice->attachment_path) {
            Storage::disk('public')->delete($notice->attachment_path);
        }

        $notice->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notice deleted successfully'
        ]);
    }

    /**
     * Get published notices visible to the current user.
     */
    public function published(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userClassIds = $this->getUserClassIds($user);

        $query = Notice::visibleToUser($user, $userClassIds)
            ->with(['createdBy'])
            ->withCount(['comments']);

        // Apply additional filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->boolean('urgent_only')) {
            $query->urgent();
        }

        // Sorting with urgent notices first
        $query->orderBy('is_urgent', 'desc')
              ->orderBy('priority', 'desc')
              ->orderBy('published_at', 'desc');

        $perPage = min($request->get('per_page', 15), 100);
        $notices = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Published notices retrieved successfully',
            'data' => NoticeResource::collection($notices),
            'meta' => [
                'current_page' => $notices->currentPage(),
                'last_page' => $notices->lastPage(),
                'per_page' => $notices->perPage(),
                'total' => $notices->total()
            ]
        ]);
    }

    /**
     * Get urgent notices.
     */
    public function urgent(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userClassIds = $this->getUserClassIds($user);

        $query = Notice::visibleToUser($user, $userClassIds)
            ->urgent()
            ->with(['createdBy'])
            ->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 10), 50);
        $notices = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Urgent notices retrieved successfully',
            'data' => NoticeResource::collection($notices),
            'meta' => [
                'current_page' => $notices->currentPage(),
                'last_page' => $notices->lastPage(),
                'per_page' => $notices->perPage(),
                'total' => $notices->total()
            ]
        ]);
    }

    /**
     * Publish a notice.
     */
    public function publish(Request $request, Notice $notice): JsonResponse
    {
        if ($notice->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to publish this notice'
            ], 403);
        }

        $publishAt = $request->has('publish_at') ? 
            Carbon::parse($request->publish_at) : Carbon::now();

        if ($request->has('publish_at')) {
            $notice->schedule($publishAt);
            $message = 'Notice scheduled for publication successfully';
        } else {
            $notice->publish($publishAt);
            $message = 'Notice published successfully';
        }

        $notice->load(['createdBy']);

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => new NoticeResource($notice)
        ]);
    }

    /**
     * Unpublish a notice.
     */
    public function unpublish(Notice $notice): JsonResponse
    {
        if ($notice->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to unpublish this notice'
            ], 403);
        }

        $notice->unpublish();
        $notice->load(['createdBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'Notice unpublished successfully',
            'data' => new NoticeResource($notice)
        ]);
    }

    /**
     * Mark notice as urgent.
     */
    public function markUrgent(Notice $notice): JsonResponse
    {
        if ($notice->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to modify this notice'
            ], 403);
        }

        $notice->markAsUrgent();
        $notice->load(['createdBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'Notice marked as urgent successfully',
            'data' => new NoticeResource($notice)
        ]);
    }

    /**
     * Remove urgent status from notice.
     */
    public function removeUrgent(Notice $notice): JsonResponse
    {
        if ($notice->created_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to modify this notice'
            ], 403);
        }

        $notice->removeUrgentStatus();
        $notice->load(['createdBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'Urgent status removed successfully',
            'data' => new NoticeResource($notice)
        ]);
    }

    /**
     * Get notice statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_notices' => Notice::count(),
            'published_notices' => Notice::where('is_published', true)->count(),
            'draft_notices' => Notice::where('is_published', false)->count(),
            'urgent_notices' => Notice::where('is_urgent', true)->count(),
            'expired_notices' => Notice::where('expires_at', '<', Carbon::now())->count(),
            'scheduled_notices' => Notice::where('is_published', true)
                ->where('published_at', '>', Carbon::now())
                ->count(),
            'total_views' => Notice::sum('view_count'),
            'period_stats' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'new_notices' => Notice::where('created_at', '>=', $startDate)->count(),
                'published_in_period' => Notice::where('published_at', '>=', $startDate)->count(),
            ]
        ];

        // Notice types breakdown
        $typeStats = Notice::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        // Priority breakdown
        $priorityStats = Notice::select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();

        // Audience targeting breakdown
        $audienceStats = Notice::select('target_audience', DB::raw('COUNT(*) as count'))
            ->groupBy('target_audience')
            ->get()
            ->pluck('count', 'target_audience')
            ->toArray();

        // Most viewed notices
        $mostViewed = Notice::select('id', 'title', 'view_count', 'created_at')
            ->where('view_count', '>', 0)
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Notice statistics retrieved successfully',
            'data' => [
                'overview' => $stats,
                'breakdown' => [
                    'by_type' => $typeStats,
                    'by_priority' => $priorityStats,
                    'by_audience' => $audienceStats,
                ],
                'most_viewed' => $mostViewed,
            ]
        ]);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Status filters
        if ($request->has('status')) {
            switch ($request->status) {
                case 'published':
                    $query->published();
                    break;
                case 'draft':
                    $query->where('is_published', false);
                    break;
                case 'expired':
                    $query->where('expires_at', '<', Carbon::now());
                    break;
                case 'scheduled':
                    $query->where('is_published', true)
                          ->where('published_at', '>', Carbon::now());
                    break;
                case 'active':
                    $query->published()->active();
                    break;
            }
        }

        // Type filter
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Priority filter
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Audience filter
        if ($request->has('target_audience')) {
            $query->forAudience($request->target_audience);
        }

        // Urgent filter
        if ($request->boolean('urgent_only')) {
            $query->urgent();
        }

        // Date range filters
        if ($request->has('created_from')) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to')) {
            $query->where('created_at', '<=', $request->created_to);
        }

        if ($request->has('published_from')) {
            $query->where('published_at', '>=', $request->published_from);
        }

        if ($request->has('published_to')) {
            $query->where('published_at', '<=', $request->published_to);
        }

        // Created by filter
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('createdBy', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Get applied filters for meta information.
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];
        
        $filterKeys = [
            'status', 'type', 'priority', 'target_audience', 'urgent_only',
            'created_from', 'created_to', 'published_from', 'published_to',
            'created_by', 'search'
        ];
        
        foreach ($filterKeys as $key) {
            if ($request->has($key)) {
                $filters[$key] = $request->get($key);
            }
        }
        
        return $filters;
    }

    /**
     * Get start date based on period.
     */
    private function getStartDate(string $period): Carbon
    {
        switch ($period) {
            case 'day':
                return Carbon::now()->startOfDay();
            case 'week':
                return Carbon::now()->startOfWeek();
            case 'year':
                return Carbon::now()->startOfYear();
            case 'month':
            default:
                return Carbon::now()->startOfMonth();
        }
    }

    /**
     * Get user's associated class IDs based on their role.
     */
    private function getUserClassIds($user): array
    {
        // This would depend on your user-class relationship implementation
        // For example, if students belong to classes, teachers teach classes, etc.
        
        if ($user->hasRole('student')) {
            return $user->classes?->pluck('id')->toArray() ?? [];
        }
        
        if ($user->hasRole('teacher')) {
            return $user->taughtClasses?->pluck('id')->toArray() ?? [];
        }
        
        if ($user->hasRole('parent')) {
            return $user->children?->pluck('class_id')->toArray() ?? [];
        }
        
        return [];
    }
}
