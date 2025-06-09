<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class BookIssueRequest extends FormRequest
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
        $method = $this->getMethod();
        $issueId = $this->route('bookIssue') ? $this->route('bookIssue')->id : null;

        $rules = [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                function ($attribute, $value, $fail) {
                    if ($this->isMethod('POST')) {
                        $book = \App\Models\Tenant\Book::find($value);
                        if ($book && !$book->isAvailable()) {
                            $fail('This book is not available for issue.');
                        }
                    }
                }
            ],
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
                function ($attribute, $value, $fail) {
                    if ($this->isMethod('POST')) {
                        // Check if student has any overdue books
                        $overdueCount = \App\Models\Tenant\BookIssue::where('student_id', $value)
                            ->where('status', 'issued')
                            ->where('due_date', '<', Carbon::now())
                            ->count();
                        
                        if ($overdueCount > 0) {
                            $fail('This student has overdue books and cannot issue new books.');
                        }

                        // Check maximum books limit (configurable, default 3)
                        $currentIssues = \App\Models\Tenant\BookIssue::where('student_id', $value)
                            ->where('status', 'issued')
                            ->count();
                        
                        $maxBooks = config('library.max_books_per_student', 3);
                        if ($currentIssues >= $maxBooks) {
                            $fail("This student has already issued the maximum number of books ({$maxBooks}).");
                        }
                    }
                }
            ],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'issue_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'due_date' => [
                'required',
                'date',
                'after:issue_date',
                'after_or_equal:today'
            ],
            'return_date' => [
                'nullable',
                'date',
                'after_or_equal:issue_date'
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['issued', 'returned', 'overdue', 'lost', 'renewed'])
            ],
            'fine_amount' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'fine_paid' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'issued_by' => ['nullable', 'integer', 'exists:users,id'],
            'returned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];

        // Additional rules for specific actions
        if ($this->has('action')) {
            switch ($this->action) {
                case 'return':
                    $rules['return_date'] = ['required', 'date', 'after_or_equal:issue_date'];
                    $rules['returned_to'] = ['required', 'integer', 'exists:users,id'];
                    break;
                
                case 'renew':
                    $rules['due_date'] = [
                        'required',
                        'date',
                        'after:' . Carbon::now()->toDateString(),
                        function ($attribute, $value, $fail) {
                            $maxRenewals = config('library.max_renewals', 2);
                            $currentRenewals = $this->route('bookIssue')->renewals ?? 0;
                            if ($currentRenewals >= $maxRenewals) {
                                $fail("This book has already been renewed the maximum number of times ({$maxRenewals}).");
                            }
                        }
                    ];
                    break;
                
                case 'mark_lost':
                    $rules['notes'] = ['required', 'string', 'max:500'];
                    $rules['fine_amount'] = ['required', 'numeric', 'min:0'];
                    break;
            }
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'book_id' => 'book',
            'student_id' => 'student',
            'user_id' => 'user',
            'issue_date' => 'issue date',
            'due_date' => 'due date',
            'return_date' => 'return date',
            'status' => 'issue status',
            'fine_amount' => 'fine amount',
            'fine_paid' => 'fine payment status',
            'notes' => 'notes',
            'issued_by' => 'issued by',
            'returned_to' => 'returned to',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'book_id.required' => 'Please select a book to issue.',
            'book_id.exists' => 'The selected book does not exist.',
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student does not exist.',
            'user_id.exists' => 'The selected user does not exist.',
            'issue_date.required' => 'The issue date is required.',
            'issue_date.date' => 'The issue date must be a valid date.',
            'issue_date.before_or_equal' => 'The issue date cannot be in the future.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after' => 'The due date must be after the issue date.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
            'return_date.date' => 'The return date must be a valid date.',
            'return_date.after_or_equal' => 'The return date cannot be before the issue date.',
            'status.required' => 'The issue status is required.',
            'status.in' => 'The issue status must be one of: issued, returned, overdue, lost, or renewed.',
            'fine_amount.numeric' => 'The fine amount must be a valid amount.',
            'fine_amount.min' => 'The fine amount cannot be negative.',
            'fine_amount.max' => 'The fine amount cannot exceed 9999.99.',
            'fine_paid.boolean' => 'The fine payment status must be true or false.',
            'notes.max' => 'The notes must not exceed 500 characters.',
            'issued_by.exists' => 'The selected issuing staff does not exist.',
            'returned_to.exists' => 'The selected receiving staff does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default issue date to today if not provided
        if (!$this->has('issue_date')) {
            $this->merge(['issue_date' => Carbon::now()->toDateString()]);
        }

        // Set default due date (14 days from issue date) if not provided
        if (!$this->has('due_date') && $this->has('issue_date')) {
            $issueDate = Carbon::parse($this->issue_date);
            $defaultDueDays = config('library.default_due_days', 14);
            $this->merge(['due_date' => $issueDate->addDays($defaultDueDays)->toDateString()]);
        }

        // Set default status to 'issued' for new issues
        if (!$this->has('status') && $this->isMethod('POST')) {
            $this->merge(['status' => 'issued']);
        }

        // Set issued_by to current user if not provided
        if (!$this->has('issued_by') && $this->isMethod('POST')) {
            $this->merge(['issued_by' => auth()->id()]);
        }

        // Set fine_paid to false if not provided
        if (!$this->has('fine_paid')) {
            $this->merge(['fine_paid' => false]);
        }

        // Convert empty strings to null for nullable fields
        $nullableFields = ['user_id', 'return_date', 'fine_amount', 'notes', 'issued_by', 'returned_to'];
        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->$field === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate return date scenarios
            if ($this->has('return_date') && $this->return_date) {
                if ($this->status !== 'returned') {
                    $validator->errors()->add('status', 
                        'Status must be "returned" when return date is provided.');
                }
            }

            // Validate fine scenarios
            if ($this->has('fine_amount') && $this->fine_amount > 0) {
                if (!in_array($this->status, ['overdue', 'returned', 'lost'])) {
                    $validator->errors()->add('fine_amount', 
                        'Fine can only be applied to overdue, returned, or lost books.');
                }
            }

            // Validate lost book scenarios
            if ($this->status === 'lost') {
                if (!$this->has('fine_amount') || $this->fine_amount <= 0) {
                    $validator->errors()->add('fine_amount', 
                        'Fine amount is required for lost books.');
                }
                if (!$this->has('notes') || empty($this->notes)) {
                    $validator->errors()->add('notes', 
                        'Notes are required when marking a book as lost.');
                }
            }

            // Validate renewal scenarios
            if ($this->status === 'renewed') {
                $originalIssue = $this->route('bookIssue');
                if ($originalIssue && $originalIssue->status !== 'issued') {
                    $validator->errors()->add('status', 
                        'Only issued books can be renewed.');
                }
            }

            // Validate due date for renewals
            if ($this->has('action') && $this->action === 'renew') {
                $maxRenewalDays = config('library.max_renewal_days', 14);
                $newDueDate = Carbon::parse($this->due_date);
                $maxAllowedDate = Carbon::now()->addDays($maxRenewalDays);
                
                if ($newDueDate->gt($maxAllowedDate)) {
                    $validator->errors()->add('due_date', 
                        "Due date cannot be more than {$maxRenewalDays} days from today for renewals.");
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

        // Add computed fields
        if ($this->has('action')) {
            $validated['action'] = $this->action;
        }

        return $validated;
    }
}
