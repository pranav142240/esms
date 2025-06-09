<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'target_audience',
        'class_ids',
        'is_published',
        'published_at',
        'expires_at',
        'is_urgent',
        'attachment_path',
        'attachment_name',
        'view_count',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'class_ids' => 'array',
        'is_published' => 'boolean',
        'is_urgent' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the user who created the notice.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all of the notice's comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all of the notice's attachments.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Scope a query to only include published notices.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', Carbon::now());
                    });
    }

    /**
     * Scope a query to only include active notices (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Scope a query to only include urgent notices.
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Scope a query by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query by target audience.
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where('target_audience', $audience);
    }

    /**
     * Scope a query by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query for specific classes.
     */
    public function scopeForClasses($query, array $classIds)
    {
        return $query->where(function ($q) use ($classIds) {
            $q->where('target_audience', '!=', 'specific_classes')
              ->orWhere(function ($subQ) use ($classIds) {
                  foreach ($classIds as $classId) {
                      $subQ->orWhereJsonContains('class_ids', $classId);
                  }
              });
        });
    }

    /**
     * Check if the notice is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if the notice is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_published && 
               ($this->published_at === null || Carbon::now()->isAfter($this->published_at)) &&
               !$this->isExpired();
    }

    /**
     * Check if the notice is scheduled for future publication.
     */
    public function isScheduled(): bool
    {
        return $this->is_published && 
               $this->published_at && 
               Carbon::now()->isBefore($this->published_at);
    }

    /**
     * Get the status of the notice.
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_published) {
            return 'draft';
        }
        
        if ($this->isScheduled()) {
            return 'scheduled';
        }
        
        if ($this->isExpired()) {
            return 'expired';
        }
        
        return 'active';
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'scheduled' => 'blue',
            'expired' => 'red',
            'active' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get the priority color for UI.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get the type label for UI.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'general' => 'General Notice',
            'academic' => 'Academic Notice',
            'event' => 'Event Notice',
            'holiday' => 'Holiday Notice',
            'exam' => 'Exam Notice',
            'fee' => 'Fee Notice',
            'admission' => 'Admission Notice',
            'sports' => 'Sports Notice',
            'cultural' => 'Cultural Notice',
            'maintenance' => 'Maintenance Notice',
            default => ucfirst($this->type ?? 'General')
        };
    }

    /**
     * Get the audience label for UI.
     */
    public function getAudienceLabelAttribute(): string
    {
        return match ($this->target_audience) {
            'all' => 'All Users',
            'students' => 'Students',
            'teachers' => 'Teachers',
            'parents' => 'Parents',
            'staff' => 'Staff',
            'specific_classes' => 'Specific Classes',
            default => ucfirst($this->target_audience ?? 'All')
        };
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Publish the notice.
     */
    public function publish(Carbon $publishAt = null): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => $publishAt ?? Carbon::now()
        ]);
    }

    /**
     * Unpublish the notice.
     */
    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'published_at' => null
        ]);
    }

    /**
     * Schedule the notice for publication.
     */
    public function schedule(Carbon $publishAt): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => $publishAt
        ]);
    }

    /**
     * Mark the notice as urgent.
     */
    public function markAsUrgent(): void
    {
        $this->update([
            'is_urgent' => true,
            'priority' => 'urgent'
        ]);
    }

    /**
     * Remove urgent status from the notice.
     */
    public function removeUrgentStatus(): void
    {
        $this->update([
            'is_urgent' => false
        ]);
    }

    /**
     * Get notices visible to a specific user based on their role and classes.
     */
    public static function visibleToUser($user, array $userClassIds = []): \Illuminate\Database\Eloquent\Builder
    {
        return self::published()
            ->active()
            ->where(function ($query) use ($user, $userClassIds) {
                $query->where('target_audience', 'all');
                
                // Add role-specific visibility
                if ($user->hasRole('student')) {
                    $query->orWhere('target_audience', 'students')
                          ->orWhere(function ($q) use ($userClassIds) {
                              $q->where('target_audience', 'specific_classes');
                              foreach ($userClassIds as $classId) {
                                  $q->orWhereJsonContains('class_ids', $classId);
                              }
                          });
                } elseif ($user->hasRole('teacher')) {
                    $query->orWhere('target_audience', 'teachers');
                } elseif ($user->hasRole('parent')) {
                    $query->orWhere('target_audience', 'parents')
                          ->orWhere(function ($q) use ($userClassIds) {
                              $q->where('target_audience', 'specific_classes');
                              foreach ($userClassIds as $classId) {
                                  $q->orWhereJsonContains('class_ids', $classId);
                              }
                          });
                } else {
                    $query->orWhere('target_audience', 'staff');
                }
            });
    }

    /**
     * Get the excerpt of the content.
     */
    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get reading time estimate in minutes.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Assuming 200 words per minute
    }

    /**
     * Check if notice has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists() || !empty($this->attachment_path);
    }
}
