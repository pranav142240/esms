<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'attendance_date' => $this->attendance_date?->format('Y-m-d'),
            'status' => $this->status,
            'remarks' => $this->remarks,
            'marked_at' => $this->marked_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Status indicators
            'is_present' => $this->status === 'present',
            'is_absent' => $this->status === 'absent',
            'is_late' => $this->status === 'late',
            'is_excused' => $this->status === 'excused',
            
            // Status display properties
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'status_icon' => $this->getStatusIcon(),
            
            // Relationships
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->name,
                    'roll_number' => $this->student->roll_number,
                    'email' => $this->student->email,
                    'photo' => $this->student->photo,
                ];
            }),
            
            'class' => $this->whenLoaded('class', function () {
                return [
                    'id' => $this->class->id,
                    'name' => $this->class->name,
                    'section' => $this->class->section,
                ];
            }),
            
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                    'code' => $this->subject->code,
                ];
            }),
            
            'marked_by' => $this->whenLoaded('markedBy', function () {
                return [
                    'id' => $this->markedBy->id,
                    'name' => $this->markedBy->name,
                    'email' => $this->markedBy->email,
                ];
            }),
        ];
    }

    /**
     * Get attendance status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'excused' => 'Excused',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'present' => 'green',
            'absent' => 'red',
            'late' => 'yellow',
            'excused' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get status icon.
     */
    private function getStatusIcon(): string
    {
        return match($this->status) {
            'present' => 'check-circle',
            'absent' => 'x-circle',
            'late' => 'clock',
            'excused' => 'shield-check',
            default => 'help-circle'
        };
    }
}
