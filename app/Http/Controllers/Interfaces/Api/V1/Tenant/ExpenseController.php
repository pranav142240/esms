<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\ExpenseResource;
use App\Http\Requests\Tenant\ExpenseRequest;
use App\Models\Models\Tenant\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Expense::with(['createdBy', 'approvedBy']);

            // Apply filters
            if ($request->filled('status')) {
                $query->status($request->status);
            }

            if ($request->filled('category')) {
                $query->category($request->category);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->dateRange($request->date_from, $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('expense_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'expense_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $expenses = $query->paginate($perPage);

            // Calculate totals
            $totals = [
                'total_amount' => $query->sum('amount'),
                'pending_amount' => Expense::status('pending')->sum('amount'),
                'approved_amount' => Expense::status('approved')->sum('amount'),
                'paid_amount' => Expense::status('paid')->sum('amount'),
            ];            return response()->json([
                'success' => true,
                'message' => 'Expenses retrieved successfully',
                'data' => ExpenseResource::collection($expenses->items()),
                'totals' => $totals,
                'meta' => [
                    'pagination' => [
                        'total' => $expenses->total(),
                        'per_page' => $expenses->perPage(),
                        'current_page' => $expenses->currentPage(),
                        'last_page' => $expenses->lastPage(),
                        'from' => $expenses->firstItem(),
                        'to' => $expenses->lastItem()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:100',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $expenseData = $request->except(['receipt_file']);
            $expenseData['created_by'] = auth()->id();

            // Handle file upload
            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('expenses/receipts', $filename, 'public');
                $expenseData['receipt_file'] = $path;
            }

            $expense = Expense::create($expenseData);

            $expense->load(['createdBy', 'approvedBy']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully',
                'data' => $expense
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense): JsonResponse
    {
        try {
            $expense->load(['createdBy', 'approvedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Expense retrieved successfully',
                'data' => $expense
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:100',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $expenseData = $request->except(['receipt_file']);

            // Handle file upload
            if ($request->hasFile('receipt_file')) {
                // Delete old file if exists
                if ($expense->receipt_file) {
                    Storage::disk('public')->delete($expense->receipt_file);
                }

                $file = $request->file('receipt_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('expenses/receipts', $filename, 'public');
                $expenseData['receipt_file'] = $path;
            }

            $expense->update($expenseData);
            $expense->load(['createdBy', 'approvedBy']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'data' => $expense
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        try {
            // Delete associated file
            if ($expense->receipt_file) {
                Storage::disk('public')->delete($expense->receipt_file);
            }

            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve an expense.
     */
    public function approve(Expense $expense): JsonResponse
    {
        try {
            $expense->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            $expense->load(['createdBy', 'approvedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Expense approved successfully',
                'data' => $expense
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an expense.
     */
    public function reject(Request $request, Expense $expense): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string'
        ]);

        try {
            $expense->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->notes
            ]);

            $expense->load(['createdBy', 'approvedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Expense rejected successfully',
                'data' => $expense
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(Expense $expense): JsonResponse
    {
        try {
            $expense->update([
                'status' => 'paid'
            ]);

            $expense->load(['createdBy', 'approvedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Expense marked as paid successfully',
                'data' => $expense
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark expense as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $currentMonth = now()->startOfMonth();
            $currentYear = now()->startOfYear();

            $stats = [
                'total_expenses' => Expense::count(),
                'pending_expenses' => Expense::status('pending')->count(),
                'approved_expenses' => Expense::status('approved')->count(),
                'paid_expenses' => Expense::status('paid')->count(),
                'rejected_expenses' => Expense::status('rejected')->count(),
                'monthly_total' => Expense::where('expense_date', '>=', $currentMonth)->sum('amount'),
                'yearly_total' => Expense::where('expense_date', '>=', $currentYear)->sum('amount'),
                'by_category' => Expense::selectRaw('category, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('category')
                    ->get(),
                'by_status' => Expense::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('status')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Expense statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense categories.
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Expense::distinct('category')
                ->pluck('category')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Expense categories retrieved successfully',
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
