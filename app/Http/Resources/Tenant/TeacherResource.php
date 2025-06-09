<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->date_of_birth ? $this->date_of_birth->age : null,
            'gender' => $this->gender,
            'gender_label' => $this->getGenderLabel(),
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'joining_date' => $this->joining_date?->format('Y-m-d'),
            'tenure_years' => $this->joining_date ? $this->joining_date->diffInYears(now()) : 0,
            'salary' => $this->salary,
            'department' => $this->department,
            'designation' => $this->designation,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'status_icon' => $this->getStatusIcon(),
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ];
            }),
            
            // Counts
            'subjects_count' => $this->when(isset($this->subjects_count), $this->subjects_count),
            'classes_count' => $this->when(isset($this->classes_count), $this->classes_count),
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'exams_count' => $this->when(isset($this->exams_count), $this->exams_count),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get gender label.
     */
    private function getGenderLabel(): string
    {
        return match ($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            default => 'Not Specified',
        };
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'terminated' => 'Terminated',
            default => 'Unknown',
        };
    }

    /**
     * Get status color.
     */
    private function getStatusColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'terminated' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status icon.
     */
    private function getStatusIcon(): string
    {
        return match ($this->status) {
            'active' => 'check-circle',
            'inactive' => 'pause-circle',
            'terminated' => 'x-circle',
            default => 'question-circle',
        };
    }
}
