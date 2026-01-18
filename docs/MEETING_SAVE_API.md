# Meeting Save API Documentation

## Overview
This API endpoint allows you to create or update a meeting with attendees and notifications. The same endpoint handles both operations - if an `id` is provided, it updates the existing meeting; otherwise, it creates a new one. Meetings can have multiple attendees (contacts) and multiple notification rules.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/meeting/save`  
**Authentication:** Required (Bearer Token via Laravel Sanctum)

---

## Headers

```
Authorization: Bearer <your-auth-token>
Content-Type: application/json
Accept: application/json
```

---

## Request Body

### Creating a New Meeting

When creating a meeting, **do not** include the `id` field:

```json
{
  "meeting_title": "Project Kickoff Meeting",
  "status": "Created",
  "date": "2025-11-15",
  "time": "14:30",
  "duration": 30,
  "meeting_type": "Video Call",
  "custom_location": "https://meet.example.com/room123",
  "survey_id": null,
  "agenda_notes": "Discuss project goals, timeline, and team roles.",
  "attendees": [1, 2, 3],
  "notifications": [
    {
      "minutes": 30,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 15,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": false
    }
  ]
}
```

### Updating an Existing Meeting

When updating a meeting, **include** the `id` field:

```json
{
  "id": 1,
  "meeting_title": "Updated Project Meeting",
  "status": "Scheduled",
  "date": "2025-11-15",
  "time": "15:00",
  "duration": 60,
  "meeting_type": "In-Person Meeting",
  "custom_location": "Conference Room A, 123 Main St",
  "survey_id": 2,
  "agenda_notes": "Updated agenda with new discussion points.",
  "attendees": [1, 2, 4, 5],
  "notifications": [
    {
      "minutes": 60,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    }
  ]
```

### Example 4: Multiple Reminders with Different Units

Meeting with reminders at different times (1 day before, 1 hour before, 30 minutes before, 10 minutes before):

```json
{
  "meeting_title": "Quarterly Review Meeting",
  "status": "Scheduled",
  "date": "2026-01-20",
  "time": "14:00",
  "duration": 60,
  "meeting_type": "Video Call",
  "custom_location": "https://meet.example.com/q1-review",
  "survey_id": null,
  "agenda_notes": "Q1 2026 performance review and planning",
  "attendees": [1, 2, 3],
  "notifications": [
    {
      "minutes": 1,
      "unit": "days",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 1,
      "unit": "hours",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 30,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 10,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    }
  ]
}
```

**Notification Timeline:**
- 1 day before: January 19, 2026 at 2:00 PM
- 1 hour before: January 20, 2026 at 1:00 PM
- 30 minutes before: January 20, 2026 at 1:30 PM
- 10 minutes before: January 20, 2026 at 1:50 PM
- Automatic "Starting Soon": January 20, 2026 at 1:55 PM (5 minutes before)

### Example 5: Simple Format for Minutes-Based Reminders

You can use a simple array format `[5, 10, 15]` for minutes-based reminders (automatically converted to full format):

```json
{
  "meeting_title": "Team Standup",
  "status": "Scheduled",
  "date": "2026-01-15",
  "time": "09:00",
  "duration": 15,
  "meeting_type": "Video Call",
  "attendees": [1, 2],
  "notifications": [5, 10, 15]
}
```

---

## Field Specifications

### Meeting Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | Meeting ID (only for updates) | Must exist in meetings table |
| `meeting_title` | string | **Yes** | Meeting title/name | Max 255 characters |
| `status` | string | **Yes** | Meeting status | Must be: `Created`, `Scheduled`, `Completed`, or `Cancelled` |
| `date` | string (date) | **Yes** | Meeting date | Format: `YYYY-MM-DD` (e.g., "2025-11-15") |
| `time` | string (time) | **Yes** | Meeting time | Format: `HH:MM` (e.g., "14:30") |
| `duration` | integer | **Yes** | Meeting duration in minutes | Must be one of: `15`, `30`, `45`, `60`, `90`, `120` |
| `meeting_type` | string | **Yes** | Type of meeting | Must be: `Video Call`, `In-Person Meeting`, `Phone Call`, or `Online Meeting` |
| `custom_location` | string | No | Custom location or meeting link | Max 500 characters |
| `survey_id` | integer | No | ID of survey to attach | Must exist in surveys table |
| `agenda_notes` | string | No | Meeting agenda, talking points, or notes | No max length |
| `attendees` | array | No | Array of contact IDs | Each ID must exist in contacts table |
| `notifications` | array | No | Array of notification rules | See notification structure below |

### Notification Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `minutes` | integer | **Yes** | Number of minutes/hours/days before/after | Must be at least 1 |
| `unit` | string | **Yes** | Time unit | Must be: `minutes`, `hours`, or `days` |
| `trigger` | string | **Yes** | When to trigger notification | Must be: `before` or `after` |
| `is_enabled` | boolean | No | Whether notification is enabled | Default: `true` |

---

## Response Examples

### Success - Meeting Created (201)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "meeting_title": "Project Kickoff Meeting",
    "status": "Created",
    "date": "2025-11-15",
    "time": "14:30",
    "duration": 30,
    "meeting_type": "Video Call",
    "custom_location": "https://meet.example.com/room123",
    "survey_id": null,
    "survey": null,
    "agenda_notes": "Discuss project goals, timeline, and team roles.",
    "created_by": 1,
    "attendees": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "1234567890"
      },
      {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane@example.com",
        "phone": "0987654321"
      }
    ],
    "notifications": [
      {
        "id": 1,
        "minutes": 30,
        "unit": "minutes",
        "trigger": "before",
        "is_enabled": true
      },
      {
        "id": 2,
        "minutes": 15,
        "unit": "minutes",
        "trigger": "before",
        "is_enabled": false
      }
    ],
    "created_at": "2025-11-04T10:30:00.000000Z",
    "updated_at": "2025-11-04T10:30:00.000000Z"
  },
  "message": "Meeting created successfully."
}
```

### Success - Meeting Updated (200)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "meeting_title": "Updated Project Meeting",
    "status": "Scheduled",
    "date": "2025-11-15",
    "time": "15:00",
    "duration": 60,
    "meeting_type": "In-Person Meeting",
    "custom_location": "Conference Room A, 123 Main St",
    "survey_id": 2,
    "survey": {
      "id": 2,
      "survey_name": "Project Survey"
    },
    "agenda_notes": "Updated agenda with new discussion points.",
    "created_by": 1,
    "attendees": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "1234567890"
      },
      {
        "id": 4,
        "first_name": "Bob",
        "last_name": "Johnson",
        "email": "bob@example.com",
        "phone": "5555555555"
      }
    ],
    "notifications": [
      {
        "id": 3,
        "minutes": 60,
        "unit": "minutes",
        "trigger": "before",
        "is_enabled": true
      }
    ],
    "created_at": "2025-11-04T10:30:00.000000Z",
    "updated_at": "2025-11-04T11:45:00.000000Z"
  },
  "message": "Meeting updated successfully."
}
```

### Error - Validation Failed (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "meeting_title": [
      "The meeting title field is required."
    ],
    "date": [
      "The date field is required."
    ],
    "time": [
      "The time must be a valid time."
    ],
    "duration": [
      "The selected duration is invalid."
    ],
    "attendees.0": [
      "The selected attendees.0 is invalid."
    ]
  }
}
```

