<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\AttendanceResource;
use App\Http\Requests\Tenant\AttendanceRequest;
use App\Models\Models\Tenant\DailyAttendances;
use App\Models\Models\Tenant\Student;
use App\Models\Models\Tenant\Classes;
use App\Models\Models\Tenant\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');
            $studentId = $request->input('student_id');
            $date = $request->input('date');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = DailyAttendances::with(['student', 'class', 'subject', 'markedBy']);

            // Apply search filter
            if ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('roll_number', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply class filter
            if ($classId) {
                $query->where('class_id', $classId);
            }

            // Apply subject filter
            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            // Apply student filter
            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            // Apply date filter
            if ($date) {
                $query->whereDate('attendance_date', $date);
            }

            // Apply date range filter
            if ($startDate && $endDate) {
                $query->whereBetween('attendance_date', [$startDate, $endDate]);
            }

            $attendances = $query->orderBy('attendance_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Attendance records retrieved successfully',
                'data' => AttendanceResource::collection($attendances->items()),
                'meta' => [
                    'current_page' => $attendances->currentPage(),
                    'last_page' => $attendances->lastPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(AttendanceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['marked_by'] = Auth::id();
            $validated['marked_at'] = now();

            // Check if attendance already exists for this student, class, subject, and date
            $existingAttendance = DailyAttendances::where([
                'student_id' => $validated['student_id'],
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'attendance_date' => $validated['attendance_date']
            ])->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already marked for this student on this date'
                ], 422);
            }

            $attendance = DailyAttendances::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully',
                'data' => new AttendanceResource($attendance->load(['student', 'class', 'subject', 'markedBy']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified attendance record.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $attendance = DailyAttendances::with(['student', 'class', 'subject', 'markedBy'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Attendance record retrieved successfully',
                'data' => new AttendanceResource($attendance)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified attendance record.
     */
    public function update(AttendanceRequest $request, string $id): JsonResponse
    {
        try {
            $attendance = DailyAttendances::findOrFail($id);
            
            $validated = $request->validated();
            $validated['marked_by'] = Auth::id();
            $validated['marked_at'] = now();

            $attendance->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully',
                'data' => new AttendanceResource($attendance->fresh(['student', 'class', 'subject', 'markedBy']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $attendance = DailyAttendances::findOrFail($id);
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk mark attendance for multiple students.
     */
    public function bulkMark(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'nullable|exists:subjects,id',
                'attendance_date' => 'required|date',
                'attendances' => 'required|array|min:1',
                'attendances.*.student_id' => 'required|exists:students,id',
                'attendances.*.status' => 'required|in:present,absent,late,excused',
                'attendances.*.remarks' => 'nullable|string|max:255',
            ]);

            $results = [];
            $errors = [];

            DB::transaction(function () use ($request, &$results, &$errors) {
                foreach ($request->attendances as $attendanceData) {
                    try {
                        // Check if attendance already exists
                        $existingAttendance = DailyAttendances::where([
                            'student_id' => $attendanceData['student_id'],
                            'class_id' => $request->class_id,
                            'subject_id' => $request->subject_id,
                            'attendance_date' => $request->attendance_date
                        ])->first();

                        if ($existingAttendance) {
                            // Update existing attendance
                            $existingAttendance->update([
                                'status' => $attendanceData['status'],
                                'remarks' => $attendanceData['remarks'] ?? null,
                                'marked_by' => Auth::id(),
                                'marked_at' => now()
                            ]);
                            $results[] = "Updated attendance for student ID: {$attendanceData['student_id']}";
                        } else {
                            // Create new attendance
                            DailyAttendances::create([
                                'student_id' => $attendanceData['student_id'],
                                'class_id' => $request->class_id,
                                'subject_id' => $request->subject_id,
                                'attendance_date' => $request->attendance_date,
                                'status' => $attendanceData['status'],
                                'remarks' => $attendanceData['remarks'] ?? null,
                                'marked_by' => Auth::id(),
                                'marked_at' => now()
                            ]);
                            $results[] = "Marked attendance for student ID: {$attendanceData['student_id']}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Failed to mark attendance for student ID: {$attendanceData['student_id']} - {$e->getMessage()}";
                    }
                }
            });

            return response()->json([
                'success' => count($errors) === 0,
                'message' => 'Bulk attendance marking completed',
                'data' => [
                    'processed' => count($results),
                    'failed' => count($errors),
                    'results' => $results,
                    'errors' => $errors
                ]
            ], count($errors) === 0 ? 200 : 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk mark attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance by class.
     */
    public function byClass(string $classId, Request $request): JsonResponse
    {
        try {
            $date = $request->input('date', today()->format('Y-m-d'));
            $subjectId = $request->input('subject_id');

            $query = DailyAttendances::with(['student', 'subject', 'markedBy'])
                                   ->where('class_id', $classId)
                                   ->whereDate('attendance_date', $date);

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $attendances = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Class attendance retrieved successfully',
                'data' => AttendanceResource::collection($attendances)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance by student.
     */
    public function byStudent(string $studentId, Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');

            $query = DailyAttendances::with(['class', 'subject', 'markedBy'])
                                   ->where('student_id', $studentId)
                                   ->whereBetween('attendance_date', [$startDate, $endDate]);

            if ($classId) {
                $query->where('class_id', $classId);
            }

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $attendances = $query->orderBy('attendance_date', 'desc')->get();

            // Calculate attendance statistics
            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $late = $attendances->where('status', 'late')->count();
            $excused = $attendances->where('status', 'excused')->count();

            $attendancePercentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'message' => 'Student attendance retrieved successfully',
                'data' => [
                    'attendances' => AttendanceResource::collection($attendances),
                    'statistics' => [
                        'total_days' => $total,
                        'present_days' => $present,
                        'absent_days' => $absent,
                        'late_days' => $late,
                        'excused_days' => $excused,
                        'attendance_percentage' => $attendancePercentage
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance reports.
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');

            $query = DailyAttendances::with(['student', 'class', 'subject'])
                                   ->whereBetween('attendance_date', [$startDate, $endDate]);

            if ($classId) {
                $query->where('class_id', $classId);
            }

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $attendances = $query->get();

            // Group by student and calculate statistics
            $studentStats = $attendances->groupBy('student_id')->map(function ($studentAttendances) {
                $total = $studentAttendances->count();
                $present = $studentAttendances->where('status', 'present')->count();
                $absent = $studentAttendances->where('status', 'absent')->count();
                $late = $studentAttendances->where('status', 'late')->count();
                $excused = $studentAttendances->where('status', 'excused')->count();
                
                $attendancePercentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
                
                $student = $studentAttendances->first()->student;
                
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_roll' => $student->roll_number,
                    'total_days' => $total,
                    'present_days' => $present,
                    'absent_days' => $absent,
                    'late_days' => $late,
                    'excused_days' => $excused,
                    'attendance_percentage' => $attendancePercentage
                ];
            })->values();

            // Overall statistics
            $totalRecords = $attendances->count();
            $overallStats = [
                'total_records' => $totalRecords,
                'present_count' => $attendances->where('status', 'present')->count(),
                'absent_count' => $attendances->where('status', 'absent')->count(),
                'late_count' => $attendances->where('status', 'late')->count(),
                'excused_count' => $attendances->where('status', 'excused')->count(),
                'overall_attendance_percentage' => $totalRecords > 0 ? 
                    round(($attendances->where('status', 'present')->count() / $totalRecords) * 100, 2) : 0
            ];

            return response()->json([
                'success' => true,
                'message' => 'Attendance reports generated successfully',
                'data' => [
                    'student_statistics' => $studentStats,
                    'overall_statistics' => $overallStats,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate attendance reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $date = $request->input('date', today()->format('Y-m-d'));
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');

            $query = DailyAttendances::whereDate('attendance_date', $date);

            if ($classId) {
                $query->where('class_id', $classId);
            }

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $attendances = $query->get();

            $stats = [
                'date' => $date,
                'total_marked' => $attendances->count(),
                'present_count' => $attendances->where('status', 'present')->count(),
                'absent_count' => $attendances->where('status', 'absent')->count(),
                'late_count' => $attendances->where('status', 'late')->count(),
                'excused_count' => $attendances->where('status', 'excused')->count(),
                'attendance_percentage' => $attendances->count() > 0 ? 
                    round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 2) : 0,
                
                // Additional statistics
                'this_week_total' => DailyAttendances::whereBetween('attendance_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                
                'this_month_total' => DailyAttendances::whereMonth('attendance_date', now()->month)
                                                    ->whereYear('attendance_date', now()->year)
                                                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Attendance statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
