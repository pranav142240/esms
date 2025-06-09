<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\ClassResource;
use App\Http\Requests\Tenant\ClassRequest;
use App\Models\Models\Tenant\Classes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');
            $grade = $request->input('grade');

            $query = Classes::with(['createdBy']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('section', 'like', "%{$search}%")
                      ->orWhere('class_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply grade filter
            if ($grade) {
                $query->where('grade', $grade);
            }

            $classes = $query->orderBy('grade')
                            ->orderBy('name')
                            ->orderBy('section')
                            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Classes retrieved successfully',
                'data' => ClassResource::collection($classes->items()),
                'meta' => [
                    'current_page' => $classes->currentPage(),
                    'last_page' => $classes->lastPage(),
                    'per_page' => $classes->perPage(),
                    'total' => $classes->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created class.
     */
    public function store(ClassRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['created_by'] = Auth::id();
            $validated['class_code'] = Classes::generateClassCode();

            $class = Classes::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Class created successfully',
                'data' => new ClassResource($class->load(['createdBy']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified class.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $class = Classes::with(['createdBy', 'students', 'subjects'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Class retrieved successfully',
                'data' => new ClassResource($class)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified class.
     */
    public function update(ClassRequest $request, string $id): JsonResponse
    {
        try {
            $class = Classes::findOrFail($id);
            $class->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Class updated successfully',
                'data' => new ClassResource($class->fresh(['createdBy']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified class.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $class = Classes::findOrFail($id);
            
            // Check if class has students
            if ($class->students()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete class with enrolled students'
                ], 422);
            }

            $class->delete();

            return response()->json([
                'success' => true,
                'message' => 'Class deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_classes' => Classes::count(),
                'active_classes' => Classes::where('status', 'active')->count(),
                'inactive_classes' => Classes::where('status', 'inactive')->count(),
                'classes_by_grade' => Classes::selectRaw('grade, COUNT(*) as count')
                                            ->groupBy('grade')
                                            ->orderBy('grade')
                                            ->get()
                                            ->pluck('count', 'grade'),
                'total_students' => Classes::withCount('students')->get()->sum('students_count'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Class statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students for a specific class.
     */
    public function students(string $id): JsonResponse
    {
        try {
            $class = Classes::findOrFail($id);
            $students = $class->students()->with(['user'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Class students retrieved successfully',
                'data' => [
                    'class' => new ClassResource($class),
                    'students' => $students->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'email' => $student->user->email,
                            'roll_number' => $student->roll_number,
                            'student_code' => $student->student_code,
                            'status' => $student->status,
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subjects for a specific class.
     */
    public function subjects(string $id): JsonResponse
    {
        try {
            $class = Classes::findOrFail($id);
            $subjects = $class->subjects()->get();

            return response()->json([
                'success' => true,
                'message' => 'Class subjects retrieved successfully',
                'data' => [
                    'class' => new ClassResource($class),
                    'subjects' => $subjects->map(function ($subject) {
                        return [
                            'id' => $subject->id,
                            'name' => $subject->name,
                            'code' => $subject->code,
                            'description' => $subject->description,
                            'status' => $subject->status,
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
