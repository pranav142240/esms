<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'title' => $this->title,
            'isbn' => $this->isbn,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'publication_year' => $this->publication_year,
            'category' => $this->category,
            'subject' => $this->subject,
            'description' => $this->description,
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'issued_copies' => $this->total_copies - $this->available_copies,
            'language' => $this->language,
            'edition' => $this->edition,
            'pages' => $this->pages,
            'price' => $this->price,
            'location' => $this->location,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'is_available' => $this->isAvailable(),
            'availability_info' => [
                'is_available' => $this->isAvailable(),
                'available_copies' => $this->available_copies,
                'total_copies' => $this->total_copies,
                'issued_copies' => $this->total_copies - $this->available_copies,
                'availability_percentage' => $this->total_copies > 0 
                    ? round(($this->available_copies / $this->total_copies) * 100, 2) 
                    : 0
            ],
            
            // Relationships
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email
                ];
            }),
            
            // Count fields
            'issues_count' => $this->when(isset($this->issues_count), $this->issues_count),
            'active_issues_count' => $this->whenLoaded('activeIssues', function () {
                return $this->activeIssues->count();
            }),
            
            // Issues information
            'issues' => $this->whenLoaded('issues', function () {
                return BookIssueResource::collection($this->issues);
            }),
            
            'active_issues' => $this->whenLoaded('activeIssues', function () {
                return BookIssueResource::collection($this->activeIssues);
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // Formatted dates
            'created_at_formatted' => $this->created_at?->format('M d, Y'),
            'updated_at_formatted' => $this->updated_at?->format('M d, Y'),
            
            // Additional metadata
            'metadata' => [
                'total_pages' => $this->pages,
                'estimated_reading_time' => $this->pages ? $this->getEstimatedReadingTime() : null,
                'age_group' => $this->getAgeGroup(),
                'popularity_score' => $this->getPopularityScore()
            ]
        ];
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'issued' => 'Issued',
            'lost' => 'Lost',
            'damaged' => 'Damaged',
            'maintenance' => 'Under Maintenance',
            'reserved' => 'Reserved',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'available' => 'success',
            'issued' => 'warning',
            'lost' => 'danger',
            'damaged' => 'danger',
            'maintenance' => 'info',
            'reserved' => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Get estimated reading time based on pages.
     */
    private function getEstimatedReadingTime(): string
    {
        if (!$this->pages) {
            return 'Unknown';
        }

        // Assuming average reading speed of 250 words per minute and 250 words per page
        $minutes = $this->pages * 1; // 1 minute per page as rough estimate
        
        if ($minutes < 60) {
            return "{$minutes} minutes";
        }
        
        $hours = round($minutes / 60, 1);
        return "{$hours} hours";
    }

    /**
     * Get age group based on category or other factors.
     */
    private function getAgeGroup(): string
    {
        $category = strtolower($this->category ?? '');
        
        if (str_contains($category, 'children') || str_contains($category, 'kids')) {
            return 'Children (5-12)';
        }
        
        if (str_contains($category, 'teen') || str_contains($category, 'young adult')) {
            return 'Teenagers (13-18)';
        }
        
        if (str_contains($category, 'academic') || str_contains($category, 'textbook')) {
            return 'Academic';
        }
        
        return 'General (All Ages)';
    }

    /**
     * Get popularity score based on issue history.
     */
    private function getPopularityScore(): int
    {
        // Simple popularity calculation based on total issues
        $totalIssues = $this->issues_count ?? 0;
        
        if ($totalIssues >= 50) return 5;
        if ($totalIssues >= 30) return 4;
        if ($totalIssues >= 15) return 3;
        if ($totalIssues >= 5) return 2;
        if ($totalIssues >= 1) return 1;
        
        return 0;
    }
}
