<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'is_all_day',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'target_audience',
        'class_ids',
        'max_participants',
        'registration_required',
        'registration_deadline',
        'status',
        'priority',
        'organizer_name',
        'organizer_contact',
        'budget',
        'actual_cost',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'registration_deadline' => 'datetime',
        'recurrence_end_date' => 'date',
        'class_ids' => 'array',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'registration_required' => 'boolean',
        'recurrence_interval' => 'integer',
        'max_participants' => 'integer',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    /**
     * Get the user who created the event.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the classes associated with the event.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'event_classes', 'event_id', 'class_id');
    }

    /**
     * Get all participants for this event.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'event_participants')
            ->withPivot(['registered_at', 'attendance_status', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get all staff members assigned to this event.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_staff')
            ->withPivot(['role', 'responsibility', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get all of the event's attachments.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', Carbon::today());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', Carbon::today());
    }

    /**
     * Scope a query to only include ongoing events.
     */
    public function scopeOngoing($query)
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    /**
     * Scope a query by event type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query by target audience.
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where('target_audience', $audience);
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
     * Scope a query for events in a date range.
     */
    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return Carbon::today()->lt($this->start_date);
    }

    /**
     * Check if the event is ongoing.
     */
    public function isOngoing(): bool
    {
        $today = Carbon::today();
        return $today->gte($this->start_date) && $today->lte($this->end_date);
    }

    /**
     * Check if the event is past.
     */
    public function isPast(): bool
    {
        return Carbon::today()->gt($this->end_date);
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        if (!$this->registration_required) {
            return false;
        }

        if ($this->registration_deadline && Carbon::now()->gt($this->registration_deadline)) {
            return false;
        }

        return $this->status === 'scheduled' && $this->isUpcoming();
    }

    /**
     * Check if event has available spots.
     */
    public function hasAvailableSpots(): bool
    {
        if (!$this->max_participants) {
            return true;
        }

        return $this->participants()->count() < $this->max_participants;
    }

    /**
     * Get the event status.
     */
    public function getEventStatusAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->status === 'completed') {
            return 'completed';
        }

        if ($this->isPast()) {
            return 'past';
        }

        if ($this->isOngoing()) {
            return 'ongoing';
        }

        if ($this->isUpcoming()) {
            return 'upcoming';
        }

        return 'scheduled';
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->event_status) {
            'cancelled' => 'red',
            'completed' => 'green',
            'past' => 'gray',
            'ongoing' => 'blue',
            'upcoming' => 'yellow',
            'scheduled' => 'indigo',
            default => 'gray'
        };
    }

    /**
     * Get the type label for UI.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'academic' => 'Academic Event',
            'sports' => 'Sports Event',
            'cultural' => 'Cultural Event',
            'social' => 'Social Event',
            'meeting' => 'Meeting',
            'workshop' => 'Workshop',
            'seminar' => 'Seminar',
            'conference' => 'Conference',
            'exam' => 'Examination',
            'holiday' => 'Holiday',
            'maintenance' => 'Maintenance',
            'other' => 'Other',
            default => ucfirst($this->type ?? 'General')
        };
    }

    /**
     * Get the category label for UI.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'curricular' => 'Curricular',
            'extracurricular' => 'Extra-curricular',
            'administrative' => 'Administrative',
            'parent_teacher' => 'Parent-Teacher',
            'staff_meeting' => 'Staff Meeting',
            'student_activity' => 'Student Activity',
            'community' => 'Community',
            default => ucfirst($this->category ?? 'General')
        ];
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
        ];
    }

    /**
     * Get event duration in hours.
     */
    public function getDurationInHoursAttribute(): float
    {
        if ($this->is_all_day) {
            return $this->start_date->diffInDays($this->end_date) * 24;
        }

        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        return $start->diffInHours($end);
    }

    /**
     * Get event duration in days.
     */
    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get participants count.
     */
    public function getParticipantsCountAttribute(): int
    {
        return $this->participants()->count();
    }

    /**
     * Get available spots.
     */
    public function getAvailableSpotsAttribute(): ?int
    {
        if (!$this->max_participants) {
            return null;
        }

        return max(0, $this->max_participants - $this->participants_count);
    }

    /**
     * Check if user can register for this event.
     */
    public function canUserRegister($user): bool
    {
        if (!$this->registration_required) {
            return false;
        }

        if (!$this->isRegistrationOpen()) {
            return false;
        }

        if (!$this->hasAvailableSpots()) {
            return false;
        }

        // Check if user is already registered
        if ($user->hasRole('student')) {
            return !$this->participants()->where('student_id', $user->student->id ?? 0)->exists();
        }

        return false;
    }

    /**
     * Register a student for this event.
     */
    public function registerStudent(Student $student, array $data = []): bool
    {
        if (!$this->isRegistrationOpen() || !$this->hasAvailableSpots()) {
            return false;
        }

        $this->participants()->attach($student->id, array_merge([
            'registered_at' => Carbon::now(),
            'attendance_status' => 'registered'
        ], $data));

        return true;
    }

    /**
     * Unregister a student from this event.
     */
    public function unregisterStudent(Student $student): bool
    {
        return $this->participants()->detach($student->id) > 0;
    }

    /**
     * Mark student attendance for this event.
     */
    public function markAttendance(Student $student, string $status, string $notes = null): bool
    {
        return $this->participants()->updateExistingPivot($student->id, [
            'attendance_status' => $status,
            'notes' => $notes
        ]) > 0;
    }

    /**
     * Get events visible to a specific user based on their role and classes.
     */
    public static function visibleToUser($user, array $userClassIds = []): \Illuminate\Database\Eloquent\Builder
    {
        return self::where(function ($query) use ($user, $userClassIds) {
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
     * Cancel the event.
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $this->notes . "\n\nCancellation reason: " . ($reason ?? 'Not specified')
        ]);
    }

    /**
     * Complete the event.
     */
    public function complete(array $summary = []): void
    {
        $this->update(array_merge([
            'status' => 'completed'
        ], $summary));
    }

    /**
     * Reschedule the event.
     */
    public function reschedule(Carbon $newStartDate, Carbon $newEndDate, array $data = []): void
    {
        $this->update(array_merge([
            'start_date' => $newStartDate,
            'end_date' => $newEndDate
        ], $data));
    }
}
