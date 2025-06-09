<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classes extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'grade_level',
        'description',
        'capacity',
        'status'
    ];

    protected $casts = [
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the students for the class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the sections for the class.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    /**
     * Get the subjects for the class.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    /**
     * Get the routines for the class.
     */
    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'class_id');
    }

    /**
     * Get the exams for the class.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    /**
     * Get the syllabus for the class.
     */
    public function syllabus(): HasMany
    {
        return $this->hasMany(Syllabus::class, 'class_id');
    }

    /**
     * The teachers that are assigned to the class.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_teacher', 'class_id', 'teacher_id');
    }
}
