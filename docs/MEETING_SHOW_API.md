# Meeting Show API Documentation

## Overview
This API endpoint retrieves a single meeting by its ID. It returns the complete meeting data including attendees and notifications for viewing or editing. The meeting must belong to the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/meeting/show`  
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

```json
{
  "id": 1
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | Meeting ID to retrieve |

---

## Response Format

### Success Response (200)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "meeting_title": "Project Kickoff Meeting",
    "status": "Scheduled",
    "date": "2025-11-15",
    "time": "14:30",
    "duration": 30,
    "meeting_type": "Video Call",
    "custom_location": "https://meet.example.com/room123",
    "survey_id": 2,
    "survey": {
      "id": 2,
      "survey_name": "Customer Satisfaction Survey"
    },
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
  "message": "Meeting retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Complete meeting data (MeetingResource) |
| `data.id` | integer | Meeting ID |
| `data.organization_id` | integer | Organization ID |
| `data.meeting_title` | string | Meeting title/name |
| `data.status` | string | Meeting status (Created, Scheduled, Completed, Cancelled) |
| `data.date` | string | Meeting date (YYYY-MM-DD format) |
| `data.time` | string | Meeting time (HH:MM format) |
| `data.duration` | integer | Meeting duration in minutes |
| `data.meeting_type` | string | Type of meeting (Video Call, In-Person Meeting, Phone Call, Online Meeting) |
| `data.custom_location` | string\|null | Custom location or meeting link |
| `data.survey_id` | integer\|null | ID of attached survey (if any) |
| `data.survey` | object\|null | Survey object (if survey_id exists) |
| `data.agenda_notes` | string\|null | Meeting agenda, talking points, or notes |
| `data.created_by` | integer | User ID who created the meeting |
| `data.attendees` | array | Array of attendee contact objects |
| `data.notifications` | array | Array of notification rules |
| `data.created_at` | string | Creation timestamp (ISO 8601) |
| `data.updated_at` | string | Last update timestamp (ISO 8601) |
| `message` | string | Success message |

### Attendee Object Structure

Each attendee in the `attendees` array contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Contact ID |
| `first_name` | string | Contact's first name |
| `last_name` | string | Contact's last name |
| `email` | string\|null | Contact's email |
| `phone` | string\|null | Contact's phone number |

### Notification Object Structure

Each notification in the `notifications` array contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Notification ID |
| `minutes` | integer | Number of time units |
| `unit` | string | Time unit (minutes, hours, days) |
| `trigger` | string | When to trigger (before, after) |
| `is_enabled` | boolean | Whether notification is enabled |

### Survey Object Structure

When a survey is attached (`survey_id` exists), the `survey` object contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Survey ID |
| `survey_name` | string | Survey name |

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get meeting by ID
async function getMeetingById(meetingId, token) {
  try {
    const response = await fetch('http://your-api-url/api/meeting/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: meetingId
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve meeting');
    }

    return data.data; // Returns meeting object
  } catch (error) {
    console.error('Error fetching meeting:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const meeting = await getMeetingById(1, token);
console.log('Meeting:', meeting);
console.log('Attendees:', meeting.attendees);
console.log('Notifications:', meeting.notifications);
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

// Get meeting by ID
async function getMeetingById(meetingId) {
  try {
    const response = await apiClient.post('/meeting/show', {
      id: meetingId
    });
    
    return response.data.data; // Returns meeting object
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const meeting = await getMeetingById(1);
console.log('Meeting:', meeting);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface Attendee {
  id: number;
  first_name: string;
  last_name: string;
  email: string | null;
  phone: string | null;
}

interface Notification {
  id: number;
  minutes: number;
  unit: string;
  trigger: string;
  is_enabled: boolean;
}

interface Meeting {
  id: number;
  organization_id: number;
  meeting_title: string;
  status: string;
  date: string;
  time: string;
  duration: number;
  meeting_type: string;
  custom_location: string | null;
  survey_id: number | null;
  survey: {
    id: number;
    survey_name: string;
  } | null;
  agenda_notes: string | null;
  created_by: number;
  attendees: Attendee[];
  notifications: Notification[];
  created_at: string;
  updated_at: string;
}

interface MeetingShowResponse {
  data: Meeting;
  message: string;
}

export function useMeetingShow(token: string, meetingId: number | null) {
  const [meeting, setMeeting] = useState<Meeting | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchMeeting = async () => {
      if (!meetingId) {
        setMeeting(null);
        return;
      }

      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<MeetingShowResponse>(
          '/api/meeting/show',
          { id: meetingId },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setMeeting(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching meeting:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token && meetingId) {
      fetchMeeting();
    }
  }, [token, meetingId]);

  return { meeting, loading, error };
}

// Usage in component
function MeetingDetails({ meetingId }: { meetingId: number }) {
  const token = localStorage.getItem('auth_token') || '';
  const { meeting, loading, error } = useMeetingShow(token, meetingId);

  if (loading) return <div>Loading meeting...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!meeting) return <div>Meeting not found</div>;

  return (
    <div>
      <h2>{meeting.meeting_title}</h2>
      <p>Date: {meeting.date} at {meeting.time}</p>
      <p>Duration: {meeting.duration} minutes</p>
      <p>Type: {meeting.meeting_type}</p>
      <p>Status: {meeting.status}</p>
      <p>Attendees: {meeting.attendees.length}</p>
      <p>Notifications: {meeting.notifications.length}</p>
      {meeting.agenda_notes && <p>Agenda: {meeting.agenda_notes}</p>}
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState, useEffect } from 'react';

function MeetingView({ meetingId, token, onEdit }) {
  const [meeting, setMeeting] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!meetingId) return;

    const fetchMeeting = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch('/api/meeting/show', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ id: meetingId })
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Failed to load meeting');
        }

        setMeeting(data.data);
      } catch (err) {
        setError(err.message);
        console.error('Error:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchMeeting();
  }, [meetingId, token]);

  if (loading) {
    return <div>Loading meeting...</div>;
  }

  if (error) {
    return <div className="error">Error: {error}</div>;
  }

  if (!meeting) {
    return <div>Meeting not found</div>;
  }

  return (
    <div className="meeting-view" style={{ padding: '20px' }}>
      <div style={{ marginBottom: '20px' }}>
        <h1>{meeting.meeting_title}</h1>
        <span style={{
          padding: '4px 8px',
          borderRadius: '4px',
          backgroundColor: meeting.status === 'Scheduled' ? '#d4edda' : 
                          meeting.status === 'Created' ? '#fff3cd' : 
                          meeting.status === 'Completed' ? '#cce5ff' : '#f8d7da',
          color: meeting.status === 'Scheduled' ? '#155724' : 
                 meeting.status === 'Created' ? '#856404' : 
                 meeting.status === 'Completed' ? '#004085' : '#721c24'
        }}>
          {meeting.status}
        </span>
        <button onClick={() => onEdit(meeting)} style={{ marginLeft: '10px' }}>
          Edit
        </button>
      </div>

      <div style={{ marginBottom: '20px' }}>
        <h2>Meeting Details</h2>
        <div>
          <strong>Date:</strong> {meeting.date}
        </div>
        <div>
          <strong>Time:</strong> {meeting.time}
        </div>
        <div>
          <strong>Duration:</strong> {meeting.duration} minutes
        </div>
        <div>
          <strong>Type:</strong> {meeting.meeting_type}
        </div>
        {meeting.custom_location && (
          <div>
            <strong>Location/Link:</strong> {meeting.custom_location}
          </div>
        )}
        {meeting.survey && (
          <div>
            <strong>Attached Survey:</strong> {meeting.survey.survey_name}
          </div>
        )}
      </div>

      {meeting.agenda_notes && (
        <div style={{ marginBottom: '20px' }}>
          <h2>Agenda & Notes</h2>
          <p style={{ whiteSpace: 'pre-wrap' }}>{meeting.agenda_notes}</p>
        </div>
      )}

      <div style={{ marginBottom: '20px' }}>
        <h2>Attendees ({meeting.attendees.length})</h2>
        {meeting.attendees.length === 0 ? (
          <p>No attendees</p>
        ) : (
          <ul>
            {meeting.attendees.map(attendee => (
              <li key={attendee.id}>
                {attendee.first_name} {attendee.last_name}
                {attendee.email && ` (${attendee.email})`}
                {attendee.phone && ` - ${attendee.phone}`}
              </li>
            ))}
          </ul>
        )}
      </div>

      <div style={{ marginBottom: '20px' }}>
        <h2>Notifications ({meeting.notifications.length})</h2>
        {meeting.notifications.length === 0 ? (
          <p>No notifications configured</p>
        ) : (
          <ul>
            {meeting.notifications.map(notification => (
              <li key={notification.id}>
                {notification.is_enabled ? '✓' : '✗'} {notification.minutes} {notification.unit} {notification.trigger}
              </li>
            ))}
          </ul>
        )}
      </div>

      <div style={{ marginTop: '20px', fontSize: '12px', color: '#666' }}>
        Created: {new Date(meeting.created_at).toLocaleString()}
        {meeting.updated_at !== meeting.created_at && (
          <span> | Updated: {new Date(meeting.updated_at).toLocaleString()}</span>
        )}
      </div>
    </div>
  );
}

// Usage
function MeetingPage({ meetingId, token }) {
  const handleEdit = (meeting) => {
    // Navigate to edit page or open edit modal
    console.log('Edit meeting:', meeting);
  };

  return (
    <MeetingView 
      meetingId={meetingId} 
      token={token} 
      onEdit={handleEdit}
    />
  );
}
```

---

## Integration with Meeting Save API

After retrieving a meeting, you can use the data to populate an edit form:

```javascript
// Step 1: Get meeting by ID
async function loadMeetingForEdit(meetingId, token) {
  const response = await fetch('/api/meeting/show', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ id: meetingId })
  });

  const data = await response.json();
  return data.data; // Meeting object
}

