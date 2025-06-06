<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class SchoolInquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // This is a public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */    public function rules(): array
    {
        return [
            // Required default fields
            'school_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:school_inquiries,school_email|unique:schools,email',
            'phone' => 'required|string|max:20',
            'domain' => 'required|string|max:100|unique:schools,domain|unique:school_inquiries,proposed_domain|regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/i',
            'address' => 'required|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'logo' => 'nullable|file|image|max:2048', // 2MB max
            'tagline' => 'nullable|string|max:255',
            
            // Optional custom fields
            'principal_name' => 'nullable|string|max:255',
            'establishment_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'student_capacity' => 'nullable|integer|min:1',
            'school_type' => 'nullable|string|in:primary,secondary,higher_secondary,university',
            'board_affiliation' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'support_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'school_name.required' => 'School name is required.',
            'email.required' => 'School email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered or has an existing inquiry.',
            'phone.required' => 'Phone number is required.',
            'domain.required' => 'Domain name is required.',
            'domain.unique' => 'This domain name is already taken.',
            'domain.regex' => 'Domain name must contain only letters, numbers, and hyphens.',
            'address.required' => 'School address is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo file size cannot exceed 2MB.',
            'establishment_year.min' => 'Establishment year cannot be before 1800.',
            'establishment_year.max' => 'Establishment year cannot be in the future.',
            'student_capacity.min' => 'Student capacity must be at least 1.',
            'school_type.in' => 'School type must be one of: primary, secondary, higher_secondary, university.',
            'support_email.email' => 'Please provide a valid support email address.',
            'website.url' => 'Please provide a valid website URL.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'school_name' => 'school name',
            'email' => 'email address',
            'phone' => 'phone number',
            'domain' => 'domain name',
            'address' => 'address',
            'logo' => 'school logo',
            'tagline' => 'tagline',
            'principal_name' => 'principal name',
            'establishment_year' => 'establishment year',
            'student_capacity' => 'student capacity',
            'school_type' => 'school type',
            'board_affiliation' => 'board affiliation',
            'license_number' => 'license number',
            'support_email' => 'support email',
            'website' => 'website',
        ];
    }
}
