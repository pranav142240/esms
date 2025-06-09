<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'expense_number' => $this->expense_number,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'expense_date' => $this->expense_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'receipt_number' => $this->receipt_number,
            'receipt_file' => $this->receipt_file ? url('storage/' . $this->receipt_file) : null,
            'custom_fields' => $this->custom_fields,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_approved' => $this->is_approved,
            'is_pending' => $this->is_pending,
            'is_overdue' => false, // Basic implementation
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            
            'approved_by' => $this->whenLoaded('approvedBy', function () {
                return $this->approvedBy ? [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ] : null;
            }),
        ];
    }
}
