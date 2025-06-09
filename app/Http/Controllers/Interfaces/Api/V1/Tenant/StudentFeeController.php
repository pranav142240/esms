<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\StudentFeeResource;
use App\Models\Models\Tenant\StudentFeeManager;
use App\Models\Models\Tenant\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StudentFeeController extends Controller
{
    /**
     * Display a listing of student fees.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StudentFeeManager::with(['student.user', 'student.class']);

            // Apply filters
            if ($request->filled('class_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('fee_type')) {
                $query->where('fee_type', $request->fee_type);
            }

            if ($request->filled('date_from')) {
                $query->where('due_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('due_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('student.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('invoice_number', 'like', "%{$search}%");
            }

            $perPage = $request->get('per_page', 15);
            $fees = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Student fees retrieved successfully',
                'data' => StudentFeeResource::collection($fees->items()),
                'meta' => [
                    'pagination' => [
                        'total' => $fees->total(),
                        'count' => $fees->count(),
                        'per_page' => $fees->perPage(),
                        'current_page' => $fees->currentPage(),
                        'total_pages' => $fees->lastPage(),
                        'has_more_pages' => $fees->hasMorePages()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student fees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created fee record.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'late_fee_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $fee = StudentFeeManager::create([
                'student_id' => $request->student_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'fee_type' => $request->fee_type,
                'amount' => $request->amount,
                'late_fee_amount' => $request->late_fee_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => $request->amount + ($request->late_fee_amount ?? 0) - ($request->discount_amount ?? 0),
                'due_date' => $request->due_date,
                'status' => 'pending',
                'description' => $request->description,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            $fee->load(['student.user', 'student.class']);

            return response()->json([
                'success' => true,
                'message' => 'Student fee created successfully',
                'data' => new StudentFeeResource($fee)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified fee record.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $fee = StudentFeeManager::with(['student.user', 'student.class', 'payments'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Student fee retrieved successfully',
                'data' => new StudentFeeResource($fee)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student fee not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified fee record.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'fee_type' => 'sometimes|required|string|max:100',
            'amount' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:500',
            'late_fee_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|required|in:pending,paid,partial,overdue,cancelled'
        ]);

        try {
            $fee = StudentFeeManager::findOrFail($id);

            // Prevent updating paid fees
            if ($fee->status === 'paid' && $request->filled('amount')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify amount for paid fees'
                ], 422);
            }

            $updateData = $request->only([
                'fee_type', 'amount', 'due_date', 'description', 
                'late_fee_amount', 'discount_amount', 'status'
            ]);

            // Recalculate total amount if relevant fields are updated
            if ($request->has(['amount', 'late_fee_amount', 'discount_amount'])) {
                $amount = $request->get('amount', $fee->amount);
                $lateFee = $request->get('late_fee_amount', $fee->late_fee_amount);
                $discount = $request->get('discount_amount', $fee->discount_amount);
                
                $updateData['total_amount'] = $amount + $lateFee - $discount;
            }

            $fee->update($updateData);
            $fee->load(['student.user', 'student.class']);

            return response()->json([
                'success' => true,
                'message' => 'Student fee updated successfully',
                'data' => new StudentFeeResource($fee)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified fee record (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $fee = StudentFeeManager::findOrFail($id);
            
            // Prevent deleting paid fees
            if ($fee->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete paid fees'
                ], 422);
            }

            $fee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student fee deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bulk invoices for a class.
     */
    public function generateBulkInvoices(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'fee_type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $students = Student::where('class_id', $request->class_id)
                ->where('status', 'active')
                ->get();

            $created = 0;
            $errors = [];

            foreach ($students as $student) {
                try {
                    StudentFeeManager::create([
                        'student_id' => $student->id,
                        'invoice_number' => $this->generateInvoiceNumber(),
                        'fee_type' => $request->fee_type,
                        'amount' => $request->amount,
                        'total_amount' => $request->amount,
                        'due_date' => $request->due_date,
                        'status' => 'pending',
                        'description' => $request->description,
                        'created_by' => auth()->id()
                    ]);

                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Student {$student->user->name}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$created} fee records",
                'data' => [
                    'created_count' => $created,
                    'total_students' => $students->count(),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate bulk invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export fee reports.
     */
    public function exportReport(Request $request): JsonResponse
    {
        try {
            $query = StudentFeeManager::with(['student.user', 'student.class']);

            // Apply same filters as index method
            if ($request->filled('class_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('due_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('due_date', '<=', $request->date_to);
            }

            $fees = $query->get();

            // Generate report data
            $reportData = [
                'total_fees' => $fees->sum('total_amount'),
                'paid_fees' => $fees->where('status', 'paid')->sum('total_amount'),
                'pending_fees' => $fees->where('status', 'pending')->sum('total_amount'),
                'overdue_fees' => $fees->where('status', 'overdue')->sum('total_amount'),
                'records' => StudentFeeResource::collection($fees)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Fee report generated successfully',
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate fee report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = StudentFeeManager::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest('id')
            ->first();
        
        $nextNumber = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -4) + 1 : 1;
        
        return 'INV' . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
