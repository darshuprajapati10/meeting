# Survey Submission Tracking - Implementation Complete ✅

## Overview
This feature tracks whether a user has already submitted a survey for a meeting. When a user tries to fill a survey for the second time, they see a "Survey Already Submitted" popup instead of the survey form.

## ✅ Implementation Complete

### 1. Database Migration
**File**: `database/migrations/2025_12_18_121318_create_survey_submissions_table.php`

- Created `survey_submissions` table
- Tracks: `user_id`, `meeting_id`, `survey_id`, `submitted_at`
- Unique constraint prevents duplicate submissions
- Foreign keys with cascade delete

### 2. SurveySubmission Model
**File**: `app/Models/SurveySubmission.php`

- Relationships: `user()`, `meeting()`, `survey()`
- Fillable fields configured
- Timestamps and casts set up

### 3. Meeting Model Updates
**File**: `app/Models/Meeting.php`

- Added `surveySubmissions()` relationship
- Added `hasUserSubmittedSurvey($userId)` method
- Checks if user has submitted survey for this meeting

### 4. MeetingResource Updates
**File**: `app/Http/Resources/MeetingResource.php`

- Added `has_submitted_survey` field to API response
- Automatically checks current user's submission status
- Returns `true` if user has submitted, `false` otherwise

### 5. SurveyStepController Updates
**File**: `app/Http/Controllers/Api/SurveyStepController.php`

- Records submission when survey is filled via `/api/survey-step/save`
- Uses `updateOrCreate` to prevent duplicates
- Only records if `meeting_id` is provided and `field_values` exist

## API Response Format

### Meeting List API (`POST /api/meeting/index`)

```json
{
  "data": [
    {
      "id": 1,
      "meeting_title": "Weekly Standup",
      "date": "2025-12-20",
      "time": "10:00",
      "duration": 30,
      "status": "completed",
      "survey_id": 5,
      "survey": {
        "id": 5,
        "survey_name": "Meeting Feedback"
      },
      "attendees": [...],
      "has_submitted_survey": true,  // ← NEW FIELD
      "created_at": "2025-12-18T10:00:00.000000Z",
      "updated_at": "2025-12-18T10:00:00.000000Z"
    }
  ],
  "meta": {...},
  "statistics": {...}
}
```

### Meeting Show API (`POST /api/meeting/show`)

```json
{
  "data": {
    "id": 1,
    "meeting_title": "Weekly Standup",
    "has_submitted_survey": false,  // ← NEW FIELD
    ...
  },
  "message": "Meeting retrieved successfully."
}
```

## How It Works

### Flow:
1. **User fills survey** → `POST /api/survey-step/save` with `meeting_id` and `field_values`
2. **Backend records submission** → Creates/updates record in `survey_submissions` table
3. **User requests meetings** → `POST /api/meeting/index`
4. **Backend checks submission** → `hasUserSubmittedSurvey()` method checks database
5. **API returns status** → `has_submitted_survey: true/false` in response
6. **Flutter shows popup** → If `true`, shows "Survey Already Submitted" instead of form

### Database Structure:

```sql
survey_submissions
├── id
├── user_id (FK → users)
├── meeting_id (FK → meetings)
├── survey_id (FK → surveys)
├── submitted_at
├── created_at
├── updated_at
└── UNIQUE (user_id, meeting_id, survey_id)
```

## Next Steps

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Test the Feature

**Test Case 1: First Submission**
```bash
# Fill survey for meeting
POST /api/survey-step/save
{
  "survey_id": 1,
  "meeting_id": 1,
  "field_values": {"1": "Answer"}
}

# Check meeting API
POST /api/meeting/show
{
  "id": 1
}

# Expected: "has_submitted_survey": true
```

**Test Case 2: Second Submission Attempt**
```bash
# Try to fill same survey again
POST /api/survey-step/save
{
  "survey_id": 1,
  "meeting_id": 1,
  "field_values": {"1": "New Answer"}
}

# Submission is updated (not duplicated)
# Meeting API still returns: "has_submitted_survey": true
```

**Test Case 3: Different Meeting**
```bash
# Fill survey for different meeting
POST /api/survey-step/save
{
  "survey_id": 1,
  "meeting_id": 2,  // Different meeting
  "field_values": {"1": "Answer"}
}

# Both meetings show: "has_submitted_survey": true
```

## Field Details

| Field | Type | Description |
|-------|------|-------------|
| `has_submitted_survey` | boolean | `true` if current authenticated user has submitted survey for this meeting, `false` otherwise |

## Important Notes

1. **User-Specific**: Submission tracking is per-user. Different users can submit surveys for the same meeting independently.

2. **Meeting-Specific**: Each meeting tracks submissions separately. Same survey in different meetings = separate submissions.

3. **Survey-Specific**: Each survey is tracked separately. If a meeting has multiple surveys, each is tracked independently.

4. **Automatic Tracking**: Submission is automatically recorded when:
   - Survey is filled via `/api/survey-step/save`
   - `meeting_id` is provided
   - `field_values` exist and are not empty

5. **No Manual API Needed**: The Flutter app doesn't need to call a separate API - the status is included in the meeting API response.

## Troubleshooting

### Issue: `has_submitted_survey` always returns `false`

**Check:**
1. Is migration run? `php artisan migrate:status`
2. Is `meeting_id` being sent in survey-step/save request?
3. Are `field_values` being sent?
4. Check logs: `tail -f storage/logs/laravel.log | grep "Survey submission recorded"`

### Issue: Duplicate submissions

**Solution**: Unique constraint prevents this. If error occurs, check:
- Are `user_id`, `meeting_id`, `survey_id` all being set correctly?
- Is the unique constraint working? Check database.

### Issue: Submission not recorded

**Check:**
1. Verify `meeting_id` is in request
2. Verify `field_values` exist and are not empty
3. Check transaction is committing (no rollback)
4. Check database: `SELECT * FROM survey_submissions WHERE user_id = ?`

## Database Queries for Testing

```sql
-- Check all submissions
SELECT * FROM survey_submissions;

-- Check submissions for a user
SELECT * FROM survey_submissions WHERE user_id = 1;

-- Check submissions for a meeting
SELECT * FROM survey_submissions WHERE meeting_id = 1;

-- Check if user submitted for specific meeting
SELECT * FROM survey_submissions 
WHERE user_id = 1 
  AND meeting_id = 1 
  AND survey_id = 1;
```

## ✅ Implementation Status

- [x] Database migration created
- [x] SurveySubmission model created
- [x] Meeting model updated with method
- [x] MeetingResource includes `has_submitted_survey`
- [x] SurveyStepController records submissions
- [x] Unique constraint prevents duplicates
- [x] Logging added for debugging

**Ready for testing!** 🚀

