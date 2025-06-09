<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StudentRequest;
use App\Http\Resources\Tenant\StudentResource;
use App\Models\Models\Tenant\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Student::with(['user', 'class', 'section', 'session']);

            // Apply filters
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            if ($request->filled('session_id')) {
                $query->where('session_id', $request->session_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('student_code', 'like', "%{$search}%")
                  ->orWhere('roll_number', 'like', "%{$search}%");
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $students = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Students retrieved successfully',
                'data' => StudentResource::collection($students->items()),
                'meta' => [
                    'pagination' => [
                        'total' => $students->total(),
                        'count' => $students->count(),
                        'per_page' => $students->perPage(),
                        'current_page' => $students->currentPage(),
                        'total_pages' => $students->lastPage(),
                        'has_more_pages' => $students->hasMorePages()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create user account for student
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password ?? 'student123'),
                'phone' => $request->phone,
                'role' => 'student'
            ]);

            // Handle profile photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_student_' . $user->id . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('students/photos', $photoName, 'public');
                $user->update(['photo' => $photoPath]);
            }

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'student_code' => $request->student_code ?? Student::generateStudentCode(),
                'roll_number' => $request->roll_number,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'session_id' => $request->session_id,
                'admission_date' => $request->admission_date,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'phone' => $request->phone,
                'address' => $request->address,
                'parent_phone' => $request->parent_phone,
                'emergency_contact' => $request->emergency_contact,
                'status' => $request->status ?? 'active',
                'notes' => $request->notes
            ]);

            // Assign student role
            $user->assignRole('student');

            DB::commit();

            $student->load(['user', 'class', 'section', 'session']);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => new StudentResource($student)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $student = Student::with(['user', 'class', 'section', 'session', 'parents', 'attendances', 'gradebooks', 'fees'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Student retrieved successfully',
                'data' => new StudentResource($student)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $student = Student::with('user')->findOrFail($id);

            // Update user information
            $student->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone
            ]);

            // Handle profile photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($student->user->photo) {
                    Storage::disk('public')->delete($student->user->photo);
                }

                $photo = $request->file('photo');
                $photoName = time() . '_student_' . $student->user->id . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('students/photos', $photoName, 'public');
                $student->user->update(['photo' => $photoPath]);
            }

            // Update password if provided
            if ($request->filled('password')) {
                $student->user->update(['password' => Hash::make($request->password)]);
            }

            // Update student information
            $student->update([
                'roll_number' => $request->roll_number,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'session_id' => $request->session_id,
                'admission_date' => $request->admission_date,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'phone' => $request->phone,
                'address' => $request->address,
                'parent_phone' => $request->parent_phone,
                'emergency_contact' => $request->emergency_contact,
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            DB::commit();

            $student->load(['user', 'class', 'section', 'session']);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => new StudentResource($student)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $student = Student::with('user')->findOrFail($id);
            
            // Soft delete the student record
            $student->delete();
            
            // Also soft delete the user account
            $student->user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate student ID card.
     */
    public function generateIdCard(string $id): JsonResponse
    {
        try {
            $student = Student::with(['user', 'class', 'section'])->findOrFail($id);

            // Here you would implement ID card generation logic
            // For now, return the student data that would be used for ID card
            
            return response()->json([
                'success' => true,
                'message' => 'Student ID card data retrieved successfully',
                'data' => [
                    'student' => new StudentResource($student),
                    'id_card_url' => null // This would be generated PDF/image URL
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate student ID card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create students from CSV.
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:academic_sessions,id'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->path()));
            $header = array_shift($csvData);

            $created = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                try {
                    $data = array_combine($header, $row);
                    
                    // Create user
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password'] ?? 'student123'),
                        'phone' => $data['phone'] ?? null,
                        'role' => 'student'
                    ]);

                    // Create student
                    Student::create([
                        'user_id' => $user->id,
                        'student_code' => $data['student_code'] ?? Student::generateStudentCode(),
                        'roll_number' => $data['roll_number'] ?? null,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'session_id' => $request->session_id,
                        'admission_date' => $data['admission_date'] ?? now(),
                        'date_of_birth' => $data['date_of_birth'] ?? null,
                        'gender' => $data['gender'] ?? null,
                        'blood_group' => $data['blood_group'] ?? null,
                        'religion' => $data['religion'] ?? null,
                        'phone' => $data['phone'] ?? null,
                        'address' => $data['address'] ?? null,
                        'parent_phone' => $data['parent_phone'] ?? null,
                        'emergency_contact' => $data['emergency_contact'] ?? null,
                        'status' => 'active'
                    ]);

                    $user->assignRole('student');
                    $created++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$created} students",
                'data' => [
                    'created_count' => $created,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk create students',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
