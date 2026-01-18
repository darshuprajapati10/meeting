<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // If 'contacts' array is not provided, but individual contact fields are at the root,
        // wrap them into a 'contacts' array for validation.
        if (!$this->has('contacts') && ($this->has('first_name') || $this->has('last_name'))) {
            $this->merge([
                'contacts' => [
                    [
                        'first_name' => $this->input('first_name'),
                        'last_name' => $this->input('last_name'),
                        'email' => $this->input('email'),
                        'phone' => $this->input('phone'),
                        'company' => $this->input('company'),
                        'job_title' => $this->input('job_title'),
                        'groups' => $this->input('groups'),
                    ]
                ]
            ]);
        }

        // Normalize 'groups' field for each contact
        $contacts = $this->input('contacts');
        if (is_array($contacts)) {
            foreach ($contacts as $index => $contact) {
                if (isset($contact['groups'])) {
                    $groups = $contact['groups'];
                    if ($groups === null || $groups === '') {
                        $contacts[$index]['groups'] = [];
                    } elseif (is_string($groups)) {
                        $decoded = json_decode($groups, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $contacts[$index]['groups'] = $decoded;
                        } else {
                            $contacts[$index]['groups'] = [];
                        }
                    } elseif (!is_array($groups)) {
                        $contacts[$index]['groups'] = [];
                    }
                }
            }
            $this->merge(['contacts' => $contacts]);
        }
    }

    public function rules(): array
    {
        return [
            'contacts' => ['required', 'array', 'min:1'],
            'allow_duplicates' => ['required', 'boolean'],
            'update_existing' => ['required', 'boolean'],
            'contacts.*.first_name' => ['required', 'string', 'max:255'],
            'contacts.*.last_name' => ['required', 'string', 'max:255'],
            'contacts.*.phone' => ['required', 'string', 'max:50'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.company' => ['nullable', 'string', 'max:255'],
            'contacts.*.job_title' => ['nullable', 'string', 'max:255'],
            'contacts.*.groups' => ['nullable', 'array'],
            'contacts.*.groups.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'contacts.required' => 'The contacts field is required.',
            'contacts.array' => 'The contacts must be an array.',
            'contacts.min' => 'The contacts must contain at least one contact.',
            'allow_duplicates.required' => 'The allow_duplicates field is required.',
            'allow_duplicates.boolean' => 'The allow_duplicates field must be a boolean.',
            'update_existing.required' => 'The update_existing field is required.',
            'update_existing.boolean' => 'The update_existing field must be a boolean.',
            'contacts.*.first_name.required' => 'The first name field is required for each contact.',
            'contacts.*.last_name.required' => 'The last name field is required for each contact.',
            'contacts.*.phone.required' => 'The phone field is required for each contact.',
            'contacts.*.email.email' => 'The email must be a valid email address for each contact.',
            'contacts.*.email.max' => 'The email may not be greater than 255 characters for each contact.',
            'contacts.*.phone.max' => 'The phone may not be greater than 50 characters for each contact.',
            'contacts.*.company.max' => 'The company may not be greater than 255 characters for each contact.',
            'contacts.*.job_title.max' => 'The job title may not be greater than 255 characters for each contact.',
            'contacts.*.groups.array' => 'The groups must be an array for each contact.',
            'contacts.*.groups.*.string' => 'Each group name must be a string.',
            'contacts.*.groups.*.max' => 'Each group name may not be greater than 50 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
