<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
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
            'exam_number' => $this->exam_number,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'exam_date' => $this->exam_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration_minutes' => $this->duration_minutes,
            'total_marks' => $this->total_marks,
            'passing_marks' => $this->passing_marks,
            'status' => $this->status,
            'instructions' => $this->instructions,
            'is_published' => $this->is_published,
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Status indicators
            'is_scheduled' => $this->status === 'scheduled',
            'is_ongoing' => $this->status === 'ongoing',
            'is_completed' => $this->status === 'completed',
            'is_cancelled' => $this->status === 'cancelled',
            'is_upcoming' => $this->exam_date > now(),
            'is_today' => $this->exam_date?->isToday() ?? false,
            
            // Type labels
            'type_label' => $this->getTypeLabel(),
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            
            // Relationships
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
            
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
        ];
    }

    /**
     * Get exam type label.
     */
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'written' => 'Written Exam',
            'oral' => 'Oral Exam',
            'practical' => 'Practical Exam',
            'online' => 'Online Exam',
            default => 'Unknown Type'
        };
    }

    /**
     * Get exam status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown Status'
        };
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'ongoing' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }
}
