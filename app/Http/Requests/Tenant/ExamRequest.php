<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamRequest extends FormRequest
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
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:written,oral,practical,online',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'total_marks' => 'required|integer|min:1|max:1000',
            'passing_marks' => 'required|integer|min:1|lte:total_marks',
            'instructions' => 'nullable|string|max:2000',
            'is_published' => 'boolean',
        ];

        // For update requests, allow exam_date to be today or future
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['exam_date'] = 'required|date|after_or_equal:today';
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The exam title is required.',
            'title.max' => 'The exam title may not be greater than 255 characters.',
            'type.required' => 'The exam type is required.',
            'type.in' => 'The exam type must be one of: written, oral, practical, online.',
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'subject_id.required' => 'The subject is required.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'exam_date.required' => 'The exam date is required.',
            'exam_date.after' => 'The exam date must be a future date.',
            'exam_date.after_or_equal' => 'The exam date must be today or a future date.',
            'start_time.required' => 'The exam start time is required.',
            'start_time.date_format' => 'The start time must be in HH:MM format.',
            'end_time.required' => 'The exam end time is required.',
            'end_time.date_format' => 'The end time must be in HH:MM format.',
            'end_time.after' => 'The end time must be after the start time.',
            'duration_minutes.required' => 'The exam duration is required.',
            'duration_minutes.integer' => 'The exam duration must be a number.',
            'duration_minutes.min' => 'The exam duration must be at least 15 minutes.',
            'duration_minutes.max' => 'The exam duration may not be greater than 480 minutes (8 hours).',
            'total_marks.required' => 'The total marks is required.',
            'total_marks.integer' => 'The total marks must be a number.',
            'total_marks.min' => 'The total marks must be at least 1.',
            'total_marks.max' => 'The total marks may not be greater than 1000.',
            'passing_marks.required' => 'The passing marks is required.',
            'passing_marks.integer' => 'The passing marks must be a number.',
            'passing_marks.min' => 'The passing marks must be at least 1.',
            'passing_marks.lte' => 'The passing marks may not be greater than total marks.',
            'instructions.max' => 'The instructions may not be greater than 2000 characters.',
        ];
    }

    /**
     * Get custom validation attributes.
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'subject_id' => 'subject',
            'exam_date' => 'exam date',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'duration_minutes' => 'duration',
            'total_marks' => 'total marks',
            'passing_marks' => 'passing marks',
            'is_published' => 'published status',
        ];
    }
}
