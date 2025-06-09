<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
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
        $bookId = $this->route('book') ? $this->route('book')->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('books', 'isbn')->ignore($bookId)->whereNull('deleted_at')
            ],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 1)],
            'category' => ['required', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'total_copies' => ['required', 'integer', 'min:1', 'max:999'],
            'available_copies' => ['nullable', 'integer', 'min:0', 'max:999'],
            'language' => ['nullable', 'string', 'max:50'],
            'edition' => ['nullable', 'string', 'max:50'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'lost', 'damaged'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'book title',
            'isbn' => 'ISBN',
            'author' => 'author name',
            'publisher' => 'publisher name',
            'publication_year' => 'publication year',
            'category' => 'book category',
            'subject' => 'subject',
            'description' => 'book description',
            'total_copies' => 'total copies',
            'available_copies' => 'available copies',
            'language' => 'language',
            'edition' => 'edition',
            'pages' => 'number of pages',
            'price' => 'book price',
            'location' => 'shelf location',
            'status' => 'book status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The book title is required.',
            'title.max' => 'The book title must not exceed 255 characters.',
            'isbn.unique' => 'This ISBN is already registered for another book.',
            'isbn.max' => 'The ISBN must not exceed 20 characters.',
            'author.required' => 'The author name is required.',
            'author.max' => 'The author name must not exceed 255 characters.',
            'publisher.max' => 'The publisher name must not exceed 255 characters.',
            'publication_year.integer' => 'The publication year must be a valid year.',
            'publication_year.min' => 'The publication year must be at least 1800.',
            'publication_year.max' => 'The publication year cannot be more than next year.',
            'category.required' => 'The book category is required.',
            'category.max' => 'The book category must not exceed 100 characters.',
            'subject.max' => 'The subject must not exceed 100 characters.',
            'description.max' => 'The description must not exceed 1000 characters.',
            'total_copies.required' => 'The total number of copies is required.',
            'total_copies.integer' => 'The total copies must be a valid number.',
            'total_copies.min' => 'The total copies must be at least 1.',
            'total_copies.max' => 'The total copies cannot exceed 999.',
            'available_copies.integer' => 'The available copies must be a valid number.',
            'available_copies.min' => 'The available copies cannot be negative.',
            'available_copies.max' => 'The available copies cannot exceed 999.',
            'language.max' => 'The language must not exceed 50 characters.',
            'edition.max' => 'The edition must not exceed 50 characters.',
            'pages.integer' => 'The number of pages must be a valid number.',
            'pages.min' => 'The number of pages must be at least 1.',
            'pages.max' => 'The number of pages cannot exceed 9999.',
            'price.numeric' => 'The price must be a valid amount.',
            'price.min' => 'The price cannot be negative.',
            'price.max' => 'The price cannot exceed 99999.99.',
            'location.max' => 'The location must not exceed 100 characters.',
            'status.required' => 'The book status is required.',
            'status.in' => 'The book status must be one of: active, inactive, lost, or damaged.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set available_copies to total_copies if not provided (for new books)
        if (!$this->has('available_copies') && $this->has('total_copies')) {
            $this->merge([
                'available_copies' => $this->total_copies
            ]);
        }

        // Ensure available_copies doesn't exceed total_copies
        if ($this->has('available_copies') && $this->has('total_copies')) {
            if ($this->available_copies > $this->total_copies) {
                $this->merge([
                    'available_copies' => $this->total_copies
                ]);
            }
        }

        // Convert empty strings to null for nullable fields
        $nullableFields = ['isbn', 'publisher', 'publication_year', 'subject', 'description', 
                          'available_copies', 'language', 'edition', 'pages', 'price', 'location'];
        
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
            // Validate that available_copies doesn't exceed total_copies
            if ($this->has('available_copies') && $this->has('total_copies')) {
                if ($this->available_copies > $this->total_copies) {
                    $validator->errors()->add('available_copies', 
                        'Available copies cannot exceed total copies.');
                }
            }

            // Validate ISBN format if provided
            if ($this->has('isbn') && $this->isbn) {
                $isbn = preg_replace('/[^0-9X]/', '', strtoupper($this->isbn));
                if (!$this->isValidISBN($isbn)) {
                    $validator->errors()->add('isbn', 'The ISBN format is invalid.');
                }
            }
        });
    }

    /**
     * Validate ISBN format (ISBN-10 or ISBN-13).
     */
    private function isValidISBN(string $isbn): bool
    {
        if (strlen($isbn) === 10) {
            return $this->isValidISBN10($isbn);
        } elseif (strlen($isbn) === 13) {
            return $this->isValidISBN13($isbn);
        }
        
        return false;
    }

    /**
     * Validate ISBN-10 format.
     */
    private function isValidISBN10(string $isbn): bool
    {
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            if (!is_numeric($isbn[$i])) {
                return false;
            }
            $sum += (int)$isbn[$i] * (10 - $i);
        }
        
        $checkDigit = $isbn[9];
        $remainder = $sum % 11;
        $expectedCheckDigit = ($remainder === 0) ? '0' : (($remainder === 1) ? 'X' : (string)(11 - $remainder));
        
        return $checkDigit === $expectedCheckDigit;
    }

    /**
     * Validate ISBN-13 format.
     */
    private function isValidISBN13(string $isbn): bool
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            if (!is_numeric($isbn[$i])) {
                return false;
            }
            $sum += (int)$isbn[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        
        $checkDigit = (int)$isbn[12];
        $expectedCheckDigit = (10 - ($sum % 10)) % 10;
        
        return $checkDigit === $expectedCheckDigit;
    }
}
