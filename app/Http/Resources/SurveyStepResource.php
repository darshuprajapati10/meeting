<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyStepResource extends JsonResource
{
    public $meetingId;


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;
        $userResponses = [];

        // If meeting_id is provided, fetch user responses for this meeting
        if ($this->meetingId && $userId && $this->survey_id) {
            // First, try to get responses from SurveyResponse table (has meeting_id)
            $surveyResponse = \App\Models\SurveyResponse::where('survey_id', $this->survey_id)
                ->where('meeting_id', $this->meetingId)
                ->where('user_id', $userId)
                ->first();

            if ($surveyResponse && $surveyResponse->response_data) {
                // Extract responses from response_data JSON field
                $responseData = is_array($surveyResponse->response_data) 
                    ? $surveyResponse->response_data 
                    : json_decode($surveyResponse->response_data, true);
                
                if (is_array($responseData)) {
                    foreach ($responseData as $fieldId => $value) {
                        $userResponses[(string)$fieldId] = $value;
                    }
                }
            }

            // Fallback: Also check SurveyFieldValue table using submission_id
            // Get the submission_id from SurveySubmission if it exists
            $submission = \App\Models\SurveySubmission::where('survey_id', $this->survey_id)
                ->where('meeting_id', $this->meetingId)
                ->where('user_id', $userId)
                ->first();

            // If we have a submission or if response_data didn't have all fields, check field values
            // Get field values for this step's fields
            if ($this->relationLoaded('surveyFields') && $this->surveyFields->isNotEmpty()) {
                $fieldIds = $this->surveyFields->pluck('id')->toArray();
                
                $fieldValuesQuery = \App\Models\SurveyFieldValue::where('survey_id', $this->survey_id)
                    ->where('survey_step_id', $this->id)
                    ->where('user_id', $userId)
                    ->whereIn('survey_field_id', $fieldIds);
                
                // If we have a submission_id, filter by it (for more accurate results)
                // Otherwise, get the most recent values
                if ($submission) {
                    // Note: submission_id might not directly link, so we'll get latest values
                    // Order by created_at desc to get most recent
                    $fieldValuesQuery->orderBy('created_at', 'desc');
                }
                
                $fieldValues = $fieldValuesQuery->get();
                
                // Group by field_id and get the most recent value for each field
                // Since we ordered by created_at desc, the first occurrence is the most recent
                $seenFieldIds = [];
                foreach ($fieldValues as $fv) {
                    $fieldId = (string)$fv->survey_field_id;
                    // Only add if we don't already have a value from response_data
                    // and we haven't processed this field yet (most recent first due to ordering)
                    if (!isset($userResponses[$fieldId]) && !isset($seenFieldIds[$fieldId])) {
                        // Try to decode JSON, otherwise return as string
                        $decoded = json_decode($fv->value, true);
                        $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $fv->value;
                        $userResponses[$fieldId] = $value;
                        $seenFieldIds[$fieldId] = true;
                    }
                }
            }
        }

        // Build fields array with user_response included
        $fields = [];
        if ($this->relationLoaded('surveyFields')) {
            $fields = $this->surveyFields->map(function ($field) use ($userResponses) {
                $fieldArray = [
                    'id' => $field->id,
                    'name' => $field->name,
                    'type' => $field->type,
                    'description' => $field->description,
                    'is_required' => $field->is_required,
                    'options' => $field->options ?? [],
                ];

                // Add user_response if available
                $fieldId = (string)$field->id;
                if (isset($userResponses[$fieldId])) {
                    $responseValue = $userResponses[$fieldId];
                    
                    // Handle different data types based on field type
                    $fieldType = strtolower($field->type ?? '');
                    
                    // For rating/number fields, ensure it's returned as number
                    if (in_array($fieldType, ['rating', 'rating scale', 'number'])) {
                        $fieldArray['user_response'] = is_numeric($responseValue) ? (int)$responseValue : $responseValue;
                    } 
                    // For date fields, return as string (already formatted)
                    elseif (in_array($fieldType, ['date'])) {
                        $fieldArray['user_response'] = (string)$responseValue;
                    }
                    // For checkboxes, ensure it's an array
                    elseif (in_array($fieldType, ['checkboxes', 'checkbox'])) {
                        if (is_string($responseValue)) {
                            $decoded = json_decode($responseValue, true);
                            $fieldArray['user_response'] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) 
                                ? $decoded 
                                : [$responseValue];
                        } else {
                            $fieldArray['user_response'] = is_array($responseValue) ? $responseValue : [$responseValue];
                        }
                    }
                    // For other types, return as-is (string or array)
                    else {
                        $fieldArray['user_response'] = $responseValue;
                    }
                } else {
                    $fieldArray['user_response'] = null;
                }

                return $fieldArray;
            })->values()->all();
        }
        
        // Load field values for current user (legacy support - for when meeting_id is not provided)
        $fieldValues = new \stdClass(); // Use object to force JSON object encoding
        if ($userId && !$this->meetingId) {
            $values = \App\Models\SurveyFieldValue::where('survey_step_id', $this->id)
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Format as {field_id: value} - use object properties to ensure JSON object encoding
            // Get only the most recent value for each field
            $seenFields = [];
            foreach ($values as $fv) {
                $fieldId = (string)$fv->survey_field_id;
                // Only add if we haven't seen this field yet (most recent first)
                if (!isset($seenFields[$fieldId])) {
                    // Try to decode JSON, otherwise return as string
                    $decoded = json_decode($fv->value, true);
                    $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $fv->value;
                    
                    // Set as object property to ensure JSON object encoding
                    $fieldValues->$fieldId = $value;
                    $seenFields[$fieldId] = true;
                }
            }
        }
        
        return [
            'id' => $this->id,
            'survey_id' => $this->survey_id,
            'step' => $this->step,
            'tagline' => $this->tagline,
            'order' => $this->order ?? null,
            'survey_fields' => $fields,
            'field_values' => $fieldValues, // Legacy support
        ];
    }
}

