<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    /**
     * Normalize input so survey_steps and survey_fields can be provided
     * as JSON strings or single objects.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('survey_steps')) {
            $steps = $this->input('survey_steps');

            // Decode if JSON string
            if (is_string($steps)) {
                $decoded = json_decode($steps, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $steps = $decoded;
                }
            }

            // Wrap single object to array
            if (is_array($steps) && array_keys($steps) !== range(0, count($steps) - 1)) {
                $steps = [$steps];
            }

            // Ensure survey_fields is array for each step
            if (is_array($steps)) {
                foreach ($steps as $i => $step) {
                    if (isset($step['survey_fields'])) {
                        $fields = $step['survey_fields'];

                        if (is_string($fields)) {
                            $decodedFields = json_decode($fields, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $fields = $decodedFields;
                            }
                        }

                        if (is_array($fields) && array_keys($fields) !== range(0, count($fields) - 1)) {
                            $fields = [$fields];
                        }

                        $steps[$i]['survey_fields'] = $fields;
                    }
                }
            }

            $this->merge(['survey_steps' => $steps]);
        }
    }
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
        return [
            'id' => 'nullable|integer|exists:surveys,id',
            'survey_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Draft,Active,Archived',
            'survey_steps' => 'nullable|array',
            'survey_steps.*.step' => 'nullable|string|max:255',
            'survey_steps.*.tagline' => 'nullable|string',
            'survey_steps.*.order' => 'nullable|integer|min:0',
            'survey_steps.*.survey_fields' => 'nullable|array',
            'survey_steps.*.survey_fields.*.name' => 'nullable|string|max:255',
            'survey_steps.*.survey_fields.*.type' => 'nullable|string|max:255',
            'survey_steps.*.survey_fields.*.description' => 'nullable|string',
            'survey_steps.*.survey_fields.*.is_required' => 'nullable|boolean',
            'survey_steps.*.survey_fields.*.options' => 'nullable|array',
            'survey_steps.*.survey_fields.*.order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom validation messages for survey_steps.
     */
    public function messages(): array
    {
        return [
            'survey_steps.array' => 'The survey steps must be an array.',
            'survey_steps.*.step.required' => 'The step field is required for each survey step.',
            'survey_steps.*.step.string' => 'Each step name must be a string.',
            'survey_steps.*.step.max' => 'Each step name must not exceed 255 characters.',
            'survey_steps.*.tagline.string' => 'Each step tagline must be a string.',
            'survey_steps.*.order.required' => 'The order field is required for each survey step.',
            'survey_steps.*.order.integer' => 'Each step order must be an integer.',
            'survey_steps.*.order.min' => 'Each step order must be at least 0.',
            'survey_steps.*.survey_fields.array' => 'Each step survey fields must be an array.',
            'survey_steps.*.survey_fields.*.name.required' => 'The name field is required for each survey field.',
            'survey_steps.*.survey_fields.*.name.string' => 'Each field name must be a string.',
            'survey_steps.*.survey_fields.*.name.max' => 'Each field name must not exceed 255 characters.',
            'survey_steps.*.survey_fields.*.type.required' => 'The type field is required for each survey field.',
            'survey_steps.*.survey_fields.*.type.string' => 'Each field type must be a string.',
            'survey_steps.*.survey_fields.*.type.max' => 'Each field type must not exceed 255 characters.',
            'survey_steps.*.survey_fields.*.description.string' => 'Each field description must be a string.',
            'survey_steps.*.survey_fields.*.is_required.boolean' => 'Each field is_required must be a boolean.',
            'survey_steps.*.survey_fields.*.options.array' => 'Each field options must be an array.',
            'survey_steps.*.survey_fields.*.order.integer' => 'Each field order must be an integer.',
            'survey_steps.*.survey_fields.*.order.min' => 'Each field order must be at least 0.',
        ];
    }
}
