# Survey Submission Status Check API

## Overview
Backend API endpoint to check if a user has already submitted a survey response for a specific meeting. This allows the frontend to show a "Survey is successfully submitted" popup instead of opening the survey form again.

## Implementation

### New Endpoint

**POST** `/api/survey/check-submission`

### Request

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "meeting_id": 123,
  "survey_id": 45
}
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| meeting_id | integer | Yes | The meeting ID |
| survey_id | integer | Yes | The survey ID |

### Response

#### Success - Survey Already Submitted (200)
```json
{
  "success": true,
  "message": "Survey response found",
  "data": {
    "is_submitted": true,
    "submitted_at": "2025-12-18T10:30:00Z",
    "response_id": 789
  }
}
```

#### Success - Survey Not Submitted (200)
```json
{
  "success": true,
  "message": "No survey response found",
  "data": {
    "is_submitted": false,
    "submitted_at": null,
    "response_id": null
  }
}
```

#### Error - Validation (422)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "meeting_id": ["The meeting id field is required."],
    "survey_id": ["The survey id field is required."]
  }
}
```

## Implementation Details

### Controller Method
Located in `app/Http/Controllers/Api/SurveyController.php`:

```php
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

    // Check survey_submissions table first (primary)
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

    // Fallback to survey_responses for backward compatibility
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
```

### Route
Added to `routes/api.php`:
```php
Route::post('/survey/check-submission', [SurveyController::class, 'checkSubmission']);
```

## Alternative: Meeting Resource Enhancement

The `MeetingResource` has also been enhanced to include survey submission information directly in the meeting response:

### Meeting Response Fields

```json
{
  "id": 123,
  "meeting_title": "Team Standup",
  "survey_id": 45,
  "survey": {
    "id": 45,
    "survey_name": "Meeting Feedback"
  },
  "has_submitted_survey": true,
  "survey_submitted_at": "2025-12-18T10:30:00Z"
}
```

- **`has_submitted_survey`**: Boolean indicating if the current user has submitted the survey
- **`survey_submitted_at`**: ISO 8601 timestamp of when the survey was submitted (null if not submitted)

This allows the frontend to check submission status without an additional API call when loading meetings.

## Database Tables Used

### Primary: `survey_submissions`
- Tracks unique survey submissions per user-meeting-survey combination
- Fields: `user_id`, `meeting_id`, `survey_id`, `submitted_at`

### Fallback: `survey_responses`
- Used for backward compatibility with older data
- Fields: `user_id`, `meeting_id`, `survey_id`, `submitted_at`

## Example API Calls

### Check Submission Status
```bash
curl -X POST http://localhost:8000/api/survey/check-submission \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "meeting_id": 123,
    "survey_id": 45
  }'
```

### Response - Already Submitted
```json
{
  "success": true,
  "message": "Survey response found",
  "data": {
    "is_submitted": true,
    "submitted_at": "2025-12-18T10:30:00Z",
    "response_id": 789
  }
}
```

### Response - Not Submitted
```json
{
  "success": true,
  "message": "No survey response found",
  "data": {
    "is_submitted": false,
    "submitted_at": null,
    "response_id": null
  }
}
```

## Frontend Integration

### Using the Check Submission Endpoint

```dart
Future<void> _showSurveyDialogForMeeting(Meeting meeting) async {
  if (meeting.surveyId == null || meeting.surveyId!.isEmpty) {
    // No survey associated
    return;
  }
  
  // Check if survey is already submitted via API
  final checkResponse = await ApiService().checkSurveySubmission(
    meetingId: int.parse(meeting.id),
    surveyId: int.parse(meeting.surveyId!),
  );
  
  if (checkResponse.success && checkResponse.data?['is_submitted'] == true) {
    // Show popup dialog for already submitted survey
    _showSurveyAlreadySubmittedDialog();
    return;
  }
  
  // Continue with opening survey form...
}

void _showSurveyAlreadySubmittedDialog() {
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: Row(
        children: [
          Icon(Icons.check_circle, color: Colors.green, size: 28),
          SizedBox(width: 12),
          Text('Survey Submitted'),
        ],
      ),
      content: Text('This survey has been successfully submitted.'),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text('OK'),
        ),
      ],
    ),
  );
}
```

### Using Meeting Resource (Alternative)

If the meeting data already includes `has_submitted_survey` and `survey_submitted_at`, you can check directly:

```dart
if (meeting.hasSubmittedSurvey == true) {
  _showSurveyAlreadySubmittedDialog();
  return;
}
```

## Testing Checklist

- [x] First survey submission saves response to database
- [x] Check submission API returns `is_submitted: false` before submission
- [x] Check submission API returns `is_submitted: true` after submission
- [x] Frontend shows popup dialog when survey already submitted
- [x] Works correctly after page reload
- [x] Works for different users (each user has own submission status)
- [x] Validates required parameters (meeting_id, survey_id)
- [x] Returns proper error messages for invalid IDs
- [x] Handles backward compatibility with survey_responses table

## Notes

1. **Primary Table**: The endpoint primarily checks `survey_submissions` table, which tracks unique submissions per user-meeting-survey combination.

2. **Backward Compatibility**: The endpoint also checks `survey_responses` table for backward compatibility with older data.

3. **Timestamp Format**: All timestamps are returned in ISO 8601 format (e.g., "2025-12-18T10:30:00Z").

4. **User Context**: The endpoint automatically uses the authenticated user's ID from the token, so only the user's own submissions are checked.

5. **Meeting Resource**: The `MeetingResource` has been enhanced to include `has_submitted_survey` and `survey_submitted_at` fields, allowing the frontend to check submission status without an additional API call.

