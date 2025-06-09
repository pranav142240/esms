<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\ExamResource;
use App\Http\Requests\Tenant\ExamRequest;
use App\Models\Models\Tenant\Exam;
use App\Models\Models\Tenant\Classes;
use App\Models\Models\Tenant\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');
            $type = $request->input('type');
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');

            $query = Exam::with(['class', 'subject', 'createdBy']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('exam_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply type filter
            if ($type) {
                $query->where('type', $type);
            }

            // Apply class filter
            if ($classId) {
                $query->where('class_id', $classId);
            }

            // Apply subject filter
            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $exams = $query->orderBy('exam_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Exams retrieved successfully',
                'data' => ExamResource::collection($exams->items()),
                'meta' => [
                    'current_page' => $exams->currentPage(),
                    'last_page' => $exams->lastPage(),
                    'per_page' => $exams->perPage(),
                    'total' => $exams->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created exam.
     */
    public function store(ExamRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['created_by'] = Auth::id();
            $validated['exam_number'] = Exam::generateExamNumber();

            $exam = Exam::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam created successfully',
                'data' => new ExamResource($exam->load(['class', 'subject', 'createdBy']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exam.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $exam = Exam::with(['class', 'subject', 'createdBy'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Exam retrieved successfully',
                'data' => new ExamResource($exam)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified exam.
     */
    public function update(ExamRequest $request, string $id): JsonResponse
    {
        try {
            $exam = Exam::findOrFail($id);
            
            // Check if exam is already completed
            if ($exam->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update completed exam'
                ], 422);
            }

            $exam->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully',
                'data' => new ExamResource($exam->fresh(['class', 'subject', 'createdBy']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $exam = Exam::findOrFail($id);
            
            // Check if exam is ongoing or completed
            if (in_array($exam->status, ['ongoing', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete ongoing or completed exam'
                ], 422);
            }

            $exam->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exam deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by class.
     */
    public function byClass(string $classId): JsonResponse
    {
        try {
            $exams = Exam::with(['subject', 'createdBy'])
                         ->where('class_id', $classId)
                         ->orderBy('exam_date', 'desc')
                         ->get();

            return response()->json([
                'success' => true,
                'message' => 'Class exams retrieved successfully',
                'data' => ExamResource::collection($exams)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by subject.
     */
    public function bySubject(string $subjectId): JsonResponse
    {
        try {
            $exams = Exam::with(['class', 'createdBy'])
                         ->where('subject_id', $subjectId)
                         ->orderBy('exam_date', 'desc')
                         ->get();

            return response()->json([
                'success' => true,
                'message' => 'Subject exams retrieved successfully',
                'data' => ExamResource::collection($exams)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subject exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start an exam.
     */
    public function start(string $id): JsonResponse
    {
        try {
            $exam = Exam::findOrFail($id);
            
            if ($exam->status !== 'scheduled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only scheduled exams can be started'
                ], 422);
            }

            $exam->update([
                'status' => 'ongoing',
                'started_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully',
                'data' => new ExamResource($exam->fresh(['class', 'subject', 'createdBy']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete an exam.
     */
    public function complete(string $id): JsonResponse
    {
        try {
            $exam = Exam::findOrFail($id);
            
            if ($exam->status !== 'ongoing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ongoing exams can be completed'
                ], 422);
            }

            $exam->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exam completed successfully',
                'data' => new ExamResource($exam->fresh(['class', 'subject', 'createdBy']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_exams' => Exam::count(),
                'scheduled_exams' => Exam::where('status', 'scheduled')->count(),
                'ongoing_exams' => Exam::where('status', 'ongoing')->count(),
                'completed_exams' => Exam::where('status', 'completed')->count(),
                'cancelled_exams' => Exam::where('status', 'cancelled')->count(),
                'upcoming_exams' => Exam::where('exam_date', '>', now())
                                       ->where('status', 'scheduled')
                                       ->count(),
                'exams_today' => Exam::whereDate('exam_date', today())->count(),
                'exams_this_week' => Exam::whereBetween('exam_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'exams_this_month' => Exam::whereMonth('exam_date', now()->month)
                                         ->whereYear('exam_date', now()->year)
                                         ->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Exam statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