// Step 2: Use the data to populate edit form
async function populateEditForm(meetingId, token) {
  const meeting = await loadMeetingForEdit(meetingId, token);
  
  // Populate form fields
  document.getElementById('meetingTitle').value = meeting.meeting_title;
  document.getElementById('status').value = meeting.status;
  document.getElementById('date').value = meeting.date;
  document.getElementById('time').value = meeting.time;
  document.getElementById('duration').value = meeting.duration;
  document.getElementById('meetingType').value = meeting.meeting_type;
  document.getElementById('customLocation').value = meeting.custom_location || '';
  document.getElementById('agendaNotes').value = meeting.agenda_notes || '';
  
  // Set survey if exists
  if (meeting.survey_id) {
    document.getElementById('surveyId').value = meeting.survey_id;
  }
  
  // Set attendees (if using multi-select)
  const attendeeIds = meeting.attendees.map(a => a.id);
  // ... populate attendees select
  
  // Set notifications
  meeting.notifications.forEach((notification, index) => {
    // ... populate notification fields
  });
  
  // Store meeting ID for update
  document.getElementById('meetingId').value = meeting.id;
}

// Step 3: Submit updated meeting via /api/meeting/save
async function updateMeeting(meetingId, formData, token) {
  const response = await fetch('/api/meeting/save', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      id: meetingId, // Include ID for update
      meeting_title: formData.title,
      status: formData.status,
      date: formData.date,
      time: formData.time,
      duration: formData.duration,
      meeting_type: formData.type,
      // ... other fields
    })
  });

  return await response.json();
}
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/meeting/show" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Meeting retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Meeting not found or doesn't belong to user's organization |
| `422` | Validation error (missing or invalid ID) |
| `500` | Server error |

