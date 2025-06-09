<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_number',
        'name',
        'description',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_marks',
        'pass_marks',
        'exam_type',
        'instructions',
        'status',
        'created_by'
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_marks' => 'integer',
        'pass_marks' => 'integer',
        'duration_minutes' => 'integer',
        'deleted_at' => 'datetime'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exam) {
            if (empty($exam->exam_number)) {
                $exam->exam_number = static::generateExamNumber();
            }
        });
    }

    /**
     * Generate unique exam number.
     */
    public static function generateExamNumber(): string
    {
        $prefix = 'EX-' . date('Y') . '-';
        $lastExam = static::where('exam_number', 'like', $prefix . '%')
            ->orderBy('exam_number', 'desc')
            ->first();

        if ($lastExam) {
            $lastNumber = (int) substr($lastExam->exam_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the class that owns the exam.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the subject that owns the exam.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user who created the exam.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the exam results for this exam.
     */
    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * Scope for filtering by class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for filtering by subject.
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if exam is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->exam_date > now()->toDateString();
    }

    /**
     * Check if exam is today.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->exam_date == now()->toDateString();
    }

    /**
     * Check if exam is completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || $this->exam_date < now()->toDateString();
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$minutes}m";
    }
}
