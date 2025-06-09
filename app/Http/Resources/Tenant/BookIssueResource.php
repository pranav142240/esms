<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookIssueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'student_id' => $this->student_id,
            'user_id' => $this->user_id,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'return_date' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'fine_amount' => $this->fine_amount,
            'fine_paid' => $this->fine_paid,
            'notes' => $this->notes,
            
            // Status information
            'is_overdue' => $this->isOverdue(),
            'days_overdue' => $this->getDaysOverdue(),
            'days_until_due' => $this->getDaysUntilDue(),
            'calculated_fine' => $this->calculateFine(),
            
            // Book information
            'book' => $this->whenLoaded('book', function () {
                return [
                    'id' => $this->book->id,
                    'title' => $this->book->title,
                    'author' => $this->book->author,
                    'isbn' => $this->book->isbn,
                    'category' => $this->book->category
                ];
            }),
            
            // Student information
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->name,
                    'student_code' => $this->student->student_code,
                    'class' => $this->student->class->name ?? null,
                    'section' => $this->student->section->name ?? null
                ];
            }),
            
            // User information (for non-student borrowers)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            
            // Staff information
            'issued_by' => $this->whenLoaded('issuedBy', function () {
                return [
                    'id' => $this->issuedBy->id,
                    'name' => $this->issuedBy->name,
                    'email' => $this->issuedBy->email
                ];
            }),
            
            'returned_to' => $this->whenLoaded('returnedTo', function () {
                return [
                    'id' => $this->returnedTo->id,
                    'name' => $this->returnedTo->name,
                    'email' => $this->returnedTo->email
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Formatted dates
            'issue_date_formatted' => $this->issue_date?->format('M d, Y'),
            'due_date_formatted' => $this->due_date?->format('M d, Y'),
            'return_date_formatted' => $this->return_date?->format('M d, Y'),
            
            // Duration information
            'duration_info' => [
                'total_days' => $this->getTotalDays(),
                'remaining_days' => $this->getRemainingDays(),
                'usage_percentage' => $this->getUsagePercentage()
            ],
            
            // Fine information
            'fine_info' => [
                'fine_amount' => $this->fine_amount,
                'calculated_fine' => $this->calculateFine(),
                'fine_paid' => $this->fine_paid,
                'fine_status' => $this->getFineStatus(),
                'daily_fine_rate' => 1.00 // Could be configurable
            ]
        ];
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'issued' => 'Issued',
            'returned' => 'Returned',
            'overdue' => 'Overdue',
            'lost' => 'Lost',
            'renewed' => 'Renewed',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'issued' => $this->isOverdue() ? 'danger' : 'primary',
            'returned' => 'success',
            'overdue' => 'danger',
            'lost' => 'dark',
            'renewed' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get days overdue.
     */
    private function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return $this->due_date->diffInDays(now());
    }

    /**
     * Get days until due.
     */
    private function getDaysUntilDue(): int
    {
        if ($this->status !== 'issued' || !$this->due_date) {
            return 0;
        }
        
        if ($this->due_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    /**
     * Get total days for the issue period.
     */
    private function getTotalDays(): int
    {
        if (!$this->issue_date || !$this->due_date) {
            return 0;
        }
        
        return $this->issue_date->diffInDays($this->due_date);
    }

    /**
     * Get remaining days.
     */
    private function getRemainingDays(): int
    {
        if ($this->status !== 'issued') {
            return 0;
        }
        
        return $this->getDaysUntilDue();
    }

    /**
     * Get usage percentage.
     */
    private function getUsagePercentage(): float
    {
        $totalDays = $this->getTotalDays();
        
        if ($totalDays === 0) {
            return 0;
        }
        
        $usedDays = $this->issue_date->diffInDays(now());
        
        return round(($usedDays / $totalDays) * 100, 2);
    }

    /**
     * Get fine status.
     */
    private function getFineStatus(): string
    {
        if ($this->fine_amount <= 0) {
            return 'no_fine';
        }
        
        if ($this->fine_paid) {
            return 'paid';
        }
        
        return 'pending';
    }
}