### Error - Meeting Not Found (404)

When trying to update a meeting that doesn't exist or doesn't belong to the user's organization:

```json
{
  "message": "No query results for model [App\\Models\\Meeting] {id}"
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Create a new meeting
async function createMeeting(meetingData, token) {
  try {
    const response = await fetch('http://your-api-url/api/meeting/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        meeting_title: meetingData.title,
        status: meetingData.status,
        date: meetingData.date,
        time: meetingData.time,
        duration: meetingData.duration,
        meeting_type: meetingData.type,
        custom_location: meetingData.location || null,
        survey_id: meetingData.surveyId || null,
        agenda_notes: meetingData.agenda || null,
        attendees: meetingData.attendees || [],
        notifications: meetingData.notifications || []
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to save meeting');
    }

    return data;
  } catch (error) {
    console.error('Error saving meeting:', error);
    throw error;
  }
}

// Update an existing meeting
async function updateMeeting(meetingId, meetingData, token) {
  try {
    const response = await fetch('http://your-api-url/api/meeting/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: meetingId,
        meeting_title: meetingData.title,
        status: meetingData.status,
        date: meetingData.date,
        time: meetingData.time,
        duration: meetingData.duration,
        meeting_type: meetingData.type,
        custom_location: meetingData.location || null,
        survey_id: meetingData.surveyId || null,
        agenda_notes: meetingData.agenda || null,
        attendees: meetingData.attendees || [],
        notifications: meetingData.notifications || []
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update meeting');
    }

    return data;
  } catch (error) {
    console.error('Error updating meeting:', error);
    throw error;
  }
}
```