---

## Error Responses

### Validation Error (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "id": [
      "The id field is required."
    ]
  }
}
```

### Meeting Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Meeting] {id}"
}
```

### Meeting from Different Organization (404)

If you try to access a meeting from a different organization:

```json
{
  "message": "No query results for model [App\\Models\\Meeting] {id}"
}
```

### No Organization (404)

```json
{
  "message": "No organization found. Please create a meeting first."
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

---

## Important Notes

### 1. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the meetings table

### 2. Organization Scope
- Only meetings from the authenticated user's organization can be accessed
- If the meeting ID exists but belongs to a different organization, you'll get a 404 error

### 3. Complete Data
- Returns full meeting data including all attendees and notifications
- Survey information is included if a survey is attached
- Use this endpoint to load meeting details for viewing or editing

### 4. Relationships
- `attendees`: Array of contact objects with basic contact information
- `notifications`: Array of notification rules with all configuration
- `survey`: Survey object (if survey_id exists) with basic survey information

### 5. Use Case
- **Viewing**: Load meeting details to display in a detail view
- **Editing**: Load meeting data to populate an edit form
- **Calendar**: Display meeting details in calendar views

### 6. Security
- Meeting access is restricted to the user's organization
- Users cannot access meetings from other organizations

---

## Best Practices

1. **Error Handling**: Always handle 404 errors gracefully (meeting not found)
2. **Loading States**: Show loading indicators while fetching
3. **Caching**: Consider caching meeting data if it won't change frequently
4. **Refresh**: Refresh meeting data after updates
5. **Validation**: Validate ID before making the request
6. **Date/Time Display**: Format date and time for better user experience
7. **Status Badges**: Use visual indicators (colors, badges) for meeting status

---

## Example: Complete Meeting View/Edit Flow

```javascript
import React, { useState, useEffect } from 'react';

