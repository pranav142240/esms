<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BookIssueRequest;
use App\Http\Resources\Tenant\BookIssueResource;
use App\Models\Tenant\BookIssue;
use App\Models\Tenant\Book;
use App\Models\Tenant\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookIssueController extends Controller
{
    /**
     * Display a listing of book issues.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BookIssue::with(['book', 'student', 'user', 'issuedBy', 'returnedTo']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = [
            'id', 'issue_date', 'due_date', 'return_date', 'status', 
            'fine_amount', 'created_at', 'updated_at'
        ];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $issues = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Book issues retrieved successfully',
            'data' => BookIssueResource::collection($issues),
            'meta' => [
                'current_page' => $issues->currentPage(),
                'last_page' => $issues->lastPage(),
                'per_page' => $issues->perPage(),
                'total' => $issues->total(),
                'filters_applied' => $this->getAppliedFilters($request),
                'sort' => ['by' => $sortBy, 'order' => $sortOrder]
            ]
        ]);
    }

    /**
     * Store a newly created book issue.
     */
    public function store(BookIssueRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Get the book and validate availability
            $book = Book::findOrFail($request->book_id);
            
            if (!$book->isAvailable()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Book is not available for issue',
                    'errors' => ['book_id' => ['This book is currently not available']]
                ], 422);
            }

            // Create the book issue
            $issue = BookIssue::create($request->validated());

            // Update book availability
            $book->issueCopy();

            DB::commit();

            $issue->load(['book', 'student', 'user', 'issuedBy']);

            return response()->json([
                'status' => 'success',
                'message' => 'Book issued successfully',
                'data' => new BookIssueResource($issue)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to issue book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified book issue.
     */
    public function show(BookIssue $bookIssue): JsonResponse
    {
        $bookIssue->load(['book', 'student', 'user', 'issuedBy', 'returnedTo']);

        return response()->json([
            'status' => 'success',
            'message' => 'Book issue retrieved successfully',
            'data' => new BookIssueResource($bookIssue)
        ]);
    }

    /**
     * Update the specified book issue.
     */
    public function update(BookIssueRequest $request, BookIssue $bookIssue): JsonResponse
    {
        $bookIssue->update($request->validated());
        $bookIssue->load(['book', 'student', 'user', 'issuedBy', 'returnedTo']);

        return response()->json([
            'status' => 'success',
            'message' => 'Book issue updated successfully',
            'data' => new BookIssueResource($bookIssue)
        ]);
    }

    /**
     * Remove the specified book issue.
     */
    public function destroy(BookIssue $bookIssue): JsonResponse
    {
        if ($bookIssue->status === 'issued') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete an active book issue. Please return the book first.'
            ], 422);
        }

        $bookIssue->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Book issue deleted successfully'
        ]);
    }

    /**
     * Return a book.
     */
    public function returnBook(Request $request, BookIssue $bookIssue): JsonResponse
    {
        if ($bookIssue->status !== 'issued') {
            return response()->json([
                'status' => 'error',
                'message' => 'This book is not currently issued or has already been returned'
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $returnDate = $request->get('return_date', Carbon::now()->toDateString());
            $notes = $request->get('notes');
            $fineAmount = $request->get('fine_amount', 0);

            // Return the book
            $bookIssue->returnBook($returnDate, auth()->id(), $notes, $fineAmount);

            DB::commit();

            $bookIssue->load(['book', 'student', 'user', 'issuedBy', 'returnedTo']);

            return response()->json([
                'status' => 'success',
                'message' => 'Book returned successfully',
                'data' => new BookIssueResource($bookIssue)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to return book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renew a book issue.
     */
    public function renewBook(Request $request, BookIssue $bookIssue): JsonResponse
    {
        if ($bookIssue->status !== 'issued') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only issued books can be renewed'
            ], 422);
        }

        $maxRenewals = config('library.max_renewals', 2);
        if ($bookIssue->renewals >= $maxRenewals) {
            return response()->json([
                'status' => 'error',
                'message' => "This book has already been renewed the maximum number of times ({$maxRenewals})"
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $newDueDate = $request->get('due_date', Carbon::now()->addDays(14)->toDateString());
            
            // Renew the book
            $bookIssue->renew($newDueDate);

            DB::commit();

            $bookIssue->load(['book', 'student', 'user', 'issuedBy']);

            return response()->json([
                'status' => 'success',
                'message' => 'Book renewed successfully',
                'data' => new BookIssueResource($bookIssue)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to renew book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a book as lost.
     */
    public function markAsLost(Request $request, BookIssue $bookIssue): JsonResponse
    {
        $request->validate([
            'fine_amount' => 'required|numeric|min:0',
            'notes' => 'required|string|max:500'
        ]);

        if ($bookIssue->status !== 'issued') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only issued books can be marked as lost'
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            // Mark as lost
            $bookIssue->markAsLost($request->fine_amount, $request->notes);

            DB::commit();

            $bookIssue->load(['book', 'student', 'user', 'issuedBy']);

            return response()->json([
                'status' => 'success',
                'message' => 'Book marked as lost successfully',
                'data' => new BookIssueResource($bookIssue)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark book as lost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue books.
     */
    public function overdue(Request $request): JsonResponse
    {
        $query = BookIssue::with(['book', 'student', 'user', 'issuedBy'])
            ->where('status', 'issued')
            ->where('due_date', '<', Carbon::now());

        // Apply additional filters
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('days_overdue')) {
            $daysOverdue = (int) $request->days_overdue;
            $query->where('due_date', '<', Carbon::now()->subDays($daysOverdue));
        }

        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($request->get('per_page', 15), 100);
        $issues = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Overdue books retrieved successfully',
            'data' => BookIssueResource::collection($issues),
            'meta' => [
                'current_page' => $issues->currentPage(),
                'last_page' => $issues->lastPage(),
                'per_page' => $issues->perPage(),
                'total' => $issues->total(),
                'total_overdue' => $issues->total()
            ]
        ]);
    }

    /**
     * Get issues by student.
     */
    public function byStudent(Request $request, Student $student): JsonResponse
    {
        $query = BookIssue::with(['book', 'user', 'issuedBy', 'returnedTo'])
            ->where('student_id', $student->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'issue_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($request->get('per_page', 15), 100);
        $issues = $query->paginate($perPage);

        $stats = [
            'total_issued' => BookIssue::where('student_id', $student->id)->count(),
            'currently_issued' => BookIssue::where('student_id', $student->id)->where('status', 'issued')->count(),
            'overdue' => BookIssue::where('student_id', $student->id)
                ->where('status', 'issued')
                ->where('due_date', '<', Carbon::now())
                ->count(),
            'total_fines' => BookIssue::where('student_id', $student->id)->sum('fine_amount'),
            'unpaid_fines' => BookIssue::where('student_id', $student->id)
                ->where('fine_paid', false)
                ->sum('fine_amount')
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Student book issues retrieved successfully',
            'data' => BookIssueResource::collection($issues),
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_id' => $student->student_id
            ],
            'statistics' => $stats,
            'meta' => [
                'current_page' => $issues->currentPage(),
                'last_page' => $issues->lastPage(),
                'per_page' => $issues->perPage(),
                'total' => $issues->total()
            ]
        ]);
    }

    /**
     * Get book issue statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $startDate = $this->getStartDate($period);

        $stats = [
            'total_issues' => BookIssue::count(),
            'active_issues' => BookIssue::where('status', 'issued')->count(),
            'overdue_issues' => BookIssue::where('status', 'issued')
                ->where('due_date', '<', Carbon::now())
                ->count(),
            'returned_books' => BookIssue::where('status', 'returned')->count(),
            'lost_books' => BookIssue::where('status', 'lost')->count(),
            'total_fines' => BookIssue::sum('fine_amount'),
            'unpaid_fines' => BookIssue::where('fine_paid', false)->sum('fine_amount'),
            'period_stats' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'new_issues' => BookIssue::where('created_at', '>=', $startDate)->count(),
                'returns' => BookIssue::where('return_date', '>=', $startDate)->count(),
                'renewals' => BookIssue::where('updated_at', '>=', $startDate)
                    ->where('renewals', '>', 0)
                    ->count()
            ]
        ];

        // Most issued books
        $mostIssued = DB::table('book_issues')
            ->join('books', 'book_issues.book_id', '=', 'books.id')
            ->select('books.title', 'books.author', DB::raw('COUNT(*) as issue_count'))
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('issue_count')
            ->limit(10)
            ->get();

        // Most active students
        $mostActiveStudents = DB::table('book_issues')
            ->join('students', 'book_issues.student_id', '=', 'students.id')
            ->select('students.name', 'students.student_id', DB::raw('COUNT(*) as issue_count'))
            ->groupBy('students.id', 'students.name', 'students.student_id')
            ->orderByDesc('issue_count')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Book issue statistics retrieved successfully',
            'data' => [
                'overview' => $stats,
                'most_issued_books' => $mostIssued,
                'most_active_students' => $mostActiveStudents
            ]
        ]);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Student filter
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Book filter
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Date range filters
        if ($request->has('issue_date_from')) {
            $query->where('issue_date', '>=', $request->issue_date_from);
        }

        if ($request->has('issue_date_to')) {
            $query->where('issue_date', '<=', $request->issue_date_to);
        }

        if ($request->has('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }

        if ($request->has('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        // Overdue filter
        if ($request->boolean('overdue_only')) {
            $query->where('status', 'issued')
                  ->where('due_date', '<', Carbon::now());
        }

        // Fine filters
        if ($request->has('has_fine')) {
            if ($request->boolean('has_fine')) {
                $query->where('fine_amount', '>', 0);
            } else {
                $query->where('fine_amount', '=', 0);
            }
        }

        if ($request->has('fine_paid')) {
            $query->where('fine_paid', $request->boolean('fine_paid'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('book', function ($bookQuery) use ($search) {
                    $bookQuery->where('title', 'like', "%{$search}%")
                             ->orWhere('author', 'like', "%{$search}%")
                             ->orWhere('isbn', 'like', "%{$search}%");
                })
                ->orWhereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('student_id', 'like', "%{$search}%");
                })
                ->orWhere('notes', 'like', "%{$search}%");
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
            'status', 'student_id', 'book_id', 'issue_date_from', 'issue_date_to',
            'due_date_from', 'due_date_to', 'overdue_only', 'has_fine', 'fine_paid', 'search'
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
}
