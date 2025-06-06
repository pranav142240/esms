<?php

namespace App\Http\Resources\Superadmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'domain' => $this->domain,
            'school_code' => $this->school_code,
            'logo' => $this->logo,
            'tagline' => $this->tagline,
            'address' => $this->address,
            'status' => $this->status,
            'database_name' => $this->database_name,
            'form_data' => $this->form_data,
            'subscription_start_date' => $this->subscription_start_date?->format('Y-m-d'),
            'subscription_end_date' => $this->subscription_end_date?->format('Y-m-d'),
            'is_expired' => $this->isExpired(),
            'in_grace_period' => $this->in_grace_period,
            'grace_period_end_date' => $this->grace_period_end_date?->format('Y-m-d'),
            'is_active' => $this->isActive(),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'subscription_plan' => $this->whenLoaded('subscriptionPlan', function () {
                return new SubscriptionPlanResource($this->subscriptionPlan);
            }),
            'approved_by' => $this->whenLoaded('approvedBy', function () {
                return new SuperadminResource($this->approvedBy);
            }),
        ];
    }
}
