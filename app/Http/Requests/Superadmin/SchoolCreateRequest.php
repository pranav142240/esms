<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolCreateRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:schools,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'domain' => ['required', 'string', 'unique:schools,domain', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?$/'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'School name is required',
            'email.required' => 'School email is required',
            'email.unique' => 'This email is already registered',
            'phone.required' => 'School phone is required',
            'address.required' => 'School address is required',
            'domain.required' => 'Domain is required',
            'domain.unique' => 'This domain is already taken',
            'domain.regex' => 'Domain format is invalid',
            'subscription_plan_id.required' => 'Subscription plan is required',
            'subscription_plan_id.exists' => 'Selected subscription plan does not exist',
            'logo.image' => 'Logo must be an image',
            'logo.max' => 'Logo size must not exceed 2MB',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('domain')) {
            $this->merge([
                'domain' => strtolower(trim($this->domain))
            ]);
        }
    }
}
