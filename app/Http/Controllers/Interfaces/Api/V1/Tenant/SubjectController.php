<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SubjectRequest;
use App\Http\Resources\Tenant\SubjectResource;
use App\Models\Models\Tenant\Subject;
use App\Models\Models\Tenant\Classes;
use App\Models\Models\Tenant\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $classId = $request->input('class_id');
            $teacherId = $request->input('teacher_id');
            $status = $request->input('status');

            $query = Subject::with(['class', 'teacher', 'createdBy']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply class filter
            if ($classId) {
                $query->where('class_id', $classId);
            }

            // Apply teacher filter
            if ($teacherId) {
                $query->where('teacher_id', $teacherId);
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            $subjects = $query->withCount(['students', 'exams'])
                            ->orderBy('name')
                            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Subjects retrieved successfully',
                'data' => SubjectResource::collection($subjects->items()),
                'meta' => [
                    'current_page' => $subjects->currentPage(),
                    'last_page' => $subjects->lastPage(),
                    'per_page' => $subjects->perPage(),
                    'total' => $subjects->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created subject.
     */
    public function store(SubjectRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            
            // Generate subject code if not provided
            if (!isset($validated['code'])) {
                $validated['code'] = $this->generateSubjectCode($validated['name']);
            }

            $validated['created_by'] = auth()->id();

            $subject = Subject::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully',
                'data' => new SubjectResource($subject->load(['class', 'teacher', 'createdBy']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subject.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $subject = Subject::with(['class', 'teacher', 'createdBy', 'students', 'exams'])
                            ->withCount(['students', 'exams'])
                            ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Subject retrieved successfully',
                'data' => new SubjectResource($subject)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified subject.
     */
    public function update(SubjectRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $subject = Subject::findOrFail($id);
            $validated = $request->validated();

            $subject->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully',
                'data' => new SubjectResource($subject->load(['class', 'teacher', 'createdBy']))
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subject (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subjects by class.
     */
    public function byClass(string $classId): JsonResponse
    {
        try {
            $class = Classes::findOrFail($classId);
            
            $subjects = Subject::where('class_id', $classId)
                            ->with(['teacher', 'createdBy'])
                            ->withCount(['students', 'exams'])
                            ->orderBy('name')
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Class subjects retrieved successfully',
                'data' => [
                    'class' => [
                        'id' => $class->id,
                        'name' => $class->name,
                        'grade_level' => $class->grade_level,
                    ],
                    'subjects' => SubjectResource::collection($subjects)
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

    /**
     * Get subjects by teacher.
     */
    public function byTeacher(string $teacherId): JsonResponse
    {
        try {
            $teacher = Teacher::findOrFail($teacherId);
            
            $subjects = Subject::where('teacher_id', $teacherId)
                            ->with(['class', 'createdBy'])
                            ->withCount(['students', 'exams'])
                            ->orderBy('name')
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Teacher subjects retrieved successfully',
                'data' => [
                    'teacher' => [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'employee_code' => $teacher->employee_code,
                    ],
                    'subjects' => SubjectResource::collection($subjects)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign teacher to subject.
     */
    public function assignTeacher(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'teacher_id' => 'required|exists:teachers,id'
            ]);

            $subject = Subject::findOrFail($id);
            $teacher = Teacher::findOrFail($request->teacher_id);

            $subject->update([
                'teacher_id' => $request->teacher_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher assigned to subject successfully',
                'data' => new SubjectResource($subject->load(['class', 'teacher', 'createdBy']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_subjects' => Subject::count(),
                'active_subjects' => Subject::where('status', 'active')->count(),
                'inactive_subjects' => Subject::where('status', 'inactive')->count(),
                'subjects_with_teachers' => Subject::whereNotNull('teacher_id')->count(),
                'subjects_without_teachers' => Subject::whereNull('teacher_id')->count(),
                'total_enrollments' => DB::table('subject_student')->count(),
                'average_enrollments_per_subject' => round(
                    DB::table('subject_student')->count() / max(Subject::count(), 1), 2
                ),
            ];

            // Subject distribution by class
            $subjectsByClass = Subject::selectRaw('class_id, COUNT(*) as count')
                                   ->join('classes', 'subjects.class_id', '=', 'classes.id')
                                   ->selectRaw('classes.name as class_name')
                                   ->groupBy('class_id', 'classes.name')
                                   ->orderBy('classes.name')
                                   ->get();

            return response()->json([
                'success' => true,
                'message' => 'Subject statistics retrieved successfully',
                'data' => [
                    'statistics' => $stats,
                    'subjects_by_class' => $subjectsByClass
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique subject code.
     */
    private function generateSubjectCode(string $name): string
    {
        $words = explode(' ', $name);
        $code = '';
        
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 2));
        }
        
        // Ensure uniqueness
        $originalCode = $code;
        $counter = 1;
        
        while (Subject::where('code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return $code;
    }
}
