<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyStep;
use App\Models\SurveyField;
use App\Models\SurveyFieldValue;
use App\Models\SurveySubmission;
use App\Models\Organization;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SurveyController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function save(StoreSurveyRequest $request)
    {
        $user = $request->user();

        // Get or create organization
        $organization = $user->organization();
        
        if (!$organization) {
            $organizationName = $user->name . "'s Organization";
            $slug = Str::slug($organizationName . '-' . $user->id);
            $organization = Organization::create([
                'name' => $organizationName,
                'slug' => $slug,
                'description' => 'Personal organization',
                'status' => 'active',
            ]);
            $user->organizations()->attach($organization->id, ['role' => 'admin']);
        }

        // Normalize status to lowercase for comparison (validation accepts capitalized values)
        $status = strtolower($request->status);

        // Check active surveys limit for new surveys or when activating
        if (!$request->id || ($request->id && $status === 'active')) {
            // If updating to active, check if we're already at limit
            if ($request->id && $status === 'active') {
                $currentSurvey = Survey::where('id', $request->id)
                    ->where('organization_id', $organization->id)
                    ->first();
                
                // Only check if survey is currently not active (case-insensitive comparison)
                if ($currentSurvey && strtolower($currentSurvey->status) !== 'active') {
                    $result = $this->subscriptionService->checkLimit($organization, 'create_survey');
                    
                    if (!$result['allowed']) {
                        return response()->json([
                            'success' => false,
                            'message' => $result['message'],
                            'data' => ['upgrade_required' => true]
                        ], 403);
                    }
                }
            } elseif (!$request->id) {
                // New survey - always check limit if status will be active
                if ($status === 'active') {
                    $result = $this->subscriptionService->checkLimit($organization, 'create_survey');
                    
                    if (!$result['allowed']) {
                        return response()->json([
                            'success' => false,
                            'message' => $result['message'],
                            'data' => ['upgrade_required' => true]
                        ], 403);
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            // Normalize status value for storage (convert to capitalized format for consistency)
            $statusValue = ucfirst(strtolower($request->status));
            // Map to valid status values
            $validStatuses = ['Draft', 'Active', 'Archived', 'Published'];
            if (!in_array($statusValue, $validStatuses)) {
                $statusValue = 'Draft'; // Default to Draft if invalid
            }

            // Create or update survey
            if ($request->id) {
                $survey = Survey::where('id', $request->id)
                    ->where('organization_id', $organization->id)
                    ->firstOrFail();
                
                $survey->update([
                    'survey_name' => $request->survey_name,
                    'description' => $request->description,
                    'status' => $statusValue,
                ]);
            } else {
                $survey = Survey::create([
                    'organization_id' => $organization->id,
                    'survey_name' => $request->survey_name,
                    'description' => $request->description,
                    'status' => $statusValue,
                    'created_by' => $user->id,
                ]);
            }

            // Delete existing steps if updating
            if ($request->id) {
                $survey->surveySteps()->delete();
            }

            // Create steps and fields (only if survey_steps is provided)
            if ($request->has('survey_steps') && is_array($request->survey_steps)) {
                foreach ($request->survey_steps as $stepData) {
                    // Skip if step data is not valid
                    if (!is_array($stepData)) {
                        continue;
                    }

                    $step = SurveyStep::create([
                        'survey_id' => $survey->id,
                        'step' => $stepData['step'] ?? null,
                        'tagline' => $stepData['tagline'] ?? null,
                        'order' => $stepData['order'] ?? 0,
                    ]);

                    // Normalize survey_fields to an array to avoid undefined index errors
                    $fields = $stepData['survey_fields'] ?? [];
                    if (is_string($fields)) {
                        $decodedFields = json_decode($fields, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $fields = $decodedFields;
                        }
                    }
                    if (!is_array($fields)) {
                        $fields = [];
                    }

                    foreach ($fields as $fieldData) {
                        if (!is_array($fieldData)) {
                            continue;
                        }

                        $name = $fieldData['name'] ?? null;
                        if ($name === null || $name === '') {
                            // Skip fields without a name to avoid undefined index errors
                            continue;
                        }

                        $type = $fieldData['type'] ?? 'Short Answer';
                        $options = $fieldData['options'] ?? null;
                        if (is_string($options)) {
                            $decodedOptions = json_decode($options, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $options = $decodedOptions;
                            }
                        }
                        if (!is_array($options)) {
                            $options = null;
                        }

                        SurveyField::create([
                            'organization_id' => $organization->id,
                            'survey_id' => $survey->id,
                            'survey_step_id' => $step->id,
                            'name' => $name,
                            'type' => $type,
                            'description' => $fieldData['description'] ?? null,
                            'is_required' => $fieldData['is_required'] ?? false,
                            'options' => $options,
                            'order' => $fieldData['order'] ?? 0,
                        ]);
                    }
                }
            }

            DB::commit();

            // Load relationships and return response
            $survey->load(['surveySteps.surveyFields']);

            return response()->json([
                'data' => new SurveyResource($survey),
                'message' => 'Survey saved successfully.',
            ], $request->id ? 200 : 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Survey not found or you do not have permission to update it.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving survey', [
                'user_id' => $user->id ?? null,
                'organization_id' => $organization->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the survey. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get surveys list for dropdown (returns only id and name)
     */
    public function dropdown(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'message' => 'No organization found. Please create a survey first.',
            ]);
        }

        $organizationId = $organization->id;

        // Get surveys for this organization, return only id and survey_name
        $surveys = Survey::where('organization_id', $organizationId)
            ->select('id', 'survey_name')
            ->orderBy('survey_name')
            ->get()
            ->map(function ($survey) {
                return [
                    'id' => $survey->id,
                    'name' => $survey->survey_name,
                ];
            });

        return response()->json([
            'data' => $surveys,
            'message' => 'Surveys retrieved successfully.',
        ]);
    }

    /**
     * Get paginated list of surveys with search and filter
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'per_page' => 15,
                    'to' => null,
                    'total' => 0,
                ],
                'message' => 'No organization found. Please create a survey first.',
            ]);
        }

        $organizationId = $organization->id;

        // Get pagination parameters from request body (JSON) or query string
        $perPage = $request->input('per_page', 15); // Default 15 per page
        $page = $request->input('page', 1); // Default page 1

        // Validate and sanitize per_page (between 1 and 100)
        $perPage = (int) $perPage;
        $perPage = min(max(1, $perPage), 100);

        // Validate and sanitize page (must be at least 1)
        $page = (int) $page;
        $page = max(1, $page); // Ensure page is at least 1

        // Build query
        $query = Survey::where('organization_id', $organizationId);

        // Optional: Search by survey_name or description
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('survey_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Optional: Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Order by created_at (newest first)
        $query->orderBy('created_at', 'desc');

        // Load relationships and count unique submissions per meeting
        $query->with(['surveySteps.surveyFields'])
            ->withCount('surveySubmissions as response_count');

        // Get paginated results
        $surveys = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Debug: Log response counts
        \Log::info('Survey Index - Response Counts', [
            'surveys_count' => $surveys->count(),
            'surveys_data' => $surveys->map(function($survey) {
                return [
                    'id' => $survey->id,
                    'survey_name' => $survey->survey_name,
                    'response_count' => $survey->response_count ?? 'NOT_SET',
                ];
            })->toArray(),
        ]);

        // Ensure requested page doesn't exceed last page
        $lastPage = $surveys->lastPage();
        if ($page > $lastPage && $lastPage > 0) {
            // Re-fetch with the last valid page and reload relationships
            $surveys = $query->with(['surveySteps.surveyFields'])
                ->withCount('surveySubmissions as response_count')
                ->paginate($perPage, ['*'], 'page', $lastPage);
        }

        // Get statistics
        $statistics = $this->getStatistics($organizationId);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => SurveyResource::collection($surveys->items()),
                'meta' => [
                    'current_page' => $surveys->currentPage(),
                    'from' => $surveys->firstItem(),
                    'last_page' => $surveys->lastPage(),
                    'per_page' => $surveys->perPage(),
                    'to' => $surveys->lastItem(),
                    'total' => $surveys->total(),
                ],
                'statistics' => $statistics,
            ],
            'message' => 'Surveys retrieved successfully.',
        ]);
    }

    /**
     * Get survey statistics (Total Surveys, Active Surveys, Total Responses, Draft Surveys)
     */
    public function state(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();

        if (!$organization) {
            return response()->json([
                'data' => [
                    'total_surveys' => 0,
                    'active_surveys' => 0,
                    'total_responses' => 0,
                    'draft_surveys' => 0,
                ],
                'message' => 'Statistics retrieved successfully.',
            ]);
        }

        $organizationId = $organization->id;

        // Total surveys count
        $totalSurveys = Survey::where('organization_id', $organizationId)->count();

        // Active surveys count (Published status)
        $activeSurveys = Survey::where('organization_id', $organizationId)
            ->where('status', 'Published')
            ->count();

        // Total responses count
        $totalResponses = DB::table('survey_responses')
            ->join('surveys', 'survey_responses.survey_id', '=', 'surveys.id')
            ->where('surveys.organization_id', $organizationId)
            ->count();

        // Draft surveys count
        $draftSurveys = Survey::where('organization_id', $organizationId)
            ->where('status', 'Draft')
            ->count();

        return response()->json([
            'data' => [
                'total_surveys' => $totalSurveys,
                'active_surveys' => $activeSurveys,
                'total_responses' => $totalResponses,
                'draft_surveys' => $draftSurveys,
            ],
            'message' => 'Statistics retrieved successfully.',
        ]);
    }

    /**
     * Get single survey by ID
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:surveys,id',
        ]);

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create a survey first.',
            ], 404);
        }

        $organizationId = $organization->id;
        $surveyId = $request->id;

        // Get survey from user's organization with relationships
        $survey = Survey::where('id', $surveyId)
            ->where('organization_id', $organizationId)
            ->withCount('surveySubmissions as response_count')
            ->firstOrFail();

        // Load relationships
        $survey->load(['surveySteps.surveyFields']);

        return response()->json([
            'data' => new SurveyResource($survey),
            'message' => 'Survey retrieved successfully.',
        ]);
    }

    /**
     * Delete a survey by ID
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:surveys,id',
        ]);

        $surveyId = $request->id;

        // First, check if survey exists at all
        $survey = Survey::find($surveyId);
        
        if (!$survey) {
            return response()->json([
                'message' => 'Survey not found.',
            ], 404);
        }

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        // Check permission: survey must belong to user's organization OR user must be the creator
        $hasPermission = false;
        
        if ($organization) {
            // Check if survey belongs to user's organization
            if ($survey->organization_id == $organization->id) {
                $hasPermission = true;
            }
        }
        
        // Also allow if user created the survey (created_by matches)
        if ($survey->created_by == $user->id) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'You do not have permission to delete this survey.',
            ], 403);
        }

        // Delete the survey (cascade will handle steps and fields)
        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully.',
        ], 200);
    }

    /**
     * Get survey statistics
     */
    private function getStatistics($organizationId)
    {
        $totalSurveys = Survey::where('organization_id', $organizationId)->count();
        $activeSurveys = Survey::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();
        $totalResponses = DB::table('survey_responses')
            ->join('surveys', 'survey_responses.survey_id', '=', 'surveys.id')
            ->where('surveys.organization_id', $organizationId)
            ->count();
        $draftSurveys = Survey::where('organization_id', $organizationId)
            ->where('status', 'draft')
            ->count();

        return [
            'total_surveys' => $totalSurveys,
            'active_surveys' => $activeSurveys,
            'total_responses' => $totalResponses,
            'draft_surveys' => $draftSurveys,
        ];
    }

    /**
     * Get analytics for a specific survey
     */
    public function analytics(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:surveys,id',
        ]);

        $user = $request->user();
        $surveyId = $request->input('id');

        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'No organization found. Please create an organization first.',
            ], 404);
        }

        // Get survey and verify it belongs to user's organization
        $survey = Survey::where('id', $surveyId)
            ->where('organization_id', $organization->id)
            ->with(['surveySteps.surveyFields'])
            ->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found',
            ], 404);
        }

        // Get total responses (unique submissions per meeting)
        $totalResponses = DB::table('survey_submissions')
            ->where('survey_id', $surveyId)
            ->whereNotNull('meeting_id')
            ->count(DB::raw('DISTINCT meeting_id'));

        // Also count responses without meeting_id (backward compatibility)
        $responsesWithoutMeeting = \App\Models\SurveyResponse::where('survey_id', $surveyId)
            ->whereNull('meeting_id')
            ->exists() ? 1 : 0;

        $totalResponses = $totalResponses + $responsesWithoutMeeting;

        // Calculate completion rate
        // A survey is considered complete if all required fields have values
        $requiredFields = SurveyField::where('survey_id', $surveyId)
            ->where('is_required', true)
            ->pluck('id')
            ->toArray();

        $completedCount = 0;
        if ($totalResponses > 0 && !empty($requiredFields)) {
            // Get all unique user-meeting combinations
            $submissions = SurveySubmission::where('survey_id', $surveyId)
                ->whereNotNull('meeting_id')
                ->select('user_id', 'meeting_id')
                ->distinct()
                ->get();

            foreach ($submissions as $submission) {
                // Check if all required fields have values for this submission
                $filledRequiredFields = DB::table('survey_field_values')
                    ->where('survey_id', $surveyId)
                    ->where('user_id', $submission->user_id)
                    ->whereIn('survey_field_id', $requiredFields)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
                    ->count(DB::raw('DISTINCT survey_field_id'));

                if ($filledRequiredFields >= count($requiredFields)) {
                    $completedCount++;
                }
            }

            // Also check responses without meeting_id
            if ($responsesWithoutMeeting > 0) {
                $usersWithoutMeeting = \App\Models\SurveyResponse::where('survey_id', $surveyId)
                    ->whereNull('meeting_id')
                    ->distinct()
                    ->pluck('user_id');

                foreach ($usersWithoutMeeting as $userId) {
                    $filledRequiredFields = DB::table('survey_field_values')
                        ->where('survey_id', $surveyId)
                        ->where('user_id', $userId)
                        ->whereIn('survey_field_id', $requiredFields)
                        ->whereNotNull('value')
                        ->where('value', '!=', '')
                        ->count(DB::raw('DISTINCT survey_field_id'));

                    if ($filledRequiredFields >= count($requiredFields)) {
                        $completedCount++;
                    }
                }
            }
        } else {
            // If no required fields, all responses are considered complete
            $completedCount = $totalResponses;
        }

        $completionRate = $totalResponses > 0
            ? round(($completedCount / $totalResponses) * 100)
            : 0;

        // Get responses by date (last 7 days)
        $responsesByDate = DB::table('survey_submissions')
            ->where('survey_id', $surveyId)
            ->whereNotNull('meeting_id')
            ->where('submitted_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(submitted_at) as date'), DB::raw('COUNT(DISTINCT meeting_id) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            });

        // Fill in missing dates with 0 count
        $dateRange = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $existing = $responsesByDate->firstWhere('date', $date);
            $dateRange[] = [
                'date' => $date,
                'count' => $existing ? $existing['count'] : 0,
            ];
        }

        // Get question-wise analytics
        $questions = [];
        foreach ($survey->surveySteps as $step) {
            foreach ($step->surveyFields as $field) {
                $questionData = [
                    'question_id' => $field->id,
                    'question' => $field->name,
                    'type' => $this->mapFieldType($field->type),
                    'answers' => [],
                ];

                // Get answer distribution for this field
                if (in_array($field->type, ['Multiple Choice', 'Dropdown', 'Rating Scale'])) {
                    // For choice-based questions
                    $options = $field->options ?? [];
                    
                    if ($field->type === 'Rating Scale') {
                        // For rating scale, generate options from 1 to max (default 5)
                        $maxRating = isset($options['max']) ? (int) $options['max'] : 5;
                        $options = [];
                        for ($i = 1; $i <= $maxRating; $i++) {
                            $options[] = (string) $i;
                        }
                    }

                    if (empty($options) && $field->type !== 'Rating Scale') {
                        // If no options defined, get unique values from responses
                        $uniqueValues = SurveyFieldValue::where('survey_id', $surveyId)
                            ->where('survey_field_id', $field->id)
                            ->whereNotNull('value')
                            ->where('value', '!=', '')
                            ->distinct()
                            ->pluck('value')
                            ->toArray();
                        $options = $uniqueValues;
                    }

                    foreach ($options as $option) {
                        $optionValue = is_array($option) ? ($option['value'] ?? $option['label'] ?? '') : $option;
                        $optionValue = (string) $optionValue;

                        // Count responses with this value
                        $count = SurveyFieldValue::where('survey_id', $surveyId)
                            ->where('survey_field_id', $field->id)
                            ->where(function ($query) use ($optionValue) {
                                // Handle JSON-encoded arrays (for checkboxes)
                                $query->where('value', $optionValue)
                                    ->orWhere('value', 'LIKE', '%"' . $optionValue . '"%')
                                    ->orWhere('value', 'LIKE', '%' . $optionValue . '%');
                            })
                            ->count();

                        $percentage = $totalResponses > 0
                            ? round(($count / $totalResponses) * 100)
                            : 0;

                        $questionData['answers'][] = [
                            'value' => $optionValue,
                            'count' => $count,
                            'percentage' => $percentage,
                        ];
                    }
                } elseif ($field->type === 'Checkboxes') {
                    // For checkboxes, each option can be selected multiple times
                    $options = $field->options ?? [];
                    
                    if (empty($options)) {
                        // Get unique values from responses
                        $uniqueValues = SurveyFieldValue::where('survey_id', $surveyId)
                            ->where('survey_field_id', $field->id)
                            ->whereNotNull('value')
                            ->where('value', '!=', '')
                            ->get()
                            ->flatMap(function ($item) {
                                // Handle JSON-encoded arrays
                                $decoded = json_decode($item->value, true);
                                return is_array($decoded) ? $decoded : [$item->value];
                            })
                            ->unique()
                            ->toArray();
                        $options = $uniqueValues;
                    }

                    foreach ($options as $option) {
                        $optionValue = is_array($option) ? ($option['value'] ?? $option['label'] ?? '') : $option;
                        $optionValue = (string) $optionValue;

                        // Count responses containing this value (can be in JSON array)
                        $count = SurveyFieldValue::where('survey_id', $surveyId)
                            ->where('survey_field_id', $field->id)
                            ->where(function ($query) use ($optionValue) {
                                $query->where('value', $optionValue)
                                    ->orWhere('value', 'LIKE', '%"' . $optionValue . '"%')
                                    ->orWhere('value', 'LIKE', '%' . $optionValue . '%');
                            })
                            ->count();

                        $percentage = $totalResponses > 0
                            ? round(($count / $totalResponses) * 100)
                            : 0;

                        $questionData['answers'][] = [
                            'value' => $optionValue,
                            'count' => $count,
                            'percentage' => $percentage,
                        ];
                    }
                } else {
                    // For text-based questions (Short Answer, Paragraph, Email, etc.)
                    $respondedCount = SurveyFieldValue::where('survey_id', $surveyId)
                        ->where('survey_field_id', $field->id)
                        ->whereNotNull('value')
                        ->where('value', '!=', '')
                        ->count();

                    $noResponseCount = $totalResponses - $respondedCount;

                    $questionData['answers'] = [
                        [
                            'value' => 'Text responses',
                            'count' => $respondedCount,
                            'percentage' => $totalResponses > 0
                                ? round(($respondedCount / $totalResponses) * 100)
                                : 0,
                        ],
                        [
                            'value' => 'No response',
                            'count' => $noResponseCount,
                            'percentage' => $totalResponses > 0
                                ? round(($noResponseCount / $totalResponses) * 100)
                                : 0,
                        ],
                    ];
                }

                $questions[] = $questionData;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Analytics loaded successfully',
            'data' => [
                'survey_id' => $survey->id,
                'survey_name' => $survey->survey_name,
                'total_responses' => $totalResponses,
                'completion_rate' => $completionRate,
                'avg_completion_time' => null, // Not tracked in current schema
                'responses_by_date' => $dateRange,
                'questions' => $questions,
            ],
        ]);
    }

    /**
     * Map internal field type to API response type
     */
    private function mapFieldType($internalType): string
    {
        $typeMap = [
            'Multiple Choice' => 'radio',
            'Checkboxes' => 'checkbox',
            'Dropdown' => 'dropdown',
            'Rating Scale' => 'rating',
            'Short Answer' => 'text',
            'Paragraph' => 'textarea',
            'Email' => 'text',
            'Number' => 'text',
            'Date' => 'text',
            'File Upload' => 'text',
        ];

        return $typeMap[$internalType] ?? 'text';
    }

    /**
     * Check if user has already submitted a survey for a meeting
     */
    public function checkSubmission(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|integer|exists:meetings,id',
            'survey_id' => 'required|integer|exists:surveys,id',
        ]);

        $user = $request->user();
        $userId = $user->id;
        $meetingId = $request->input('meeting_id');
        $surveyId = $request->input('survey_id');

        // Check if survey submission exists in survey_submissions table
        $submission = SurveySubmission::where('user_id', $userId)
            ->where('meeting_id', $meetingId)
            ->where('survey_id', $surveyId)
            ->first();

        if ($submission) {
            return response()->json([
                'success' => true,
                'message' => 'Survey response found',
                'data' => [
                    'is_submitted' => true,
                    'submitted_at' => $submission->submitted_at 
                        ? $submission->submitted_at->toIso8601String() 
                        : ($submission->created_at ? $submission->created_at->toIso8601String() : null),
                    'response_id' => $submission->id,
                ],
            ]);
        }

        // If not found in survey_submissions, also check survey_responses for backward compatibility
        $response = \App\Models\SurveyResponse::where('user_id', $userId)
            ->where('meeting_id', $meetingId)
            ->where('survey_id', $surveyId)
            ->first();

        if ($response) {
            return response()->json([
                'success' => true,
                'message' => 'Survey response found',
                'data' => [
                    'is_submitted' => true,
                    'submitted_at' => $response->submitted_at 
                        ? $response->submitted_at->toIso8601String() 
                        : ($response->created_at ? $response->created_at->toIso8601String() : null),
                    'response_id' => $response->id,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'No survey response found',
            'data' => [
                'is_submitted' => false,
                'submitted_at' => null,
                'response_id' => null,
            ],
        ]);
    }
}
