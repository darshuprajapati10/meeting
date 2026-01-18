<?php

namespace App\Http\Requests;

use App\Models\SurveyField;
use App\Models\SurveyStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSurveyStepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Normalize input so survey_fields can be provided as JSON strings or single objects.
     */
    protected function prepareForValidation(): void
    {
        // Log raw input for debugging
        \Log::info('StoreSurveyStepRequest - prepareForValidation', [
            'has_survey_fields' => $this->has('survey_fields'),
            'has_field_values' => $this->has('field_values'),
            'raw_survey_fields' => $this->input('survey_fields'),
            'raw_field_values' => $this->input('field_values'),
        ]);
        
        // Handle is_required conversion from integer (0/1) to boolean
        if ($this->has('survey_fields') && is_array($this->input('survey_fields'))) {
            $fields = $this->input('survey_fields');
            foreach ($fields as $index => $field) {
                if (isset($field['is_required'])) {
                    $fields[$index]['is_required'] = (bool) $field['is_required'];
                }
                // Convert empty string description to null
                if (isset($field['description']) && $field['description'] === '') {
                    $fields[$index]['description'] = null;
                }
            }
            $this->merge(['survey_fields' => $fields]);
        }

        // If survey_fields is missing, don't set it - let 'required' validation catch it
        if (!$this->has('survey_fields')) {
            // Still process field_values even if survey_fields is missing
            if ($this->has('field_values')) {
                $this->normalizeFieldValues();
            }
            return;
        }

        $fields = $this->input('survey_fields');

        // Handle null separately - set to empty array so min:1 fails
        if ($fields === null) {
            $this->merge(['survey_fields' => []]);
            if ($this->has('field_values')) {
                $this->normalizeFieldValues();
            }
            return;
        }

        // Handle empty string
        if ($fields === '') {
            $this->merge(['survey_fields' => []]);
            if ($this->has('field_values')) {
                $this->normalizeFieldValues();
            }
            return;
        }

        // Decode if JSON string
        if (is_string($fields)) {
            $decoded = json_decode($fields, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $fields = $decoded;
            } else {
                // Invalid JSON, set to empty array so validation fails
                $this->merge(['survey_fields' => []]);
                if ($this->has('field_values')) {
                    $this->normalizeFieldValues();
                }
                return;
            }
        }

        // Ensure it's an array
        if (!is_array($fields)) {
            $this->merge(['survey_fields' => []]);
            if ($this->has('field_values')) {
                $this->normalizeFieldValues();
            }
            return;
        }

        // If empty array, keep it empty so validation fails with min:1
        if (empty($fields)) {
            $this->merge(['survey_fields' => []]);
            if ($this->has('field_values')) {
                $this->normalizeFieldValues();
            }
            return;
        }

        // Wrap single object to array
        if (is_array($fields) && array_keys($fields) !== range(0, count($fields) - 1)) {
            $fields = [$fields];
        }

        $this->merge(['survey_fields' => $fields]);
        
        // Normalize field_values
        if ($this->has('field_values')) {
            $this->normalizeFieldValues();
        }
    }

    /**
     * Normalize field_values input
     */
    protected function normalizeFieldValues(): void
    {
        $fieldValues = $this->input('field_values');
        
        \Log::info('normalizeFieldValues - Input', [
            'type' => gettype($fieldValues),
            'value' => $fieldValues,
        ]);
        
        if (is_string($fieldValues)) {
            // Try to decode JSON string
            $decoded = json_decode($fieldValues, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $fieldValues = $decoded;
            } else {
                // Try to fix common JSON syntax errors
                $fixed = $this->fixInvalidJsonFormat($fieldValues);
                if ($fixed !== null) {
                    $fieldValues = $fixed;
                    \Log::info('normalizeFieldValues - Fixed invalid JSON format', [
                        'original' => $this->input('field_values'),
                        'fixed' => $fixed,
                    ]);
                } else {
                    \Log::warning('normalizeFieldValues - JSON decode failed', [
                        'error' => json_last_error_msg(),
                        'input' => $fieldValues,
                    ]);
                    $fieldValues = [];
                }
            }
        }
        
        // Handle case where field_values is an array with a single object (wrong format from frontend)
        if (is_array($fieldValues) && count($fieldValues) === 1 && isset($fieldValues[0]) && is_array($fieldValues[0])) {
            // Convert [{325: "value"}] to {325: "value"}
            $fieldValues = $fieldValues[0];
            \Log::info('normalizeFieldValues - Converted array format to object', [
                'converted' => $fieldValues,
            ]);
        }
        
        // Ensure it's an array/object (associative array for field_values)
        if (!is_array($fieldValues)) {
            $fieldValues = [];
        }
        
        \Log::info('normalizeFieldValues - Output', [
            'type' => gettype($fieldValues),
            'value' => $fieldValues,
        ]);
        
        $this->merge(['field_values' => $fieldValues]);
    }

    /**
     * Try to fix common invalid JSON formats for field_values
     */
    protected function fixInvalidJsonFormat(string $input): ?array
    {
        $input = trim($input);
        
        // Handle array format with unquoted keys: [{325: "value"}] or [325: "value"]
        if (preg_match('/^\[?\s*\{?\s*(\d+)\s*:\s*"([^"]+)"\s*\}?\s*\]?$/', $input, $matches)) {
            $fieldId = $matches[1];
            $value = $matches[2];
            return [(string)$fieldId => $value];
        }
        
        // Handle object format with unquoted keys: {325: "value"}
        if (preg_match('/^\{?\s*(\d+)\s*:\s*"([^"]+)"\s*\}?$/', $input, $matches)) {
            $fieldId = $matches[1];
            $value = $matches[2];
            return [(string)$fieldId => $value];
        }
        
        // Handle multiple key-value pairs: {325: "value1", 326: "value2"}
        if (preg_match_all('/(\d+)\s*:\s*"([^"]+)"/', $input, $matches, PREG_SET_ORDER)) {
            $result = [];
            foreach ($matches as $match) {
                $result[(string)$match[1]] = $match[2];
            }
            return $result;
        }
        
        // Try to fix by adding quotes around unquoted keys
        $fixed = preg_replace('/(\d+)\s*:/', '"$1":', $input);
        $decoded = json_decode($fixed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        
        return null;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:survey_steps,id'],
            'survey_id' => ['required', 'integer', 'exists:surveys,id'],
            'step' => ['required', 'string', 'max:255'],
          //  'tagline' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer'],
            'meeting_id' => ['nullable', 'integer', 'exists:meetings,id'],
            'submission_id' => ['nullable', 'string', 'max:100'],
            'survey_fields' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (empty($value) || (is_array($value) && count($value) < 1)) {
                        $fail('The survey fields must have at least 1 field.');
                    }
                },
            ],
            'survey_fields.*.name' => ['required', 'string', 'max:255'],
            'survey_fields.*.type' => ['required', 'string', 'max:255'],
            'survey_fields.*.description' => [
                'nullable',
                'string',
            ],
            'survey_fields.*.is_required' => ['required', 'boolean'],
            'survey_fields.*.options' => ['nullable', 'array'],
            'survey_fields.*.order' => ['nullable', 'integer', 'min:0'],
            
            // Add field_values validation
            'field_values' => ['nullable', 'array'],
            'field_values.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Extract field ID from attribute (e.g., "field_values.241" -> 241)
                    $parts = explode('.', $attribute);
                    if (count($parts) >= 2) {
                        $fieldId = $parts[1];
                        
                        // Convert field ID to integer for database lookup (handles both string and integer IDs)
                        $fieldIdInt = (int) $fieldId;
                        
                        // Get field definition from database
                        $field = SurveyField::find($fieldIdInt);
                        
                        // If field doesn't exist, skip validation (controller will handle it)
                        if (!$field) {
                            return;
                        }
                        
                        // Check if field is required
                        if ($field->is_required && (empty($value) || $value === null || (is_string($value) && trim($value) === ''))) {
                            $fail("{$field->type} Field is required.");
                            return;
                        }
                        
                        // Skip validation if value is empty and field is not required
                        if (empty($value) || $value === null || (is_string($value) && trim($value) === '')) {
                            return;
                        }
                        
                        // Validate based on field type
                        $fieldType = $field->type;
                        
                        switch ($fieldType) {
                            case 'Email':
                                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                    $fail("The {$field->name} must be a valid email address.");
                                }
                                break;
                                
                            case 'Number':
                                if (!is_numeric($value)) {
                                    $fail("The {$field->name} must be a number.");
                                }
                                break;
                                
                            case 'Date':
                                // Validate YYYY-MM-DD format
                                $date = \DateTime::createFromFormat('Y-m-d', $value);
                                if (!$date || $date->format('Y-m-d') !== $value) {
                                    $fail("The {$field->name} must be a valid date in YYYY-MM-DD format.");
                                }
                                break;
                                
                            case 'Dropdown':
                            case 'Multiple Choice':
                                // Validate that selected option is in options list
                                $options = $field->options ?? [];
                                if (empty($options)) {
                                    break; // No options to validate against
                                }
                                if (!in_array($value, $options)) {
                                    $fail("The selected option for {$field->name} is invalid.");
                                }
                                break;
                                
                            case 'Checkboxes':
                                // Validate that all selected options are in options list
                                if (!is_array($value)) {
                                    $fail("The {$field->name} must be an array.");
                                    return;
                                }
                                $options = $field->options ?? [];
                                if (empty($options)) {
                                    break; // No options to validate against
                                }
                                foreach ($value as $selectedOption) {
                                    if (!in_array($selectedOption, $options)) {
                                        $fail("The selected option '{$selectedOption}' for {$field->name} is invalid.");
                                        return;
                                    }
                                }
                                break;
                                
                            case 'Rating Scale':
                                // Validate rating is between 1-5
                                $rating = (int) $value;
                                if ($rating < 1 || $rating > 5) {
                                    $fail("The {$field->name} must be between 1 and 5.");
                                }
                                break;
                                
                            case 'Short Answer':
                            case 'Paragraph':
                                // Just ensure it's a string (already handled by nullable/string rules)
                                break;
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Configure the validator instance to check for missing required fields.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $surveyId = $this->input('survey_id');
            if (!$surveyId) {
                return; // survey_id validation will catch this
            }
            
            // Get the survey step
            $stepId = $this->input('id');
            $surveyStep = null;
            
            if ($stepId) {
                // If updating, get by ID
                $surveyStep = SurveyStep::where('id', $stepId)
                    ->where('survey_id', $surveyId)
                    ->first();
            } else {
                // If creating a new step, skip field_values validation
                // because the step and its fields don't exist yet
                // field_values will be validated and saved after step creation
                return;
            }
            
            // If step not found, skip this validation (other validations will catch it)
            if (!$surveyStep) {
                return;
            }
            
            // Get all required fields for this step (from database - saved via /survey/save API)
            $requiredFields = SurveyField::where('survey_step_id', $surveyStep->id)
                ->where('is_required', true)
                ->get();
            
            // Get field_values from request
            $fieldValues = $this->input('field_values', []);
            
            // Check if all required fields are present and not empty
            foreach ($requiredFields as $field) {
                $fieldId = $field->id;
                $fieldIdString = (string) $fieldId;
                $fieldIdInt = (int) $fieldId;
                
                // Check both string and integer key formats (frontend may send either)
                $value = null;
                if (isset($fieldValues[$fieldIdString])) {
                    $value = $fieldValues[$fieldIdString];
                } elseif (isset($fieldValues[$fieldIdInt])) {
                    $value = $fieldValues[$fieldIdInt];
                } elseif (isset($fieldValues[$fieldId])) {
                    $value = $fieldValues[$fieldId];
                }
                
                // Check if required field is missing or empty
                if ($value === null || 
                    $value === '' ||
                    (is_string($value) && trim($value) === '') ||
                    (is_array($value) && empty($value))) {
                    
                    // Add validation error for missing required field (use string format for consistency)
                    $validator->errors()->add(
                        "field_values.{$fieldIdString}",
                        "{$field->type} Field is required."
                    );
                }
            }
        });
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            // id field validation messages
            'id.integer' => 'The survey step id must be an integer.',
            'id.exists' => 'The selected survey step id does not exist.',
            
            // survey_id field validation messages
            'survey_id.required' => 'The survey id field is required.',
            'survey_id.integer' => 'The survey id must be an integer.',
            'survey_id.exists' => 'The selected survey id does not exist.',
            
            // step field validation messages
            'step.required' => 'The step field is required.',
            'step.string' => 'The step must be a string.',
            'step.max' => 'The step must not exceed 255 characters.',
            
            // tagline field validation messages
            'tagline.required' => 'The tagline field is required.',
            'tagline.string' => 'The tagline must be a string.',
            'tagline.max' => 'The tagline must not exceed 255 characters.',
            
            // order field validation messages
            'order.required' => 'The order field is required.',
            'order.integer' => 'The order must be an integer.',
            
            // survey_fields array validation messages
            'survey_fields.required' => 'The survey fields field is required.',
            'survey_fields.array' => 'The survey fields must be an array.',
            'survey_fields.min' => 'The survey fields must have at least 1 field.',
            
            // survey_fields.*.name validation messages
            'survey_fields.*.name.required' => 'The name field is required.',
            'survey_fields.*.name.string' => 'Each field name must be a string.',
            'survey_fields.*.name.max' => 'Each field name must not exceed 255 characters.',
            
            // survey_fields.*.type validation messages
            'survey_fields.*.type.required' => 'The type field is required.',
            'survey_fields.*.type.string' => 'Each field type must be a string.',
            'survey_fields.*.type.max' => 'Each field type must not exceed 255 characters.',
            
            // survey_fields.*.description validation messages
            'survey_fields.*.description.string' => 'Each field description must be a string.',
            
            // survey_fields.*.is_required validation messages
            'survey_fields.*.is_required.required' => 'The is_required field is required for each survey field.',
            'survey_fields.*.is_required.boolean' => 'Each field is_required must be a boolean.',
            
            // survey_fields.*.options validation messages
            'survey_fields.*.options.array' => 'Each field options must be an array.',
            
            // survey_fields.*.order validation messages
            'survey_fields.*.order.integer' => 'Each field order must be an integer.',
            'survey_fields.*.order.min' => 'Each field order must be at least 0.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $formattedErrors = [];
        
        // Format errors to match Flutter expectations
        foreach ($errors->messages() as $key => $messages) {
            // If error is for field_values, format as field ID with "field_" prefix
            if (strpos($key, 'field_values.') === 0) {
                $fieldId = str_replace('field_values.', '', $key);
                // Only add with "field_" prefix (e.g., "field_286")
                $formattedErrors["field_{$fieldId}"] = $messages;
            } else {
                $formattedErrors[$key] = $messages;
            }
        }
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $formattedErrors,
            ], 422)
        );
    }
}