### Axios Example

```javascript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://your-api-url/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add token to requests
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Create or update meeting
async function saveMeeting(meetingData) {
  try {
    const response = await apiClient.post('/meeting/save', {
      // Include id only for updates
      ...(meetingData.id && { id: meetingData.id }),
      meeting_title: meetingData.title,
      status: meetingData.status,
      date: meetingData.date,
      time: meetingData.time,
      duration: meetingData.duration,
      meeting_type: meetingData.type,
      custom_location: meetingData.location || null,
      survey_id: meetingData.surveyId || null,
      agenda_notes: meetingData.agenda || null,
      attendees: meetingData.attendees || [],
      notifications: meetingData.notifications || []
    });

    return response.data;
  } catch (error) {
    if (error.response) {
      // Handle validation errors
      console.error('Validation errors:', error.response.data.errors);
      throw error.response.data;
    }
    throw error;
  }
}
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface NotificationRule {
  minutes: number;
  unit: 'minutes' | 'hours' | 'days';
  trigger: 'before' | 'after';
  is_enabled?: boolean;
}

interface MeetingFormData {
  id?: number;
  title: string;
  status: 'Created' | 'Scheduled' | 'Completed' | 'Cancelled';
  date: string;
  time: string;
  duration: 15 | 30 | 45 | 60 | 90 | 120;
  type: 'Video Call' | 'In-Person Meeting' | 'Phone Call' | 'Online Meeting';
  location?: string;
  surveyId?: number | null;
  agenda?: string;
  attendees?: number[];
  notifications?: NotificationRule[];
}

export function useSaveMeeting() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const saveMeeting = async (formData: MeetingFormData, token: string) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post(
        '/api/meeting/save',
        {
          ...(formData.id && { id: formData.id }),
          meeting_title: formData.title,
          status: formData.status,
          date: formData.date,
          time: formData.time,
          duration: formData.duration,
          meeting_type: formData.type,
          custom_location: formData.location || null,
          survey_id: formData.surveyId || null,
          agenda_notes: formData.agenda || null,
          attendees: formData.attendees || [],
          notifications: formData.notifications || []
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
        }
      );

      return response.data;
    } catch (err: any) {
      const errorData = err.response?.data || err.message;
      setError(errorData);
      throw errorData;
    } finally {
      setLoading(false);
    }
  };

  return { saveMeeting, loading, error };
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function MeetingForm({ token, onSaveSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({
    meeting_title: '',
    status: 'Created',
    date: '',
    time: '',
    duration: 30,
    meeting_type: 'Video Call',
    custom_location: '',
    survey_id: null,
    agenda_notes: '',
    attendees: [],
    notifications: []
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/meeting/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to save meeting');
      }

      if (onSaveSuccess) {
        onSaveSuccess(data.data);
      }

      alert(data.message || 'Meeting saved successfully');
    } catch (err) {
      setError(err.message);
      alert('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const addNotification = () => {
    setFormData(prev => ({
      ...prev,
      notifications: [
        ...prev.notifications,
        {
          minutes: 30,
          unit: 'minutes',
          trigger: 'before',
          is_enabled: true
        }
      ]
    }));
  };

  const removeNotification = (index) => {
    setFormData(prev => ({
      ...prev,
      notifications: prev.notifications.filter((_, i) => i !== index)
    }));
  };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>Meeting Title *</label>
        <input
          type="text"
          value={formData.meeting_title}
          onChange={(e) => setFormData(prev => ({ ...prev, meeting_title: e.target.value }))}
          required
        />
      </div>

      <div>
        <label>Status *</label>
        <select
          value={formData.status}
          onChange={(e) => setFormData(prev => ({ ...prev, status: e.target.value }))}
          required
        >
          <option value="Created">Created</option>
          <option value="Scheduled">Scheduled</option>
          <option value="Completed">Completed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>

      <div>
        <label>Date *</label>
        <input
          type="date"
          value={formData.date}
          onChange={(e) => setFormData(prev => ({ ...prev, date: e.target.value }))}
          required
        />
      </div>

      <div>
        <label>Time *</label>
        <input
          type="time"
          value={formData.time}
          onChange={(e) => setFormData(prev => ({ ...prev, time: e.target.value }))}
          required
        />
      </div>

      <div>
        <label>Duration (minutes) *</label>
        <div>
          {[15, 30, 45, 60, 90, 120].map(duration => (
            <button
              key={duration}
              type="button"
              onClick={() => setFormData(prev => ({ ...prev, duration }))}
              className={formData.duration === duration ? 'active' : ''}
            >
              {duration}m
            </button>
          ))}
        </div>
      </div>

      <div>
        <label>Meeting Type *</label>
        <div>
          {['Video Call', 'In-Person Meeting', 'Phone Call', 'Online Meeting'].map(type => (
            <button
              key={type}
              type="button"
              onClick={() => setFormData(prev => ({ ...prev, meeting_type: type }))}
              className={formData.meeting_type === type ? 'active' : ''}
            >
              {type}
            </button>
          ))}
        </div>
      </div>

      <div>
        <label>Custom Location/Link</label>
        <input
          type="text"
          value={formData.custom_location}
          onChange={(e) => setFormData(prev => ({ ...prev, custom_location: e.target.value }))}
          placeholder="Or enter custom location/link..."
        />
      </div>

      <div>
        <label>Attach Survey</label>
        <select
          value={formData.survey_id || ''}
          onChange={(e) => setFormData(prev => ({ ...prev, survey_id: e.target.value ? parseInt(e.target.value) : null }))}
        >
          <option value="">No survey</option>
          {/* Populate with surveys */}
        </select>
      </div>

      <div>
        <label>Agenda & Notes</label>
        <textarea
          value={formData.agenda_notes}
          onChange={(e) => setFormData(prev => ({ ...prev, agenda_notes: e.target.value }))}
          placeholder="Meeting agenda, talking points, or additional notes..."
        />
      </div>

      <div>
        <label>Attendees</label>
        <select
          multiple
          value={formData.attendees}
          onChange={(e) => setFormData(prev => ({ 
            ...prev, 
            attendees: Array.from(e.target.selectedOptions, option => parseInt(option.value))
          }))}
        >
          {/* Populate with contacts */}
        </select>
      </div>

      <div>
        <label>
          Notifications
          <button type="button" onClick={addNotification}>Add</button>
        </label>
        {formData.notifications.map((notification, index) => (
          <div key={index}>
            <input
              type="checkbox"
              checked={notification.is_enabled}
              onChange={(e) => {
                const updated = [...formData.notifications];
                updated[index].is_enabled = e.target.checked;
                setFormData(prev => ({ ...prev, notifications: updated }));
              }}
            />
            <input
              type="number"
              value={notification.minutes}
              onChange={(e) => {
                const updated = [...formData.notifications];
                updated[index].minutes = parseInt(e.target.value);
                setFormData(prev => ({ ...prev, notifications: updated }));
              }}
            />
            <select
              value={notification.unit}
              onChange={(e) => {
                const updated = [...formData.notifications];
                updated[index].unit = e.target.value;
                setFormData(prev => ({ ...prev, notifications: updated }));
              }}
            >
              <option value="minutes">minutes</option>
              <option value="hours">hours</option>
              <option value="days">days</option>
            </select>
            <span>before</span>
            <button type="button" onClick={() => removeNotification(index)}>X</button>
          </div>
        ))}
      </div>

      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Meeting'}
      </button>

      {error && <div style={{ color: 'red' }}>{error}</div>}
    </form>
  );
}
```

