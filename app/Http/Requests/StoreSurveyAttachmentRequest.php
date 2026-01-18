<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'id' => ['nullable', 'integer', 'exists:survey_attachments,id'],
        ];

        // If updating (id provided), file is optional
        if ($this->input('id')) {
            $rules['file'] = ['nullable', 'file', 'max:10240']; // 10MB max
        } else {
            // If creating, file is required
            $rules['file'] = ['required', 'file', 'max:10240']; // 10MB max
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'The file field is required.',
            'file.file' => 'The file must be a valid file.',
            'file.max' => 'The file must not be larger than 10MB.',
            'id.integer' => 'The id must be an integer.',
            'id.exists' => 'The selected attachment does not exist.',
        ];
    }
}
