<?php

namespace App\Http\Resources\Superadmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'tenant_id' => $this->tenant_id,
            'converted_at' => $this->converted_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Status information
            'status_info' => [
                'label' => ucfirst(str_replace('_', ' ', $this->status)),
                'can_edit' => !$this->isConverted(),
                'can_delete' => !$this->isConverted(),
                'can_reset_password' => !$this->isConverted(),
            ],
            
            // Tenant information (when loaded)
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'data' => $this->tenant->data,
                    'domains' => $this->tenant->domains->map(function ($domain) {
                        return [
                            'id' => $domain->id,
                            'domain' => $domain->domain,
                        ];
                    }),
                ];
            }),
            
            // Conversion history (when loaded)
            'conversions' => $this->whenLoaded('conversions', function () {
                return $this->conversions->map(function ($conversion) {
                    return [
                        'id' => $conversion->id,
                        'status' => $conversion->conversion_status,
                        'error_message' => $conversion->error_message,
                        'converted_at' => $conversion->converted_at->format('Y-m-d H:i:s'),
                        'is_completed' => $conversion->isCompleted(),
                        'is_failed' => $conversion->isFailed(),
                    ];
                });
            }),
            
            // Helper flags
            'flags' => [
                'is_converted' => $this->isConverted(),
                'can_create_school' => $this->canCreateSchool(),
                'requires_password_change' => $this->status === 'pending',
            ],
        ];
    }
}