---

## cURL Examples

### Create Meeting

```bash
curl -X POST "http://your-api-url/api/meeting/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "meeting_title": "Project Kickoff Meeting",
    "status": "Created",
    "date": "2025-11-15",
    "time": "14:30",
    "duration": 30,
    "meeting_type": "Video Call",
    "custom_location": "https://meet.example.com/room123",
    "survey_id": null,
    "agenda_notes": "Discuss project goals and timeline.",
    "attendees": [1, 2, 3],
    "notifications": [
      {
        "minutes": 30,
        "unit": "minutes",
        "trigger": "before",
        "is_enabled": true
      }
    ]
  }'
```

### Update Meeting

```bash
curl -X POST "http://your-api-url/api/meeting/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1,
    "meeting_title": "Updated Meeting Title",
    "status": "Scheduled",
    "date": "2025-11-15",
    "time": "15:00",
    "duration": 60,
    "meeting_type": "In-Person Meeting",
    "custom_location": "Conference Room A",
    "survey_id": 2,
    "agenda_notes": "Updated agenda.",
    "attendees": [1, 2, 4],
    "notifications": [
      {
        "minutes": 60,
        "unit": "minutes",
        "trigger": "before",
        "is_enabled": true
      }
    ]
  }'
```

---

## Important Notes

