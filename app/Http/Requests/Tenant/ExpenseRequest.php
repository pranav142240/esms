<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:100',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string|max:1000'
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'title.required' => 'The expense title is required.',
            'title.string' => 'The expense title must be a valid text.',
            'title.max' => 'The expense title cannot exceed 255 characters.',
            
            'category.required' => 'The expense category is required.',
            'category.string' => 'The expense category must be a valid text.',
            'category.max' => 'The expense category cannot exceed 100 characters.',
            
            'amount.required' => 'The expense amount is required.',
            'amount.numeric' => 'The expense amount must be a valid number.',
            'amount.min' => 'The expense amount must be greater than or equal to 0.',
            
            'expense_date.required' => 'The expense date is required.',
            'expense_date.date' => 'The expense date must be a valid date.',
            'expense_date.before_or_equal' => 'The expense date cannot be in the future.',
            
            'receipt_file.file' => 'The receipt must be a valid file.',
            'receipt_file.mimes' => 'The receipt file must be a JPG, JPEG, PNG, or PDF.',
            'receipt_file.max' => 'The receipt file size cannot exceed 2MB.',
            
            'custom_fields.array' => 'Custom fields must be a valid array.',
            
            'description.max' => 'The description cannot exceed 1000 characters.',
            'payment_method.max' => 'The payment method cannot exceed 100 characters.',
            'receipt_number.max' => 'The receipt number cannot exceed 100 characters.',
            'notes.max' => 'The notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'title' => 'expense title',
            'description' => 'expense description',
            'category' => 'expense category',
            'amount' => 'expense amount',
            'expense_date' => 'expense date',
            'payment_method' => 'payment method',
            'receipt_number' => 'receipt number',
            'receipt_file' => 'receipt file',
            'custom_fields' => 'custom fields',
            'notes' => 'notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert amount to float if it's a string
        if ($this->has('amount') && is_string($this->amount)) {
            $this->merge([
                'amount' => (float) str_replace(',', '', $this->amount)
            ]);
        }
    }
}