function MeetingEdit({ meetingId, token, onSave }) {
  const [meeting, setMeeting] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({});

  // Load meeting data
  useEffect(() => {
    const fetchMeeting = async () => {
      try {
        const response = await fetch('/api/meeting/show', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ id: meetingId })
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Failed to load meeting');
        }

        setMeeting(data.data);
        setFormData({
          meeting_title: data.data.meeting_title,
          status: data.data.status,
          date: data.data.date,
          time: data.data.time,
          duration: data.data.duration,
          meeting_type: data.data.meeting_type,
          custom_location: data.data.custom_location || '',
          survey_id: data.data.survey_id || null,
          agenda_notes: data.data.agenda_notes || '',
          attendees: data.data.attendees.map(a => a.id),
          notifications: data.data.notifications
        });
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchMeeting();
  }, [meetingId, token]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('/api/meeting/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          id: meetingId,
          meeting_title: formData.meeting_title,
          status: formData.status,
          date: formData.date,
          time: formData.time,
          duration: formData.duration,
          meeting_type: formData.meeting_type,
          custom_location: formData.custom_location || null,
          survey_id: formData.survey_id || null,
          agenda_notes: formData.agenda_notes || null,
          attendees: formData.attendees || [],
          notifications: formData.notifications || []
        })
      });

      const data = await response.json();

      if (response.ok) {
        onSave && onSave(data.data);
      } else {
        alert('Error: ' + data.message);
      }
    } catch (error) {
      alert('Error saving meeting: ' + error.message);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!meeting) return <div>Meeting not found</div>;

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={formData.meeting_title}
        onChange={(e) => setFormData({...formData, meeting_title: e.target.value})}
        placeholder="Meeting Title"
        required
      />
      <input
        type="date"
        value={formData.date}
        onChange={(e) => setFormData({...formData, date: e.target.value})}
        required
      />
      <input
        type="time"
        value={formData.time}
        onChange={(e) => setFormData({...formData, time: e.target.value})}
        required
      />
      {/* Add other fields */}
      <button type="submit">Save Meeting</button>
    </form>
  );
}
```

---

## Example: Formatting Meeting Date/Time

```javascript
// Format meeting date and time for display
function formatMeetingDateTime(meeting) {
  const date = new Date(`${meeting.date}T${meeting.time}`);
  return {
    dateFormatted: date.toLocaleDateString('en-US', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    }),
    timeFormatted: date.toLocaleTimeString('en-US', { 
      hour: 'numeric', 
      minute: '2-digit',
      hour12: true 
    }),
    dateTime: date.toLocaleString()
  };
}

// Usage
const meeting = await getMeetingById(1, token);
const formatted = formatMeetingDateTime(meeting);
console.log(formatted.dateFormatted); // "Monday, November 15, 2025"
console.log(formatted.timeFormatted); // "2:30 PM"
```

---

## Example: Displaying Notifications

```javascript
// Format notification for display
function formatNotification(notification) {
  const status = notification.is_enabled ? 'Enabled' : 'Disabled';
  const timeText = `${notification.minutes} ${notification.unit} ${notification.trigger}`;
  return `${status}: ${timeText}`;
}

// Usage
meeting.notifications.forEach(notification => {
  console.log(formatNotification(notification));
  // "Enabled: 30 minutes before"
  // "Disabled: 15 minutes before"
});
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/meeting/show` with Bearer token and `{"id": 1}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **List Meetings**: `/api/meeting/index` - Get paginated list of meetings
- **Save Meeting**: `/api/meeting/save` - Create or update meeting
- **Delete Meeting**: `/api/meeting/delete` - Delete meeting
- **Contacts Show**: `/api/contacts/show` - Similar endpoint for contacts
- **Survey Show**: `/api/survey/show` - Similar endpoint for surveys

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

