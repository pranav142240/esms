<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
        $teacherId = $this->route('teacher') ?? $this->route('id');
        
        return [
            'user_id' => 'nullable|exists:users,id',
            'employee_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('teachers', 'employee_code')->ignore($teacherId)
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('teachers', 'email')->ignore($teacherId)
            ],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'qualification' => 'nullable|string|max:1000',
            'experience_years' => 'integer|min:0|max:50',
            'joining_date' => 'required|date|before_or_equal:today',
            'salary' => 'nullable|numeric|min:0|max:999999.99',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'status' => 'in:active,inactive,terminated',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user account',
            'employee_code' => 'employee code',
            'date_of_birth' => 'date of birth',
            'experience_years' => 'years of experience',
            'joining_date' => 'joining date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The teacher name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'A teacher with this email already exists.',
            'phone.required' => 'The phone number is required.',
            'employee_code.unique' => 'A teacher with this employee code already exists.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'joining_date.required' => 'The joining date is required.',
            'joining_date.before_or_equal' => 'Joining date cannot be in the future.',
            'experience_years.min' => 'Experience years cannot be negative.',
            'experience_years.max' => 'Experience years cannot exceed 50.',
            'salary.min' => 'Salary cannot be negative.',
            'user_id.exists' => 'The selected user account does not exist.',
        ];
    }
}
