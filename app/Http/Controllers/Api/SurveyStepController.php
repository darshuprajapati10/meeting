<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SurveyStepRepository;
use App\Http\Requests\StoreSurveyStepRequest;
use App\Http\Resources\SurveyStepResource;
use App\Http\Resources\SurveyResource;
use App\Models\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyField;
use App\Models\SurveyFieldValue;
use App\Models\SurveyResponse;
use App\Models\SurveySubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyStepController extends Controller
{
    protected $surveyStepRepository;

    public function __construct(SurveyStepRepository $surveyStepRepository)
    {
        $this->surveyStepRepository = $surveyStepRepository;
    }

    /**
     * Get survey with all steps and fields
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Validate survey_id (required)
        $request->validate([
            'survey_id' => 'required|integer|exists:surveys,id',
        ], [
            'survey_id.required' => 'The survey id field is required.',
            'survey_id.integer' => 'The survey id must be an integer.',
            'survey_id.exists' => 'The selected survey id does not exist.',
        ]);

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create an organization first.',
            ], 404);
        }

        $organizationId = $organization->id;
        $surveyId = $request->input('survey_id');

        // Verify survey belongs to user's organization
        $survey = Survey::where('id', $surveyId)
            ->where('organization_id', $organizationId)
            ->first();

        if (!$survey) {
            return response()->json([
                'message' => 'Survey not found or you do not have permission to access it.',
            ], 404);
        }

        // Load survey with all steps and fields
        $survey->load(['surveySteps.surveyFields']);

        return response()->json([
            'data' => new SurveyResource($survey),
            'message' => 'Survey steps retrieved successfully.',
        ]);
    }

    /**
     * Get survey with all steps and fields by survey step ID
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided, meeting_id is optional
        $request->validate([
            'id' => 'required|integer|exists:survey_steps,id',
            'meeting_id' => 'nullable|integer|exists:meetings,id',
        ]);

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create an organization first.',
            ], 404);
        }

        $organizationId = $organization->id;
        $surveyStepId = $request->id;
        $meetingId = $request->input('meeting_id');

        // Get survey step and verify it belongs to user's organization
        $surveyStep = SurveyStep::where('id', $surveyStepId)
            ->whereHas('survey', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->with(['survey', 'surveyFields'])
            ->firstOrFail();

        // If meeting_id is provided, verify the meeting belongs to the organization
        if ($meetingId) {
            $meeting = \App\Models\Meeting::where('id', $meetingId)
                ->where('organization_id', $organizationId)
                ->first();
            
            if (!$meeting) {
                return response()->json([
                    'message' => 'Meeting not found or you do not have permission to access it.',
                ], 404);
            }
        }

        // Pass meeting_id to the resource so it can include user responses
        $resource = new SurveyStepResource($surveyStep);
        $resource->meetingId = $meetingId; // Set meetingId directly
        
        return response()->json([
            'success' => true,
            'data' => $resource,
            'message' => 'Survey step retrieved successfully.',
        ]);
    }

    /**
     * Create or update a survey step
     */
    public function save(StoreSurveyStepRequest $request)
    {
        try {
            $user = $request->user();
            
            // Log raw request for debugging
            \Log::info('Survey Step Save - Raw Request', [
                'user_id' => $user->id ?? null,
                'raw_input' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // Get organization_id from user's organizations
            $organization = $user->organizations()->first();
            
            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'No organization found. Please create an organization first.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $organizationId = $organization->id;

            DB::beginTransaction();
            
            $data = $request->validated();
            
            // Log validated data for debugging
            \Log::info('Survey Step Save - Validated Data', [
                'user_id' => $user->id,
                'data' => $data,
            ]);

            // Get survey_id from validated data (validation ensures it's present)
            $surveyId = $data['survey_id'];
            
            // Verify survey belongs to user's organization
            $survey = Survey::where('id', $surveyId)
                ->where('organization_id', $organizationId)
                ->firstOrFail();

            // Prepare data for create/update (validation ensures required fields are present)
            $stepData = [
                'survey_id' => $surveyId,
                'step' => $data['step'],
                'order' => $data['order'] ?? 0, // Default to 0 if not provided (matches response structure)
            ];
            
            // Optional fields
            if (isset($data['tagline']) && $data['tagline'] !== null) {
                $stepData['tagline'] = $data['tagline'];
            }

            // Check if this is an update (id is provided and exists)
            $stepId = $request->input('id') ?? $data['id'] ?? null;
            
            if ($stepId) {
                // Update existing survey step
                $surveyStep = SurveyStep::where('id', $stepId)
                    ->where('survey_id', $surveyId)
                    ->first();
                
                if (!$surveyStep) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Survey step not found or you do not have permission to update it.',
                    ], 404);
                }

                // Update the survey step
                $surveyStep = $this->surveyStepRepository->update($surveyStep, $stepData);
            } else {
                // Create new survey step
                $surveyStep = $this->surveyStepRepository->create($stepData);
            }

            // Store created/updated field IDs for field_values lookup
            // Map: frontend_field_id => database_field_id (for matching field_values)
            $fieldIdMap = []; // Maps old/existing field IDs to their database IDs
            $processedFieldIds = [];
            
            // Handle survey fields update/create
            if (isset($data['survey_fields']) && is_array($data['survey_fields'])) {
                $fields = $data['survey_fields'];
                
                // Get existing field IDs for this step (only if updating)
                $existingFieldIds = [];
                $providedFieldIds = [];
                
                if ($stepId) {
                    $existingFieldIds = $surveyStep->surveyFields()->pluck('id')->toArray();
                }
                
                foreach ($fields as $fieldIndex => $fieldData) {
                    // All validation is handled in StoreSurveyStepRequest, so we can trust the data
                    $options = $fieldData['options'] ?? [];
                    if (is_string($options)) {
                        $decodedOptions = json_decode($options, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOptions)) {
                            $options = $decodedOptions;
                        } else {
                            $options = [];
                        }
                    }
                    if (!is_array($options)) {
                        $options = [];
                    }
                    
                    $fieldId = $fieldData['id'] ?? null;
                    $frontendFieldId = $fieldId; // Store the frontend field ID
                    
                    if ($fieldId && $stepId && in_array($fieldId, $existingFieldIds)) {
                        // Update existing field
                        $existingField = SurveyField::find($fieldId);
                        if ($existingField && $existingField->survey_step_id == $surveyStep->id) {
                            $existingField->update([
                                'name' => $fieldData['name'],
                                'type' => $fieldData['type'],
                                'description' => $fieldData['description'] ?? null,
                                'is_required' => (bool)($fieldData['is_required'] ?? false),
                                'options' => $options,
                                'order' => $fieldData['order'] ?? 0,
                            ]);
                            $providedFieldIds[] = $fieldId;
                            $processedFieldIds[] = $fieldId;
                            // Map frontend ID to database ID (same in this case)
                            if ($frontendFieldId) {
                                $fieldIdMap[(int)$frontendFieldId] = $fieldId;
                                $fieldIdMap[(string)$frontendFieldId] = $fieldId;
                            }
                            // Also map by array index
                            $fieldIdMap[$fieldIndex] = $fieldId;
                        }
                    } else {
                        // Create new field
                        $newField = SurveyField::create([
                            'organization_id' => $organizationId,
                            'survey_id' => $surveyId,
                            'survey_step_id' => $surveyStep->id,
                            'name' => $fieldData['name'],
                            'type' => $fieldData['type'],
                            'description' => $fieldData['description'] ?? null,
                            'is_required' => (bool)($fieldData['is_required'] ?? false),
                            'options' => $options,
                            'order' => $fieldData['order'] ?? 0,
                        ]);
                        if ($newField) {
                            $providedFieldIds[] = $newField->id;
                            $processedFieldIds[] = $newField->id;
                            // Map frontend ID (if provided) to database ID
                            if ($frontendFieldId) {
                                $fieldIdMap[(int)$frontendFieldId] = $newField->id;
                                $fieldIdMap[(string)$frontendFieldId] = $newField->id;
                            }
                            // Also map by array index as fallback (field_values might use index)
                            $fieldIdMap[$fieldIndex] = $newField->id;
                        }
                    }
                }
                
                \Log::info('Field ID Mapping', [
                    'field_id_map' => $fieldIdMap,
                    'processed_field_ids' => $processedFieldIds,
                    'field_id_map_keys' => array_keys($fieldIdMap),
                ]);
                
                // Delete fields that were not provided in the update (only if updating, not creating)
                if ($stepId && !empty($existingFieldIds)) {
                    $fieldsToDelete = array_diff($existingFieldIds, $providedFieldIds);
                    if (!empty($fieldsToDelete)) {
                        SurveyField::whereIn('id', $fieldsToDelete)
                            ->where('survey_step_id', $surveyStep->id)
                            ->delete();
                    }
                }
            }

            // Handle field_values save (user responses to survey fields)
            $savedFieldValuesCount = 0;
            $skippedFieldValuesCount = 0;
            
            // Get meeting_id and submission_id from request (for tracking submissions)
            $meetingId = $request->input('meeting_id') ?? $data['meeting_id'] ?? null;
            $submissionId = $request->input('submission_id') ?? $data['submission_id'] ?? null;
            
            if (isset($data['field_values']) && is_array($data['field_values']) && !empty($data['field_values'])) {
                $fieldValues = $data['field_values'];
                
                \Log::info('Processing field_values', [
                    'field_values' => $fieldValues,
                    'field_values_count' => count($fieldValues),
                    'survey_step_id' => $surveyStep->id,
                    'survey_id' => $surveyId,
                    'processed_field_ids' => $processedFieldIds,
                    'field_id_map' => $fieldIdMap,
                ]);
                
                // If we have survey_fields, try to match field_values by array position/index
                // This handles the case where frontend sends old field IDs but fields are recreated
                $fieldsArray = isset($data['survey_fields']) && is_array($data['survey_fields']) ? $data['survey_fields'] : [];
                
                // Convert field_values to indexed array to match by position
                $fieldValueKeys = array_keys($fieldValues);
                $fieldValueIndex = 0;
                
                foreach ($fieldValues as $fieldId => $value) {
                    // Convert field ID to integer (handles both string and integer IDs from frontend)
                    $fieldIdInt = (int) $fieldId;
                    $originalFieldId = $fieldId;
                    
                    // First, try to map frontend field ID to database field ID using our mapping
                    $databaseFieldId = $fieldIdInt;
                    
                    // Try mapping by field ID first (if field exists in database with this ID)
                    if (isset($fieldIdMap[$fieldIdInt])) {
                        $databaseFieldId = $fieldIdMap[$fieldIdInt];
                        \Log::info('Field ID mapped from fieldIdMap (by ID)', [
                            'frontend_id' => $fieldIdInt,
                            'database_id' => $databaseFieldId,
                        ]);
                    } elseif (isset($fieldIdMap[(string)$fieldId])) {
                        $databaseFieldId = $fieldIdMap[(string)$fieldId];
                        \Log::info('Field ID mapped (string key)', [
                            'frontend_id' => $fieldId,
                            'database_id' => $databaseFieldId,
                        ]);
                    } else {
                        // Try to find field by matching position in survey_fields array
                        // This handles the case where fields are recreated without IDs
                        $fieldIndex = null;
                        foreach ($fieldsArray as $idx => $fieldData) {
                            $fieldDataId = $fieldData['id'] ?? null;
                            if ($fieldDataId && (int)$fieldDataId === $fieldIdInt) {
                                $fieldIndex = $idx;
                                break;
                            }
                        }
                        
                        // If found by position, use the mapped database ID for that position
                        if ($fieldIndex !== null && isset($fieldIdMap[$fieldIndex])) {
                            $databaseFieldId = $fieldIdMap[$fieldIndex];
                            \Log::info('Field ID mapped by array position (matched ID)', [
                                'frontend_id' => $fieldIdInt,
                                'field_index' => $fieldIndex,
                                'database_id' => $databaseFieldId,
                            ]);
                        } elseif (isset($fieldIdMap[$fieldValueIndex])) {
                            // Match by position in field_values array (most common case when fields are recreated)
                            // This assumes field_values are sent in the same order as survey_fields
                            $databaseFieldId = $fieldIdMap[$fieldValueIndex];
                            \Log::info('Field ID mapped by field_values array position', [
                                'frontend_id' => $fieldIdInt,
                                'field_value_index' => $fieldValueIndex,
                                'database_id' => $databaseFieldId,
                            ]);
                        }
                    }
                    
                    // Increment index for next iteration
                    $fieldValueIndex++;
                    
                    // Try to find field using mapped ID or original ID
                    $field = null;
                    
                    // First check if this field was just processed (created/updated) using mapped ID
                    if (in_array($databaseFieldId, $processedFieldIds)) {
                        $field = SurveyField::find($databaseFieldId);
                        if ($field) {
                            \Log::info('Field found in processed fields (mapped ID)', [
                                'frontend_id' => $fieldIdInt,
                                'database_id' => $databaseFieldId,
                            ]);
                        }
                    }
                    
                    // If not found, check if original ID exists in processed fields
                    if (!$field && in_array($fieldIdInt, $processedFieldIds)) {
                        $field = SurveyField::find($fieldIdInt);
                        if ($field) {
                            \Log::info('Field found in processed fields (original ID)', [
                                'frontend_id' => $fieldIdInt,
                                'database_id' => $fieldIdInt,
                            ]);
                            $databaseFieldId = $fieldIdInt;
                        }
                    }
                    
                    // If not found in processed fields, check database - first try with original ID (most likely case)
                    // This handles the case where frontend sends existing field IDs that weren't in survey_fields
                    if (!$field) {
                        $field = SurveyField::where('id', $fieldIdInt)
                            ->where('survey_step_id', $surveyStep->id)
                            ->first();
                        
                        if ($field) {
                            \Log::info('Field found in database (step - original ID)', [
                                'frontend_id' => $fieldIdInt,
                                'database_id' => $fieldIdInt,
                            ]);
                            $databaseFieldId = $fieldIdInt;
                        }
                    }
                    
                    // If still not found, check with mapped ID
                    if (!$field && $databaseFieldId !== $fieldIdInt) {
                        $field = SurveyField::where('id', $databaseFieldId)
                            ->where('survey_step_id', $surveyStep->id)
                            ->first();
                        
                        if ($field) {
                            \Log::info('Field found in database (step - mapped ID)', [
                                'frontend_id' => $fieldIdInt,
                                'database_id' => $databaseFieldId,
                            ]);
                        }
                    }
                    
                    // If still not found, try broader search (same survey, any step)
                    if (!$field) {
                        $field = SurveyField::where('id', $fieldIdInt)
                            ->where('survey_id', $surveyId)
                            ->first();
                        
                        if ($field) {
                            \Log::info('Field found in database (survey - original ID)', [
                                'frontend_id' => $fieldIdInt,
                                'database_id' => $fieldIdInt,
                            ]);
                            $databaseFieldId = $fieldIdInt;
                        }
                    }
                    
                    // Last resort: try with mapped ID in survey
                    if (!$field && $databaseFieldId !== $fieldIdInt) {
                        $field = SurveyField::where('id', $databaseFieldId)
                            ->where('survey_id', $surveyId)
                            ->first();
                        
                        if ($field) {
                            \Log::info('Field found in database (survey - mapped ID)', [
                                'frontend_id' => $fieldIdInt,
                                'database_id' => $databaseFieldId,
                            ]);
                        }
                    }
                    
                    if (!$field) {
                        \Log::warning('Field not found for field_value - SKIPPING', [
                            'frontend_field_id' => $fieldIdInt,
                            'original_field_id' => $originalFieldId,
                            'database_field_id' => $databaseFieldId,
                            'survey_step_id' => $surveyStep->id,
                            'survey_id' => $surveyId,
                            'processed_field_ids' => $processedFieldIds,
                            'field_id_map' => $fieldIdMap,
                            'field_id_map_keys' => array_keys($fieldIdMap),
                            'all_survey_fields' => SurveyField::where('survey_id', $surveyId)->pluck('id')->toArray(),
                            'step_fields' => SurveyField::where('survey_step_id', $surveyStep->id)->pluck('id')->toArray(),
                        ]);
                        $skippedFieldValuesCount++;
                        continue; // Skip invalid field IDs
                    }
                    
                    // Use the correct database field ID
                    $fieldIdInt = $databaseFieldId;
                    
                    // Convert array values to JSON string
                    if (is_array($value)) {
                        $value = json_encode($value);
                    } elseif ($value === null) {
                        $value = null;
                    } else {
                        $value = (string) $value;
                    }
                    
                    // Always INSERT new record (never UPDATE) to support multiple submissions
                    // Store meeting_id and submission_id for tracking
                    try {
                        $fieldValue = SurveyFieldValue::create([
                            'organization_id' => $organizationId,
                            'survey_id' => $surveyId,
                            'survey_step_id' => $surveyStep->id,
                            'survey_field_id' => $fieldIdInt,
                            'submission_id' => $submissionId,
                            'user_id' => $request->user()->id,
                            'value' => $value,
                        ]);
                        
                        $savedFieldValuesCount++;
                        \Log::info('Field value saved successfully', [
                            'field_id' => $fieldIdInt,
                            'value' => $value,
                            'field_value_id' => $fieldValue->id,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error saving field value', [
                            'field_id' => $fieldIdInt,
                            'value' => $value,
                            'error' => $e->getMessage(),
                        ]);
                        $skippedFieldValuesCount++;
                    }
                }
                
                \Log::info('Field values processing complete', [
                    'saved_count' => $savedFieldValuesCount,
                    'skipped_count' => $skippedFieldValuesCount,
                    'total_count' => count($fieldValues),
                ]);
            } else {
                \Log::info('No field_values to process', [
                    'has_field_values' => isset($data['field_values']),
                    'is_array' => isset($data['field_values']) && is_array($data['field_values']),
                    'is_empty' => isset($data['field_values']) && empty($data['field_values']),
                ]);
            }

            // Track survey response when field_values exist (indicates survey was filled/submitted)
            // Note: meetingId and submissionId are already retrieved above (line 306-307)
            $contactId = $request->input('contact_id') ?? $data['contact_id'] ?? null;
            
            // Track response if field_values are provided (survey was filled)
            if (isset($data['field_values']) && is_array($data['field_values']) && !empty($data['field_values'])) {
                // Build query to find existing response
                $existingResponseQuery = SurveyResponse::where('survey_id', $surveyId)
                    ->where('user_id', $user->id);
                
                // If meeting_id is provided, include it in the query to prevent duplicates per meeting
                // Otherwise, check for existing response without meeting_id for this user-survey combination
                if ($meetingId) {
                    $existingResponseQuery->where('meeting_id', $meetingId);
                } else {
                    $existingResponseQuery->whereNull('meeting_id');
                }
                
                $existingResponse = $existingResponseQuery->first();
                
                if ($existingResponse) {
                    // Update existing response
                    $existingResponse->update([
                        'contact_id' => $contactId,
                        'response_data' => $data['field_values'],
                        'submitted_at' => now(),
                    ]);
                    $surveyResponse = $existingResponse;
                    $action = 'updated';
                } else {
                    // Create new response
                    $surveyResponse = SurveyResponse::create([
                        'survey_id' => $surveyId,
                        'meeting_id' => $meetingId,
                        'user_id' => $user->id,
                        'contact_id' => $contactId,
                        'response_data' => $data['field_values'],
                        'submitted_at' => now(),
                    ]);
                    $action = 'created';
                }
                
                \Log::info('Survey response tracked', [
                    'action' => $action,
                    'survey_id' => $surveyId,
                    'meeting_id' => $meetingId,
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
                    'response_id' => $surveyResponse->id,
                    'field_values_count' => count($data['field_values']),
                ]);
            } else {
                \Log::info('Survey response NOT tracked - conditions not met', [
                    'survey_id' => $surveyId,
                    'meeting_id' => $meetingId,
                    'has_field_values' => isset($data['field_values']),
                    'is_array' => isset($data['field_values']) && is_array($data['field_values']),
                    'is_empty' => isset($data['field_values']) && empty($data['field_values']),
                ]);
            }

            // Record survey submission (if meeting_id is provided)
            // This tracks that the user has submitted the survey for this meeting
            if ($meetingId && isset($data['field_values']) && is_array($data['field_values']) && !empty($data['field_values'])) {
                SurveySubmission::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'meeting_id' => $meetingId,
                        'survey_id' => $surveyId,
                    ],
                    [
                        'submitted_at' => now(),
                    ]
                );
                
                \Log::info('Survey submission recorded', [
                    'user_id' => $user->id,
                    'meeting_id' => $meetingId,
                    'survey_id' => $surveyId,
                    'submission_id' => $submissionId,
                ]);
            } else {
                \Log::warning('Survey submission NOT recorded - missing conditions', [
                    'user_id' => $user->id,
                    'meeting_id' => $meetingId,
                    'survey_id' => $surveyId,
                    'has_field_values' => isset($data['field_values']),
                    'is_array' => isset($data['field_values']) && is_array($data['field_values']),
                    'is_empty' => isset($data['field_values']) && empty($data['field_values']),
                ]);
            }

            // Commit transaction
            DB::commit();
            
            // Verify field values were saved
            if (isset($data['field_values']) && is_array($data['field_values']) && !empty($data['field_values'])) {
                $savedFieldValues = SurveyFieldValue::where('survey_step_id', $surveyStep->id)
                    ->where('user_id', $user->id)
                    ->get(['id', 'survey_field_id', 'value']);
                
                \Log::info('Field values verification after commit', [
                    'survey_step_id' => $surveyStep->id,
                    'user_id' => $user->id,
                    'saved_count' => $savedFieldValues->count(),
                    'saved_values' => $savedFieldValues->map(function($fv) {
                        return [
                            'id' => $fv->id,
                            'field_id' => $fv->survey_field_id,
                            'value' => $fv->value,
                        ];
                    })->toArray(),
                ]);
            }

            // Reload survey step with fields
            $surveyStep->refresh();
            $surveyStep->load('surveyFields');

            return response()->json([
                'success' => true,
                'message' => $stepId ? 'Survey step updated successfully.' : 'Survey step saved successfully.',
                'data' => new SurveyStepResource($surveyStep),
                'errors' => null,
            ], $stepId ? 200 : 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // This should not happen as validation is handled by FormRequest
            // But just in case, log it
            \Log::error('Survey Step Save - Validation Exception', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            // This is thrown by FormRequest on validation failure
            // Re-throw it so it returns the proper validation response
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            \Log::error('Survey Step Save - Model Not Found', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Survey not found or you do not have permission to access it.',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Survey Step Save Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the survey step: ' . $e->getMessage(),
                'data' => null,
                'errors' => ['general' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Delete a survey step by ID
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:survey_steps,id',
        ]);

        $surveyStepId = $request->id;

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create an organization first.',
            ], 404);
        }

        $organizationId = $organization->id;

        // Get survey step and verify it belongs to user's organization
        $surveyStep = SurveyStep::where('id', $surveyStepId)
            ->whereHas('survey', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->first();
        
        if (!$surveyStep) {
            return response()->json([
                'message' => 'Survey step not found or you do not have permission to delete it.',
            ], 404);
        }

        // Delete the survey step
        $this->surveyStepRepository->delete($surveyStep);

        return response()->json([
            'message' => 'Survey step deleted successfully.',
        ], 200);
    }
}

