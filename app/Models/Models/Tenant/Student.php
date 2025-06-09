<?php

namespace App\Models\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_code',
        'roll_number',
        'class_id',
        'section_id',
        'session_id',
        'admission_date',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'phone',
        'address',
        'parent_phone',
        'emergency_contact',
        'status',
        'notes'
    ];

    protected $casts = [
        'admission_date' => 'date',
        'date_of_birth' => 'date',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the user that owns the student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class that the student belongs to.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the section that the student belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the session that the student belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the parents for the student.
     */
    public function parents(): HasMany
    {
        return $this->hasMany(ParentModel::class);
    }

    /**
     * Get the enrollments for the student.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the attendance records for the student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(DailyAttendances::class);
    }

    /**
     * Get the gradebook entries for the student.
     */
    public function gradebooks(): HasMany
    {
        return $this->hasMany(Gradebook::class);
    }

    /**
     * Get the book issues for the student.
     */
    public function bookIssues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }

    /**
     * Get the fee records for the student.
     */
    public function fees(): HasMany
    {
        return $this->hasMany(StudentFeeManager::class);
    }

    /**
     * Generate student code automatically.
     */
    public static function generateStudentCode(): string
    {
        $year = date('Y');
        $lastStudent = static::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $lastStudent ? (int)substr($lastStudent->student_code, -4) + 1 : 1;
        
        return 'STU' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->student_code)) {
                $student->student_code = static::generateStudentCode();
            }
        });
    }
}
