<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $studentId = $this->route('student');
        
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($studentId ? "user_id,{$studentId},students" : ''),
            'password' => $this->isMethod('POST') ? 'required|string|min:8' : 'nullable|string|min:8',
            'student_code' => 'nullable|string|unique:students,student_code,' . $studentId,
            'roll_number' => 'nullable|string|max:50',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'admission_date' => 'required|date',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'parent_phone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Student name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
            'class_id.required' => 'Class selection is required',
            'class_id.exists' => 'Selected class does not exist',
            'session_id.required' => 'Academic session is required',
            'session_id.exists' => 'Selected session does not exist',
            'admission_date.required' => 'Admission date is required',
            'admission_date.date' => 'Please provide a valid admission date',
            'date_of_birth.date' => 'Please provide a valid birth date',
            'date_of_birth.before' => 'Birth date must be before today',
            'gender.in' => 'Gender must be male, female, or other',
            'status.in' => 'Status must be active, inactive, graduated, or transferred',
            'photo.image' => 'Photo must be an image file',
            'photo.mimes' => 'Photo must be a jpeg, png, or jpg file',
            'photo.max' => 'Photo size must not exceed 2MB'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'student name',
            'email' => 'email address',
            'class_id' => 'class',
            'section_id' => 'section',
            'session_id' => 'academic session',
            'date_of_birth' => 'birth date',
            'parent_phone' => 'parent phone number',
            'emergency_contact' => 'emergency contact number'
        ];
    }
}
