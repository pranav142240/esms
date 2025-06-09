<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class DailyAttendances extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'attendance_date',
        'status',
        'remarks',
        'marked_by',
        'marked_at'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'marked_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the student that owns the attendance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class that owns the attendance.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the subject that owns the attendance.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user who marked the attendance.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope for filtering by class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for filtering by student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if attendance is present.
     */
    public function getIsPresentAttribute(): bool
    {
        return $this->status === 'present';
    }

    /**
     * Check if attendance is absent.
     */
    public function getIsAbsentAttribute(): bool
    {
        return $this->status === 'absent';
    }

    /**
     * Check if attendance is late.
     */
    public function getIsLateAttribute(): bool
    {
        return $this->status === 'late';
    }

    /**
     * Get attendance status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'present' => 'green',
            'absent' => 'red',
            'late' => 'yellow',
            'excused' => 'blue',
            default => 'gray'
        };
    }
}
