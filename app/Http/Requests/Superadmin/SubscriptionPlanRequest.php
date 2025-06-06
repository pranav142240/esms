<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanRequest extends FormRequest
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
        $planId = $this->route('subscription_plan');
        
        return [
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $planId,
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3|in:USD,EUR,GBP,INR',
            'billing_cycle' => 'required|string|in:monthly,yearly,quarterly',
            'features' => 'required|array',
            'features.*' => 'string|max:255',
            'user_limit' => 'nullable|integer|min:1',
            'storage_limit_gb' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Plan name is required.',
            'name.unique' => 'A plan with this name already exists.',
            'price.required' => 'Plan price is required.',
            'price.numeric' => 'Plan price must be a valid number.',
            'price.min' => 'Plan price cannot be negative.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Currency must be one of: USD, EUR, GBP, INR.',
            'billing_cycle.required' => 'Billing cycle is required.',
            'billing_cycle.in' => 'Billing cycle must be monthly, yearly, or quarterly.',
            'features.required' => 'At least one feature is required.',
            'features.array' => 'Features must be provided as an array.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('is_default')) {
            $this->merge([
                'is_default' => filter_var($this->is_default, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