### 1. Organization Auto-Creation
- If a user doesn't have an organization associated with their account, the system will **automatically create** a personal organization for them
- The organization will be named: `"{User Name}'s Organization"`
- The user will be automatically assigned the "admin" role in this organization
- This happens transparently - no additional API calls needed

### 2. Attendees
- `attendees` is an array of contact IDs
- Each contact ID must exist in the contacts table
- Contacts must belong to the user's organization
- When updating, existing attendees are replaced with the new list (sync operation)
- Empty array `[]` will remove all attendees

### 3. Notifications
- `notifications` is an array of notification rules
- Each notification has:
  - `minutes`: Number of time units (must be at least 1)
  - `unit`: `minutes`, `hours`, or `days`
  - `trigger`: `before` or `after` the meeting
  - `is_enabled`: Boolean (default: `true`)
- When updating, existing notifications are deleted and replaced with new ones
- Empty array `[]` will remove all notifications

### 4. Duration Options
- Valid duration values: `15`, `30`, `45`, `60`, `90`, `120` (minutes)
- Default is `30` minutes if not specified

### 5. Meeting Types
- Valid types: `Video Call`, `In-Person Meeting`, `Phone Call`, `Online Meeting`
- Default is `Video Call` if not specified

### 6. Status Options
- Valid statuses: `Created`, `Scheduled`, `Completed`, `Cancelled`
- Default is `Created` if not specified

### 7. Date and Time Format
- Date: `YYYY-MM-DD` format (e.g., "2025-11-15")
- Time: `HH:MM` format in 24-hour format (e.g., "14:30" for 2:30 PM)

### 8. Survey Attachment
- `survey_id` can be `null` or an integer
- If provided, must exist in the surveys table
- Survey must belong to the user's organization

### 9. Update Behavior
- When updating (with `id`), only meetings within the user's organization can be updated
- Attempting to update a meeting from a different organization will result in a 404 error
- When updating, all notifications are deleted and replaced with new ones
- Attendees are synced (replaced) with the new list

### 10. Authentication
- All requests must include a valid Bearer token in the Authorization header
- Tokens are obtained through the login/signup endpoints

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Meeting updated successfully |
| `201` | Meeting created successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Meeting not found (for updates) |
| `422` | Validation error |
| `500` | Server error |

---

## Error Handling Best Practices

1. **Check Response Status**: Always check the HTTP status code before processing the response
2. **Handle Validation Errors**: Display field-specific errors from `errors` object
3. **Network Errors**: Handle timeout and network connectivity issues
4. **Token Expiration**: Handle 401 errors by redirecting to login

Example error handling:

```javascript
try {
  const response = await fetch('/api/meeting/save', {...});
  const data = await response.json();
  
  if (!response.ok) {
    if (response.status === 422) {
      // Handle validation errors
      Object.keys(data.errors).forEach(field => {
        console.error(`${field}: ${data.errors[field].join(', ')}`);
      });
    } else if (response.status === 401) {
      // Handle authentication error
      // Redirect to login
    } else {
      // Handle other errors
      console.error(data.message);
    }
    return;
  }
  
  // Success
  console.log('Meeting saved:', data.data);
} catch (error) {
  console.error('Network error:', error);
}
```

---

## Example Request Bodies

### Example 1: Simple Meeting (No Attendees, No Notifications)

```json
{
  "meeting_title": "Quick Team Sync",
  "status": "Created",
  "date": "2025-11-15",
  "time": "10:00",
  "duration": 15,
  "meeting_type": "Video Call",
  "custom_location": null,
  "survey_id": null,
  "agenda_notes": "Brief team status update.",
  "attendees": [],
  "notifications": []
}
```

