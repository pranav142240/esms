<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'class_id',
        'teacher_id',
        'credits',
        'status',
        'created_by'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'credits' => 'integer'
    ];

    /**
     * Get the class that owns the subject.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the teacher assigned to the subject.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Get the user who created the subject.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The students enrolled in the subject.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'subject_student', 'subject_id', 'student_id');
    }    /**
     * Get the exams for the subject.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'subject_id');
    }
}
