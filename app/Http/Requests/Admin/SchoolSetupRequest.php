<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Tenant;

class SchoolSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'school_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('tenants', 'name')
            ],
            'school_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')
            ],
            'school_phone' => [
                'nullable',
                'string',
                'max:20'
            ],
            'school_address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'preferred_domain' => [
                'nullable',
                'string',
                'alpha_dash',
                'min:3',
                'max:50',
                Rule::unique('tenants', 'domain')
            ],
            'subscription_plan' => [
                'required',
                'string',
                'in:basic,standard,premium'
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'school_name.required' => 'School name is required.',
            'school_name.min' => 'School name must be at least 3 characters.',
            'school_name.unique' => 'A school with this name already exists.',
            'school_email.required' => 'School email address is required.',
            'school_email.email' => 'Please provide a valid email address.',
            'school_email.unique' => 'This email address is already registered.',
            'preferred_domain.alpha_dash' => 'Domain can only contain letters, numbers, dashes and underscores.',
            'preferred_domain.unique' => 'This domain name is already taken.',
            'preferred_domain.min' => 'Domain must be at least 3 characters.',
            'subscription_plan.required' => 'Please select a subscription plan.',
            'subscription_plan.in' => 'Please select a valid subscription plan.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, or GIF file.',
            'logo.max' => 'Logo file size cannot exceed 2MB.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'school_name' => 'school name',
            'school_email' => 'school email',
            'school_phone' => 'school phone',
            'school_address' => 'school address',
            'preferred_domain' => 'preferred domain',
            'subscription_plan' => 'subscription plan'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and prepare domain if provided
        if ($this->has('preferred_domain') && !empty($this->preferred_domain)) {
            $this->merge([
                'preferred_domain' => strtolower(trim($this->preferred_domain))
            ]);
        }

        // Clean school name
        if ($this->has('school_name')) {
            $this->merge([
                'school_name' => trim($this->school_name)
            ]);
        }

        // Clean email
        if ($this->has('school_email')) {
            $this->merge([
                'school_email' => trim(strtolower($this->school_email))
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation for domain conflicts
            if ($this->has('preferred_domain') && !empty($this->preferred_domain)) {
                $domain = $this->preferred_domain;
                
                // Check if domain is reserved
                $reservedDomains = ['admin', 'api', 'www', 'mail', 'ftp', 'superadmin', 'app', 'dashboard'];
                if (in_array($domain, $reservedDomains)) {
                    $validator->errors()->add('preferred_domain', 'This domain name is reserved and cannot be used.');
                }
            }

            // Validate school name format
            if ($this->has('school_name')) {
                $schoolName = $this->school_name;
                
                // Check for inappropriate content (basic check)
                $forbiddenWords = ['admin', 'superadmin', 'api', 'test', 'demo'];
                foreach ($forbiddenWords as $word) {
                    if (stripos($schoolName, $word) !== false) {
                        $validator->errors()->add('school_name', 'School name contains forbidden words.');
                        break;
                    }
                }
            }
        });
    }
}
