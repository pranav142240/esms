<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class BookIssue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_id',
        'student_id',
        'user_id',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'fine_paid',
        'notes',
        'issued_by',
        'returned_to'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'boolean'
    ];

    /**
     * Get the book that was issued.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the student who borrowed the book.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the user who borrowed the book (if not a student).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who issued the book.
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the user who received the returned book.
     */
    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    /**
     * Check if the book is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'issued' && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    /**
     * Calculate the fine amount for overdue book.
     */
    public function calculateFine(float $dailyFineRate = 1.00): float
    {
        if (!$this->isOverdue()) {
            return 0.0;
        }

        $overdueDays = $this->due_date->diffInDays(now());
        return $overdueDays * $dailyFineRate;
    }

    /**
     * Return the book.
     */
    public function returnBook(int $returnedToUserId, string $notes = null): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        $this->update([
            'status' => 'returned',
            'return_date' => now(),
            'returned_to' => $returnedToUserId,
            'notes' => $notes,
            'fine_amount' => $this->calculateFine()
        ]);

        // Update book availability
        $this->book->returnCopy();

        return true;
    }

    /**
     * Renew the book issue.
     */
    public function renew(int $renewalDays = 7): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        $this->update([
            'due_date' => $this->due_date->addDays($renewalDays)
        ]);

        return true;
    }

    /**
     * Mark book as lost.
     */
    public function markAsLost(float $replacementCost = null): bool
    {
        $this->update([
            'status' => 'lost',
            'fine_amount' => $replacementCost ?? $this->book->price ?? 0
        ]);

        // Update book status
        $this->book->update(['status' => 'lost']);

        return true;
    }

    /**
     * Scope for active issues (currently issued).
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope for overdue issues.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'issued')
                    ->where('due_date', '<', now());
    }

    /**
     * Scope for returned issues.
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Scope for lost books.
     */
    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }
}
