<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class StudentFeeManager extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'invoice_number',
        'fee_type',
        'amount',
        'late_fee_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_date',
        'paid_date',
        'status',
        'description',
        'payment_method',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the student that owns the fee.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who created the fee record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the payments for this fee.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'fee_id');
    }

    /**
     * Calculate the remaining balance.
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if the fee is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fee) {
            if (empty($fee->paid_amount)) {
                $fee->paid_amount = 0;
            }
        });

        static::updating(function ($fee) {
            // Auto-update status based on payment
            if ($fee->paid_amount >= $fee->total_amount) {
                $fee->status = 'paid';
                if (!$fee->paid_date) {
                    $fee->paid_date = now();
                }
            } elseif ($fee->paid_amount > 0) {
                $fee->status = 'partial';
            } elseif ($fee->due_date < now() && $fee->paid_amount == 0) {
                $fee->status = 'overdue';
            }
        });
    }
}
