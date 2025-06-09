<?php

namespace App\Http\Controllers\Interfaces\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BookRequest;
use App\Http\Resources\Tenant\BookResource;
use App\Models\Models\Tenant\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $category = $request->input('category');
            $status = $request->input('status');
            $author = $request->input('author');

            $query = Book::with(['createdBy']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('publisher', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            }

            // Apply category filter
            if ($category) {
                $query->where('category', $category);
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply author filter
            if ($author) {
                $query->where('author', 'like', "%{$author}%");
            }

            $books = $query->withCount(['issues'])
                          ->orderBy('title')
                          ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Books retrieved successfully',
                'data' => BookResource::collection($books->items()),
                'meta' => [
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                    'per_page' => $books->perPage(),
                    'total' => $books->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve books',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created book.
     */
    public function store(BookRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['created_by'] = auth()->id();

            $book = Book::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book created successfully',
                'data' => new BookResource($book->load('createdBy'))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified book.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $book = Book::with(['createdBy', 'issues'])
                       ->withCount(['issues'])
                       ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Book retrieved successfully',
                'data' => new BookResource($book)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified book.
     */
    public function update(BookRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $book = Book::findOrFail($id);
            $validated = $request->validated();

            $book->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully',
                'data' => new BookResource($book->load('createdBy'))
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified book (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $book = Book::findOrFail($id);
            
            // Check if book has active issues
            if ($book->issues()->where('status', 'issued')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete book with active issues'
                ], 400);
            }

            $book->delete();

            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available books for issue.
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $category = $request->input('category');

            $query = Book::where('status', 'available')
                       ->where('available_copies', '>', 0);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            }

            if ($category) {
                $query->where('category', $category);
            }

            $books = $query->with('createdBy')
                          ->orderBy('title')
                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Available books retrieved successfully',
                'data' => BookResource::collection($books)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available books',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get book categories.
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Book::selectRaw('category, COUNT(*) as count')
                            ->whereNotNull('category')
                            ->groupBy('category')
                            ->orderBy('category')
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Book categories retrieved successfully',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get book statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_books' => Book::count(),
                'available_books' => Book::where('status', 'available')->count(),
                'issued_books' => Book::where('status', 'issued')->count(),
                'lost_books' => Book::where('status', 'lost')->count(),
                'damaged_books' => Book::where('status', 'damaged')->count(),
                'total_copies' => Book::sum('total_copies'),
                'available_copies' => Book::sum('available_copies'),
                'total_issues' => DB::table('book_issues')->count(),
                'active_issues' => DB::table('book_issues')->where('status', 'issued')->count(),
            ];

            // Books by category
            $booksByCategory = Book::selectRaw('category, COUNT(*) as count, SUM(total_copies) as total_copies')
                                 ->whereNotNull('category')
                                 ->groupBy('category')
                                 ->orderBy('category')
                                 ->get();

            return response()->json([
                'success' => true,
                'message' => 'Book statistics retrieved successfully',
                'data' => [
                    'statistics' => $stats,
                    'books_by_category' => $booksByCategory
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
     * Bulk create books.
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'books' => 'required|array|min:1|max:100',
                'books.*.title' => 'required|string|max:255',
                'books.*.isbn' => 'required|string|max:20|unique:books,isbn',
                'books.*.author' => 'required|string|max:255',
                'books.*.total_copies' => 'required|integer|min:1',
                'books.*.category' => 'nullable|string|max:100',
                'books.*.publisher' => 'nullable|string|max:255',
                'books.*.publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            DB::beginTransaction();

            $books = [];
            $errors = [];

            foreach ($request->books as $index => $bookData) {
                try {
                    $bookData['available_copies'] = $bookData['total_copies'];
                    $bookData['created_by'] = auth()->id();

                    $book = Book::create($bookData);
                    $books[] = new BookResource($book);
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'data' => $bookData,
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
                'message' => 'Books created successfully',
                'data' => $books,
                'count' => count($books)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create books',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
