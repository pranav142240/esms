<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Superadmin\Models\Superadmin;

class SchoolInquiry extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_name',
        'school_email',
        'school_phone',
        'school_address',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
        'proposed_domain',
        'school_tagline',
        'form_data',
        'status',
        'notes',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'converted_school_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the superadmin who reviewed this inquiry.
     */
    public function reviewedBy()
    {
        return $this->belongsTo(Superadmin::class, 'reviewed_by');
    }

    /**
     * Get the school created from this inquiry.
     */
    public function convertedSchool()
    {
        return $this->belongsTo(School::class, 'converted_school_id');
    }

    /**
     * Scope for pending inquiries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved inquiries.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected inquiries.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Mark inquiry as approved.
     */
    public function approve(int $reviewedBy, string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'notes' => $notes,
        ]);
    }

    /**
     * Mark inquiry as rejected.
     */
    public function reject(int $reviewedBy, string $reason, string $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'rejection_reason' => $reason,
            'notes' => $notes,
        ]);
    }
}
