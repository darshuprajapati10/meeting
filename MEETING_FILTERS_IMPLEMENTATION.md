# Meeting Filters Implementation

## Overview
Backend API filtering for meetings has been implemented across all calendar and meeting endpoints. Filters are applied server-side for better performance and scalability.

## Implementation Details

### Files Modified

1. **`app/Traits/AppliesMeetingFilters.php`** (NEW)
   - Trait containing the `applyMeetingFilters()` method
   - Handles all filter logic: meeting_type, attendees, duration, status
   - Used by both CalendarController and MeetingController

2. **`app/Services/CalendarService.php`**
   - Updated `buildMonth()`, `buildWeek()`, and `buildDay()` methods
   - Added optional `$queryModifier` parameter to accept filter closures

3. **`app/Http/Controllers/Api/CalendarController.php`**
   - Updated `currentMonth()`, `currentWeek()`, and `currentDay()` methods
   - Added filter validation and application
   - Returns `filters_applied` in response

4. **`app/Http/Controllers/Api/MeetingController.php`**
   - Updated `index()` method
   - Added filter validation and application
   - Returns `filters_applied` in response

## Filter Parameters

### Request Format
```json
{
  "year": 2025,
  "month": 12,
  "filters": {
    "meeting_type": "Video Call",
    "attendees": "small",
    "duration": "30",
    "status": "upcoming"
  }
}
```

### Filter Details

| Parameter | Type | Values | Description |
|-----------|------|--------|-------------|
| `filters.meeting_type` | string | "Video Call", "In-Person Meeting", "Phone Call", "Online Meeting" | Filter by meeting type |
| `filters.attendees` | string | "1-on-1", "small", "medium", "large" | Filter by attendee count |
| `filters.duration` | string | "15", "30", "60", "120" | Filter by duration in minutes |
| `filters.status` | string | "upcoming", "completed", "cancelled" | Filter by meeting status |

### Filter Logic

#### Meeting Type
- Exact match on `meeting_type` column
- Values: "Video Call", "In-Person Meeting", "Phone Call", "Online Meeting"

#### Attendees
- Uses subquery to count attendees from `meeting_attendees` table
- **1-on-1**: Exactly 1 attendee
- **small**: 2-5 attendees
- **medium**: 6-15 attendees
- **large**: 16+ attendees

#### Duration
- Exact match for 15, 30, 60 minutes
- For 120: `duration >= 120` (2+ hours)

#### Status
- Maps frontend values to database values:
  - `upcoming` → `['Created', 'Scheduled']`
  - `completed` → `'Completed'`
  - `cancelled` → `'Cancelled'`

## Affected Endpoints

### 1. `/api/meeting/current-month` (POST)
- **Controller**: `CalendarController@currentMonth`
- **Filters**: All filter types supported
- **Response**: Includes `filters_applied` field

### 2. `/api/meeting/current-week` (POST)
- **Controller**: `CalendarController@currentWeek`
- **Filters**: All filter types supported
- **Response**: Includes `filters_applied` field

### 3. `/api/meeting/current-day` (POST)
- **Controller**: `CalendarController@currentDay`
- **Filters**: All filter types supported
- **Response**: Includes `filters_applied` field

### 4. `/api/meeting/index` (POST)
- **Controller**: `MeetingController@index`
- **Filters**: All filter types supported
- **Response**: Includes `filters_applied` field (if filters provided)

## Example API Calls

### Get Current Month with Filters
```bash
curl -X POST http://localhost:8000/api/meeting/current-month \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "year": 2025,
    "month": 12,
    "filters": {
      "meeting_type": "Video Call",
      "status": "upcoming"
    }
  }'
```

### Get Current Week with Small Group Meetings
```bash
curl -X POST http://localhost:8000/api/meeting/current-week \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-12-18",
    "filters": {
      "attendees": "small",
      "duration": "30"
    }
  }'
```

### Get Today's Cancelled Meetings
```bash
curl -X POST http://localhost:8000/api/meeting/current-day \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "filters": {
      "status": "cancelled"
    }
  }'
```

### Get Meetings List with Filters
```bash
curl -X POST http://localhost:8000/api/meeting/index \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "filters": {
      "meeting_type": "Video Call",
      "attendees": "medium",
      "duration": "60",
      "status": "upcoming"
    }
  }'
```

## Response Format

All endpoints return a `filters_applied` field when filters are provided:

```json
{
  "success": true,
  "data": {
    "meetings": [...],
    "filters_applied": {
      "meeting_type": "Video Call",
      "status": "upcoming"
    }
  },
  "message": "Meetings retrieved successfully."
}
```

## Validation

All filter parameters are validated:
- `filters.meeting_type`: Must be one of the valid meeting types
- `filters.attendees`: Must be "1-on-1", "small", "medium", or "large"
- `filters.duration`: Must be "15", "30", "60", or "120"
- `filters.status`: Must be "upcoming", "completed", or "cancelled"

Invalid filter values will return a 422 validation error.

## Database Schema

### Meetings Table
- `meeting_type`: ENUM('Video Call', 'In-Person Meeting', 'Phone Call', 'Online Meeting')
- `status`: ENUM('Created', 'Scheduled', 'Completed', 'Cancelled', 'Pending', 'Rescheduled')
- `duration`: INTEGER (minutes)

### Meeting Attendees Table
- `meeting_id`: Foreign key to meetings
- `contact_id`: Foreign key to contacts
- Used for attendee count filtering

## Testing Checklist

- [x] Filter by meeting type only
- [x] Filter by attendees only (1-on-1, small, medium, large)
- [x] Filter by duration only (15, 30, 60, 120)
- [x] Filter by status only (upcoming, completed, cancelled)
- [x] Multiple filters combined
- [x] Empty filters (return all)
- [x] Invalid filter values (validation error)
- [x] Filters work with month view
- [x] Filters work with week view
- [x] Filters work with day view
- [x] Filters work with meetings index

## Notes

1. **Status Mapping**: The frontend uses "upcoming" which maps to both "Created" and "Scheduled" statuses in the database.

2. **Attendee Count**: Uses a subquery to count attendees, which works efficiently with pagination and other query operations.

3. **Duration Filter**: For 120 minutes, the filter uses `>= 120` to include all meetings 2 hours or longer.

4. **Backward Compatibility**: All endpoints continue to work without filters. Filters are optional.

5. **Performance**: Filters are applied at the database level for optimal performance, especially with large datasets.

