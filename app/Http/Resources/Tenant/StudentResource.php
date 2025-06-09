<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'student_code' => $this->student_code,
            'roll_number' => $this->roll_number,
            'status' => $this->status,
            'admission_date' => $this->admission_date?->format('Y-m-d'),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'religion' => $this->religion,
            'phone' => $this->phone,
            'address' => $this->address,
            'parent_phone' => $this->parent_phone,
            'emergency_contact' => $this->emergency_contact,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // User information
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'photo' => $this->user->photo ? asset('storage/' . $this->user->photo) : null,
                    'email_verified_at' => $this->user->email_verified_at?->format('Y-m-d H:i:s')
                ];
            }),
            
            // Class information
            'class' => $this->whenLoaded('class', function () {
                return [
                    'id' => $this->class->id,
                    'name' => $this->class->name,
                    'grade_level' => $this->class->grade_level
                ];
            }),
            
            // Section information
            'section' => $this->whenLoaded('section', function () {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name,
                    'capacity' => $this->section->capacity
                ];
            }),
            
            // Session information
            'session' => $this->whenLoaded('session', function () {
                return [
                    'id' => $this->session->id,
                    'name' => $this->session->name,
                    'start_date' => $this->session->start_date?->format('Y-m-d'),
                    'end_date' => $this->session->end_date?->format('Y-m-d'),
                    'is_active' => $this->session->is_active
                ];
            }),
            
            // Parents information
            'parents' => $this->whenLoaded('parents', function () {
                return $this->parents->map(function ($parent) {
                    return [
                        'id' => $parent->id,
                        'name' => $parent->user->name,
                        'email' => $parent->user->email,
                        'phone' => $parent->user->phone,
                        'relationship' => $parent->relationship
                    ];
                });
            }),
            
            // Statistics (when requested)
            'statistics' => $this->when($request->input('include_stats'), function () {
                return [
                    'attendance_percentage' => $this->calculateAttendancePercentage(),
                    'total_fees' => $this->calculateTotalFees(),
                    'pending_fees' => $this->calculatePendingFees(),
                    'books_issued' => $this->bookIssues()->where('status', 'issued')->count(),
                    'average_grade' => $this->calculateAverageGrade()
                ];
            })
        ];
    }

    /**
     * Calculate attendance percentage for the student.
     */
    protected function calculateAttendancePercentage(): float
    {
        $totalDays = $this->attendances()->count();
        if ($totalDays === 0) return 0;
        
        $presentDays = $this->attendances()->where('status', 'present')->count();
        return round(($presentDays / $totalDays) * 100, 2);
    }

    /**
     * Calculate total fees for the student.
     */
    protected function calculateTotalFees(): float
    {
        return $this->fees()->sum('amount') ?? 0;
    }

    /**
     * Calculate pending fees for the student.
     */
    protected function calculatePendingFees(): float
    {
        return $this->fees()->where('status', 'pending')->sum('amount') ?? 0;
    }

    /**
     * Calculate average grade for the student.
     */
    protected function calculateAverageGrade(): float
    {
        return $this->gradebooks()->avg('marks') ?? 0;
    }
}
