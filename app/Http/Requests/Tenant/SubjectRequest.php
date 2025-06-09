<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
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
        $subjectId = $this->route('subject') ?? $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('subjects', 'code')->ignore($subjectId)
            ],
            'description' => 'nullable|string|max:1000',
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'credits' => 'integer|min:1|max:10',
            'status' => 'in:active,inactive',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'subject name',
            'code' => 'subject code',
            'class_id' => 'class',
            'teacher_id' => 'teacher',
            'credits' => 'credit hours',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The subject name is required.',
            'class_id.required' => 'Please select a class for this subject.',
            'class_id.exists' => 'The selected class does not exist.',
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'code.unique' => 'A subject with this code already exists.',
            'credits.min' => 'Credit hours must be at least 1.',
            'credits.max' => 'Credit hours cannot exceed 10.',
        ];
    }
}
