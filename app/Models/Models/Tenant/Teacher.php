<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'qualification',
        'experience_years',
        'joining_date',
        'salary',
        'department',
        'designation',
        'status',
        'created_by'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'experience_years' => 'integer'
    ];

    /**
     * Get the user associated with the teacher.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created the teacher record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the subjects assigned to the teacher.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    /**
     * The classes that the teacher is assigned to.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classes::class, 'class_teacher', 'teacher_id', 'class_id');
    }

    /**
     * Get the students assigned to the teacher.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'teacher_student', 'teacher_id', 'student_id');
    }    /**
     * Get the exams created by the teacher.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    /**
     * Generate a unique employee code.
     */
    public static function generateEmployeeCode(): string
    {
        $year = date('Y');
        $lastTeacher = static::withTrashed()
                           ->where('employee_code', 'like', "TCH-{$year}-%")
                           ->orderBy('employee_code', 'desc')
                           ->first();
        
        if ($lastTeacher) {
            $lastNumber = (int) substr($lastTeacher->employee_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "TCH-{$year}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
