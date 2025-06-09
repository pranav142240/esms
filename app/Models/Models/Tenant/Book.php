<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'isbn',
        'author',
        'publisher',
        'publication_year',
        'category',
        'subject',
        'description',
        'total_copies',
        'available_copies',
        'language',
        'edition',
        'pages',
        'price',
        'location',
        'status',
        'created_by'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'publication_year' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'pages' => 'integer',
        'price' => 'decimal:2'
    ];

    /**
     * Get the user who created the book record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the book issues for this book.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'book_id');
    }

    /**
     * Get active book issues (currently issued).
     */
    public function activeIssues(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'book_id')->where('status', 'issued');
    }

    /**
     * Check if book is available for issue.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->available_copies > 0;
    }

    /**
     * Issue a copy of the book.
     */
    public function issueCopy(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->decrement('available_copies');
        
        if ($this->available_copies === 0) {
            $this->update(['status' => 'issued']);
        }

        return true;
    }

    /**
     * Return a copy of the book.
     */
    public function returnCopy(): bool
    {
        $this->increment('available_copies');
        
        if ($this->status === 'issued' && $this->available_copies > 0) {
            $this->update(['status' => 'available']);
        }

        return true;
    }

    /**
     * Generate unique book code.
     */
    public static function generateBookCode(): string
    {
        $year = date('Y');
        $lastBook = static::withTrashed()
                         ->where('isbn', 'like', "BK-{$year}-%")
                         ->orderBy('isbn', 'desc')
                         ->first();
        
        if ($lastBook) {
            $lastNumber = (int) substr($lastBook->isbn, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "BK-{$year}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for available books.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('available_copies', '>', 0);
    }

    /**
     * Scope for books by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for books by author.
     */
    public function scopeByAuthor($query, $author)
    {
        return $query->where('author', 'like', "%{$author}%");
    }
}
