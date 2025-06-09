<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TeacherRequest;
use App\Http\Resources\Tenant\TeacherResource;
use App\Models\Models\Tenant\Teacher;
use App\Models\Models\Tenant\Classes;
use App\Models\Models\Tenant\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $department = $request->input('department');
            $status = $request->input('status');
            $designation = $request->input('designation');

            $query = Teacher::with(['user', 'createdBy']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('department', 'like', "%{$search}%")
                      ->orWhere('designation', 'like', "%{$search}%");
                });
            }

            // Apply department filter
            if ($department) {
                $query->where('department', $department);
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply designation filter
            if ($designation) {
                $query->where('designation', $designation);
            }

            $teachers = $query->withCount(['subjects', 'classes', 'exams'])
                            ->orderBy('name')
                            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Teachers retrieved successfully',
                'data' => TeacherResource::collection($teachers->items()),
                'meta' => [
                    'current_page' => $teachers->currentPage(),
                    'last_page' => $teachers->lastPage(),
                    'per_page' => $teachers->perPage(),
                    'total' => $teachers->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created teacher.
     */
    public function store(TeacherRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            
            // Generate employee code if not provided
            if (!isset($validated['employee_code'])) {
                $validated['employee_code'] = Teacher::generateEmployeeCode();
            }

            $validated['created_by'] = auth()->id();

            $teacher = Teacher::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully',
                'data' => new TeacherResource($teacher->load(['user', 'createdBy']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $teacher = Teacher::with(['user', 'createdBy', 'subjects', 'classes', 'exams'])
                            ->withCount(['subjects', 'classes', 'exams', 'students'])
                            ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Teacher retrieved successfully',
                'data' => new TeacherResource($teacher)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified teacher.
     */
    public function update(TeacherRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $teacher = Teacher::findOrFail($id);
            $validated = $request->validated();

            $teacher->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully',
                'data' => new TeacherResource($teacher->load(['user', 'createdBy']))
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $teacher = Teacher::findOrFail($id);
            $teacher->delete();

            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teachers by department.
     */
    public function byDepartment(Request $request): JsonResponse
    {
        try {
            $department = $request->input('department');
            $status = $request->input('status', 'active');

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department parameter is required'
                ], 400);
            }
            
            $teachers = Teacher::where('department', $department)
                            ->where('status', $status)
                            ->with(['user', 'createdBy'])
                            ->withCount(['subjects', 'classes'])
                            ->orderBy('name')
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Department teachers retrieved successfully',
                'data' => [
                    'department' => $department,
                    'teachers' => TeacherResource::collection($teachers)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve department teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available teachers for assignment.
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');
            
            $query = Teacher::where('status', 'active')
                          ->with(['user', 'createdBy'])
                          ->withCount(['subjects', 'classes']);

            // Filter teachers not assigned to specific class
            if ($classId) {
                $query->whereDoesntHave('classes', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            }

            // Filter teachers not assigned to specific subject
            if ($subjectId) {
                $query->where(function ($q) use ($subjectId) {
                    $q->whereNull('id')
                      ->orWhereDoesntHave('subjects', function ($subQuery) use ($subjectId) {
                          $subQuery->where('subject_id', $subjectId);
                      });
                });
            }

            $teachers = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Available teachers retrieved successfully',
                'data' => TeacherResource::collection($teachers)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign teacher to class.
     */
    public function assignToClass(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'role' => 'in:class_teacher,subject_teacher,assistant'
            ]);

            $teacher = Teacher::findOrFail($id);
            $class = Classes::findOrFail($request->class_id);
            $role = $request->input('role', 'subject_teacher');

            // Check if assignment already exists
            $existingAssignment = DB::table('class_teacher')
                                  ->where('class_id', $request->class_id)
                                  ->where('teacher_id', $id)
                                  ->where('role', $role)
                                  ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher is already assigned to this class with this role'
                ], 400);
            }

            // Create assignment
            DB::table('class_teacher')->insert([
                'class_id' => $request->class_id,
                'teacher_id' => $id,
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher assigned to class successfully',
                'data' => [
                    'teacher' => new TeacherResource($teacher->load(['user', 'createdBy'])),
                    'class' => [
                        'id' => $class->id,
                        'name' => $class->name,
                        'grade_level' => $class->grade_level,
                    ],
                    'role' => $role
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign teacher to class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher subjects.
     */
    public function subjects(string $id): JsonResponse
    {
        try {
            $teacher = Teacher::findOrFail($id);
            
            $subjects = Subject::where('teacher_id', $id)
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
                    'subjects' => $subjects->map(function ($subject) {
                        return [
                            'id' => $subject->id,
                            'name' => $subject->name,
                            'code' => $subject->code,
                            'credits' => $subject->credits,
                            'status' => $subject->status,
                            'class' => [
                                'id' => $subject->class->id,
                                'name' => $subject->class->name,
                                'grade_level' => $subject->class->grade_level,
                            ],
                            'students_count' => $subject->students_count,
                            'exams_count' => $subject->exams_count,
                        ];
                    })
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
     * Get teacher classes.
     */
    public function classes(string $id): JsonResponse
    {
        try {
            $teacher = Teacher::findOrFail($id);
            
            $classes = DB::table('class_teacher')
                       ->join('classes', 'class_teacher.class_id', '=', 'classes.id')
                       ->where('class_teacher.teacher_id', $id)
                       ->select([
                           'classes.*',
                           'class_teacher.role',
                           'class_teacher.created_at as assigned_at'
                       ])
                       ->get();

            return response()->json([
                'success' => true,
                'message' => 'Teacher classes retrieved successfully',
                'data' => [
                    'teacher' => [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'employee_code' => $teacher->employee_code,
                    ],
                    'classes' => $classes->map(function ($class) {
                        return [
                            'id' => $class->id,
                            'name' => $class->name,
                            'grade_level' => $class->grade_level,
                            'capacity' => $class->capacity,
                            'status' => $class->status,
                            'role' => $class->role,
                            'assigned_at' => $class->assigned_at,
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_teachers' => Teacher::count(),
                'active_teachers' => Teacher::where('status', 'active')->count(),
                'inactive_teachers' => Teacher::where('status', 'inactive')->count(),
                'terminated_teachers' => Teacher::where('status', 'terminated')->count(),
                'teachers_with_subjects' => Teacher::whereHas('subjects')->count(),
                'teachers_without_subjects' => Teacher::whereDoesntHave('subjects')->count(),
                'average_experience' => round(Teacher::avg('experience_years'), 1),
                'total_subjects_assigned' => DB::table('subjects')->whereNotNull('teacher_id')->count(),
                'total_class_assignments' => DB::table('class_teacher')->count(),
            ];

            // Teachers by department
            $teachersByDepartment = Teacher::selectRaw('department, COUNT(*) as count')
                                         ->whereNotNull('department')
                                         ->groupBy('department')
                                         ->orderBy('department')
                                         ->get();

            // Teachers by designation
            $teachersByDesignation = Teacher::selectRaw('designation, COUNT(*) as count')
                                          ->whereNotNull('designation')
                                          ->groupBy('designation')
                                          ->orderBy('designation')
                                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Teacher statistics retrieved successfully',
                'data' => [
                    'statistics' => $stats,
                    'teachers_by_department' => $teachersByDepartment,
                    'teachers_by_designation' => $teachersByDesignation
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
     * Bulk create teachers.
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'teachers' => 'required|array|min:1|max:100',
                'teachers.*.name' => 'required|string|max:255',
                'teachers.*.email' => 'required|email|max:255|unique:teachers,email',
                'teachers.*.phone' => 'required|string|max:20',
                'teachers.*.joining_date' => 'required|date|before_or_equal:today',
                'teachers.*.department' => 'nullable|string|max:255',
                'teachers.*.designation' => 'nullable|string|max:255',
                'teachers.*.status' => 'in:active,inactive,terminated',
            ]);

            DB::beginTransaction();

            $teachers = [];
            $errors = [];

            foreach ($request->teachers as $index => $teacherData) {
                try {
                    // Generate employee code
                    $teacherData['employee_code'] = Teacher::generateEmployeeCode();
                    $teacherData['created_by'] = auth()->id();

                    $teacher = Teacher::create($teacherData);
                    $teachers[] = new TeacherResource($teacher);
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'data' => $teacherData,
                        'error' => $e->getMessage()
                    ];
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk creation failed',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teachers created successfully',
                'data' => $teachers,
                'count' => count($teachers)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
