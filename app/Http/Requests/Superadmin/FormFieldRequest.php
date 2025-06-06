<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class FormFieldRequest extends FormRequest
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
        $fieldId = $this->route('form_field');
        
        return [
            'name' => 'required|string|max:255|unique:form_fields,name,' . $fieldId,
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,phone,number,textarea,select,radio,checkbox,file,date,time,datetime,url,password',
            'is_required' => 'boolean',
            'is_default' => 'boolean',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
            'validation_rules' => 'nullable|string|max:1000',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Field name is required.',
            'name.unique' => 'A field with this name already exists.',
            'label.required' => 'Field label is required.',
            'type.required' => 'Field type is required.',
            'type.in' => 'Field type must be one of the supported types.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_required')) {
            $this->merge([
                'is_required' => filter_var($this->is_required, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('is_default')) {
            $this->merge([
                'is_default' => filter_var($this->is_default, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
