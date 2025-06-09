<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class NoticeRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => [
                'required',
                'string',
                Rule::in([
                    'general', 'academic', 'event', 'holiday', 'exam', 
                    'fee', 'admission', 'sports', 'cultural', 'maintenance'
                ])
            ],
            'priority' => [
                'required',
                'string',
                Rule::in(['low', 'medium', 'high', 'urgent'])
            ],
            'target_audience' => [
                'required',
                'string',
                Rule::in(['all', 'students', 'teachers', 'parents', 'staff', 'specific_classes'])
            ],
            'class_ids' => [
                'required_if:target_audience,specific_classes',
                'array',
                'min:1'
            ],
            'class_ids.*' => ['integer', 'exists:classes,id'],
            'is_published' => ['boolean'],
            'published_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
                function ($attribute, $value, $fail) {
                    if ($this->boolean('is_published') && !$value) {
                        // If is_published is true and no published_at is provided, it's immediate publication
                        return;
                    }
                    if ($value && Carbon::parse($value)->isPast()) {
                        $fail('The publication date cannot be in the past.');
                    }
                }
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    if ($value && $this->has('published_at') && $this->published_at) {
                        $publishedAt = Carbon::parse($this->published_at);
                        $expiresAt = Carbon::parse($value);
                        if ($expiresAt->lte($publishedAt)) {
                            $fail('The expiry date must be after the publication date.');
                        }
                    }
                }
            ],
            'is_urgent' => ['boolean'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,rar'
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'notice title',
            'content' => 'notice content',
            'type' => 'notice type',
            'priority' => 'priority level',
            'target_audience' => 'target audience',
            'class_ids' => 'selected classes',
            'class_ids.*' => 'class',
            'is_published' => 'publication status',
            'published_at' => 'publication date',
            'expires_at' => 'expiry date',
            'is_urgent' => 'urgent status',
            'attachment' => 'attachment file',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The notice title is required.',
            'title.max' => 'The notice title must not exceed 255 characters.',
            'content.required' => 'The notice content is required.',
            'type.required' => 'The notice type is required.',
            'type.in' => 'The selected notice type is invalid.',
            'priority.required' => 'The priority level is required.',
            'priority.in' => 'The selected priority level is invalid.',
            'target_audience.required' => 'The target audience is required.',
            'target_audience.in' => 'The selected target audience is invalid.',
            'class_ids.required_if' => 'Please select at least one class when targeting specific classes.',
            'class_ids.array' => 'The selected classes must be an array.',
            'class_ids.min' => 'Please select at least one class.',
            'class_ids.*.integer' => 'Each selected class must be a valid ID.',
            'class_ids.*.exists' => 'One or more selected classes do not exist.',
            'is_published.boolean' => 'The publication status must be true or false.',
            'published_at.date' => 'The publication date must be a valid date.',
            'published_at.after_or_equal' => 'The publication date cannot be in the past.',
            'expires_at.date' => 'The expiry date must be a valid date.',
            'expires_at.after' => 'The expiry date must be in the future.',
            'is_urgent.boolean' => 'The urgent status must be true or false.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.max' => 'The attachment file size must not exceed 10MB.',
            'attachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png, gif, txt, zip, rar.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        if (!$this->has('is_published')) {
            $this->merge(['is_published' => false]);
        }

        if (!$this->has('is_urgent')) {
            $this->merge(['is_urgent' => false]);
        }

        // If urgent is true, set priority to urgent
        if ($this->boolean('is_urgent')) {
            $this->merge(['priority' => 'urgent']);
        }

        // Clean up class_ids if target_audience is not specific_classes
        if ($this->target_audience !== 'specific_classes') {
            $this->merge(['class_ids' => null]);
        }

        // Convert empty strings to null for nullable fields
        $nullableFields = ['published_at', 'expires_at'];
        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->$field === '') {
                $this->merge([$field => null]);
            }
        }

        // If is_published is true and no published_at is provided, set it to now
        if ($this->boolean('is_published') && !$this->has('published_at')) {
            $this->merge(['published_at' => Carbon::now()->toDateTimeString()]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that urgent notices have high priority
            if ($this->boolean('is_urgent') && $this->priority !== 'urgent') {
                $validator->errors()->add('priority', 
                    'Urgent notices must have urgent priority.');
            }

            // Validate content length (minimum 10 characters)
            if ($this->has('content') && strlen(strip_tags($this->content)) < 10) {
                $validator->errors()->add('content', 
                    'The notice content must be at least 10 characters long.');
            }

            // Validate scheduled publication logic
            if ($this->boolean('is_published') && $this->has('published_at') && $this->published_at) {
                $publishedAt = Carbon::parse($this->published_at);
                if ($publishedAt->isFuture() && $this->boolean('is_urgent')) {
                    $validator->errors()->add('is_urgent', 
                        'Urgent notices cannot be scheduled for future publication.');
                }
            }

            // Validate class_ids uniqueness
            if ($this->has('class_ids') && is_array($this->class_ids)) {
                $uniqueClassIds = array_unique($this->class_ids);
                if (count($uniqueClassIds) !== count($this->class_ids)) {
                    $validator->errors()->add('class_ids', 
                        'Duplicate classes are not allowed.');
                }
            }
        });
    }

    /**
     * Get validated data with computed fields.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Ensure class_ids is null if not targeting specific classes
        if ($validated['target_audience'] !== 'specific_classes') {
            $validated['class_ids'] = null;
        }

        return $validated;
    }
}
