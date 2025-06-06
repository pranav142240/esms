<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolUpdateRequest extends FormRequest
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
        $schoolId = $this->route('school');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('schools', 'email')->ignore($schoolId)],
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:1000'],
            'domain' => ['sometimes', 'string', Rule::unique('schools', 'domain')->ignore($schoolId), 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?$/'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'subscription_plan_id' => ['sometimes', 'exists:subscription_plans,id'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered',
            'domain.unique' => 'This domain is already taken',
            'domain.regex' => 'Domain format is invalid',
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