### Example 2: Full Featured Meeting

```json
{
  "meeting_title": "Quarterly Business Review",
  "status": "Scheduled",
  "date": "2025-12-01",
  "time": "09:00",
  "duration": 120,
  "meeting_type": "In-Person Meeting",
  "custom_location": "Main Conference Room, Floor 5, 123 Business St",
  "survey_id": 5,
  "agenda_notes": "1. Review Q4 results\n2. Discuss Q1 goals\n3. Budget planning\n4. Team updates",
  "attendees": [1, 2, 3, 4, 5, 6],
  "notifications": [
    {
      "minutes": 1,
      "unit": "days",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 2,
      "unit": "hours",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 30,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 15,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": false
    }
  ]
}
```

### Example 3: Online Meeting with Custom Link

```json
{
  "meeting_title": "Client Demo Presentation",
  "status": "Scheduled",
  "date": "2025-11-20",
  "time": "14:00",
  "duration": 60,
  "meeting_type": "Online Meeting",
  "custom_location": "https://zoom.us/j/123456789?pwd=abc123",
  "survey_id": null,
  "agenda_notes": "Demo new features and gather client feedback.",
  "attendees": [10, 11, 12],
  "notifications": [
    {
      "minutes": 24,
      "unit": "hours",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 10,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    }
  ]
}
```

### Example 4: Phone Call Meeting

```json
{
  "meeting_title": "Follow-up Call with Client",
  "status": "Created",
  "date": "2025-11-12",
  "time": "16:00",
  "duration": 30,
  "meeting_type": "Phone Call",
  "custom_location": "+1 (555) 123-4567",
  "survey_id": null,
  "agenda_notes": "Discuss contract terms and pricing.",
  "attendees": [7],
  "notifications": [
    {
      "minutes": 15,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    }
  ]
}
```

### Example 4: Multiple Reminders with Different Units

Meeting with reminders at different times (1 day before, 1 hour before, 30 minutes before, 10 minutes before):

```json
{
  "meeting_title": "Quarterly Review Meeting",
  "status": "Scheduled",
  "date": "2026-01-20",
  "time": "14:00",
  "duration": 60,
  "meeting_type": "Video Call",
  "custom_location": "https://meet.example.com/q1-review",
  "survey_id": null,
  "agenda_notes": "Q1 2026 performance review and planning",
  "attendees": [1, 2, 3],
  "notifications": [
    {
      "minutes": 1,
      "unit": "days",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 1,
      "unit": "hours",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 30,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    },
    {
      "minutes": 10,
      "unit": "minutes",
      "trigger": "before",
      "is_enabled": true
    }
  ]
}
```

**Notification Timeline for Example 4:**
- 1 day before: January 19, 2026 at 2:00 PM
- 1 hour before: January 20, 2026 at 1:00 PM
- 30 minutes before: January 20, 2026 at 1:30 PM
- 10 minutes before: January 20, 2026 at 1:50 PM
- Automatic "Starting Soon": January 20, 2026 at 1:55 PM (5 minutes before, sent automatically)

### Example 5: Simple Format for Minutes-Based Reminders

You can use a simple array format `[5, 10, 15]` for minutes-based reminders (automatically converted to full format):

```json
{
  "meeting_title": "Team Standup",
  "status": "Scheduled",
  "date": "2026-01-15",
  "time": "09:00",
  "duration": 15,
  "meeting_type": "Video Call",
  "attendees": [1, 2],
  "notifications": [5, 10, 15]
}
```

This is equivalent to:
```json
{
  "notifications": [
    {"minutes": 5, "unit": "minutes", "trigger": "before", "is_enabled": true},
    {"minutes": 10, "unit": "minutes", "trigger": "before", "is_enabled": true},
    {"minutes": 15, "unit": "minutes", "trigger": "before", "is_enabled": true}
  ]
}
```

**Notes on Multiple Reminders:**
- Duplicate reminder timings are automatically deduplicated
- Maximum 10 reminders per meeting
- Reminders are only scheduled if the calculated time is in the future
- Reminders cannot be scheduled after the meeting time
- Simple format `[5, 10, 15]` automatically removes duplicates
- If no reminders are provided, a default 1-hour reminder is used

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/meeting/save` with Bearer token and request body
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

