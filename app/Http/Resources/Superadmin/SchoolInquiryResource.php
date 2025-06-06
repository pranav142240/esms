<?php

namespace App\Http\Resources\Superadmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolInquiryResource extends JsonResource
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
            'form_data' => $this->form_data,
            'status' => $this->status,
            'notes' => $this->notes,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Extracted common fields for easy access
            'school_name' => $this->form_data['school_name'] ?? null,
            'email' => $this->form_data['email'] ?? null,
            'phone' => $this->form_data['phone'] ?? null,
            'domain' => $this->form_data['domain'] ?? null,
            
            // Status badge information
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
        ];
    }

    /**
     * Get status label for display.
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'registered' => 'Registered',
            'archived' => 'Archived',
            default => 'Unknown',
        };
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'registered' => 'primary',
            'archived' => 'secondary',
            default => 'secondary',
        };
    }
}
