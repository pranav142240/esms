<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_code' => $this->class_code,
            'name' => $this->name,
            'section' => $this->section,
            'grade' => $this->grade,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'room_number' => $this->room_number,
            'status' => $this->status,
            'is_active' => $this->status === 'active',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Status indicators
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            
            // Computed fields
            'full_name' => $this->getFullName(),
            
            // Counts
            'students_count' => $this->whenCounted('students'),
            'subjects_count' => $this->whenCounted('subjects'),
            
            // Relationships
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            
            'students' => $this->whenLoaded('students', function () {
                return $this->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name ?? 'N/A',
                        'roll_number' => $student->roll_number,
                        'student_code' => $student->student_code,
                        'status' => $student->status,
                    ];
                });
            }),
            
            'subjects' => $this->whenLoaded('subjects', function () {
                return $this->subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                        'status' => $subject->status,
                    ];
                });
            }),
        ];
    }

    /**
     * Get class status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'archived' => 'Archived',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'red',
            'archived' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get full class name.
     */
    private function getFullName(): string
    {
        $parts = array_filter([$this->name, $this->section]);
        return implode(' - ', $parts);
    }
}
