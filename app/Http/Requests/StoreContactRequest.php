<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Normalize groups field: convert null, empty string, or invalid types to empty array
        if ($this->has('groups')) {
            $groups = $this->input('groups');
            
            // If it's null or empty string, convert to empty array
            if ($groups === null || $groups === '') {
                $this->merge(['groups' => []]);
            } elseif (!is_array($groups)) {
                // If it's a string that looks like JSON, try to decode it
                if (is_string($groups)) {
                    $decoded = json_decode($groups, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $this->merge(['groups' => $decoded]);
                    } else {
                        // Invalid string format, convert to empty array
                        $this->merge(['groups' => []]);
                    }
                } else {
                    // Invalid type (not array, not string), convert to empty array
                    $this->merge(['groups' => []]);
                }
            }
            // If it's already a valid array, leave it as is
        }
        // If groups is not provided at all, leave it as is (will be null in controller)
    }

    public function rules(): array
    {
        return [
            'id'         => ['nullable', 'integer', 'exists:contacts,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'company'    => ['nullable', 'string', 'max:255'],
            'job_title'  => ['nullable', 'string', 'max:255'],
            'referrer_id'=> ['nullable', 'integer', 'exists:contacts,id'],
            'groups'     => ['nullable', 'array'],
            'groups.*'    => ['string', 'max:100'],
            'address'    => ['nullable', 'string', 'max:500'],
            'notes'      => ['nullable', 'string'],
            'avatar_color' => ['nullable', 'string', 'in:bg-teal,bg-lavender,bg-navy,bg-purple,bg-green,bg-orange,bg-pink,bg-blue'],
        ];
    }
}


