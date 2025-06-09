<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'status' => 'required|string|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'The student is required.',
            'student_id.exists' => 'The selected student does not exist.',
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'attendance_date.required' => 'The attendance date is required.',
            'attendance_date.date' => 'The attendance date must be a valid date.',
            'attendance_date.before_or_equal' => 'The attendance date cannot be in the future.',
            'status.required' => 'The attendance status is required.',
            'status.in' => 'The attendance status must be one of: present, absent, late, excused.',
            'remarks.max' => 'The remarks may not be greater than 255 characters.',
        ];
    }

    /**
     * Get custom validation attributes.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'class_id' => 'class',
            'subject_id' => 'subject',
            'attendance_date' => 'attendance date',
        ];
    }
}
