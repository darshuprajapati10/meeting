# Meeting Delete API Documentation

## Overview
This API endpoint allows you to delete a meeting by its ID. The meeting must belong to the authenticated user's organization or the user must be the creator of the meeting. Once deleted, the meeting along with all its attendees and notifications are permanently removed from the database through cascade delete.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/meeting/delete`  
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
| `id` | integer | **Yes** | Meeting ID to delete |

---

## Response Format

### Success Response (200)

```json
{
  "message": "Meeting deleted successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `message` | string | Success message |

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Delete meeting by ID
async function deleteMeeting(meetingId, token) {
  try {
    const response = await fetch('http://your-api-url/api/meeting/delete', {
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
      throw new Error(data.message || 'Failed to delete meeting');
    }

    return data; // Returns { message: "Meeting deleted successfully." }
  } catch (error) {
    console.error('Error deleting meeting:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
await deleteMeeting(1, token);
console.log('Meeting deleted successfully');
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

// Delete meeting by ID
async function deleteMeeting(meetingId) {
  try {
    const response = await apiClient.post('/meeting/delete', {
      id: meetingId
    });
    
    return response.data; // Returns { message: "Meeting deleted successfully." }
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await deleteMeeting(1);
console.log(result.message);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface DeleteMeetingResponse {
  message: string;
}

export function useDeleteMeeting(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const deleteMeeting = async (meetingId: number) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<DeleteMeetingResponse>(
        '/api/meeting/delete',
        { id: meetingId },
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

  return { deleteMeeting, loading, error };
}

// Usage in component
function MeetingList({ token }: { token: string }) {
  const { deleteMeeting, loading, error } = useDeleteMeeting(token);

  const handleDelete = async (meetingId: number) => {
    if (window.confirm('Are you sure you want to delete this meeting? This will also delete all attendees and notifications.')) {
      try {
        const result = await deleteMeeting(meetingId);
        alert(result.message);
        // Refresh the meeting list
        window.location.reload();
      } catch (error) {
        alert('Error deleting meeting: ' + error.message);
      }
    }
  };

  return (
    <div>
      {/* Your meeting list */}
      <button onClick={() => handleDelete(1)} disabled={loading}>
        {loading ? 'Deleting...' : 'Delete Meeting'}
      </button>
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function MeetingDeleteButton({ meeting, token, onDeleteSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleDelete = async () => {
    if (!window.confirm(`Are you sure you want to delete "${meeting.meeting_title}"? This will permanently delete all attendees and notifications.`)) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/meeting/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: meeting.id })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete meeting');
      }

      // Call success callback to refresh list
      if (onDeleteSuccess) {
        onDeleteSuccess();
      }

      alert(data.message || 'Meeting deleted successfully');
    } catch (err) {
      setError(err.message);
      alert('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleDelete}
      disabled={loading}
      className="delete-btn"
      style={{
        backgroundColor: '#dc3545',
        color: 'white',
        border: 'none',
        padding: '8px 16px',
        borderRadius: '4px',
        cursor: loading ? 'not-allowed' : 'pointer'
      }}
    >
      {loading ? 'Deleting...' : 'Delete'}
    </button>
  );
}

// Usage in meeting list
function MeetingsTable({ meetings, token, onMeetingDeleted }) {
  return (
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Date & Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {meetings.map(meeting => (
          <tr key={meeting.id}>
            <td>{meeting.meeting_title}</td>
            <td>{meeting.date} {meeting.time}</td>
            <td>{meeting.status}</td>
            <td>
              <MeetingDeleteButton
                meeting={meeting}
                token={token}
                onDeleteSuccess={onMeetingDeleted}
              />
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
```

---

## Integration Example: Delete with Confirmation

```javascript
// Complete delete flow with confirmation
async function deleteMeetingWithConfirmation(meetingId, meetingTitle, token) {
  // Show confirmation dialog
  const confirmed = window.confirm(
    `Are you sure you want to delete "${meetingTitle}"?\n\nThis will permanently delete:\n- The meeting\n- All attendees\n- All notifications\n\nThis action cannot be undone.`
  );

  if (!confirmed) {
    return { cancelled: true };
  }

  try {
    const response = await fetch('/api/meeting/delete', {
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
      throw new Error(data.message || 'Failed to delete meeting');
    }

    return {
      success: true,
      message: data.message
    };
  } catch (error) {
    return {
      success: false,
      error: error.message
    };
  }
}

// Usage
const result = await deleteMeetingWithConfirmation(1, 'Project Kickoff Meeting', token);
if (result.success) {
  console.log('Deleted:', result.message);
} else if (!result.cancelled) {
  console.error('Error:', result.error);
}
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/meeting/delete" \
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
| `200` | Success - Meeting deleted successfully |
| `401` | Unauthorized (missing or invalid token) |
| `403` | Forbidden - User doesn't have permission to delete this meeting |
| `404` | Meeting not found |
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
  "message": "Meeting not found."
}
```

### Permission Denied (403)

```json
{
  "message": "You do not have permission to delete this meeting."
}
```

### Meeting ID Doesn't Exist (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "id": [
      "The selected id is invalid."
    ]
  }
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

### 1. Permanent Deletion
- ⚠️ **Warning**: Deleting a meeting is **permanent** and cannot be undone
- The meeting will be permanently removed from the database
- All associated attendees and notifications will also be deleted automatically (cascade delete)
- Consider implementing a confirmation dialog in your frontend

### 2. Cascade Delete
- When a meeting is deleted, all related data is automatically deleted:
  - All `meeting_attendees` records are deleted (attendee relationships)
  - All `meeting_notifications` records are deleted (notification rules)
- This happens automatically through database foreign key constraints with `cascadeOnDelete`
- No need to manually delete attendees or notifications

### 3. Organization Scope
- Only meetings from the authenticated user's organization can be deleted
- If the meeting ID exists but belongs to a different organization, you'll get a 403 error

### 4. Creator Permission
- Users can also delete meetings they created, even if they're not in the same organization
- The API checks both:
  - Meeting belongs to user's organization, OR
  - User is the creator (created_by matches user ID)

### 5. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the meetings table

### 6. Survey Relationship
- If a meeting has an attached survey (`survey_id`), the survey is **NOT** deleted
- Only the meeting and its direct relationships (attendees, notifications) are deleted
- The survey remains in the database and can still be used by other meetings

### 7. Security
- Meeting deletion is restricted to:
  - Meetings from the user's organization, OR
  - Meetings created by the user
- Users cannot delete meetings from other organizations unless they created them

---

## Best Practices

1. **Confirmation Dialog**: Always show a confirmation dialog before deleting
2. **Warning Message**: Inform users that deletion is permanent and will delete all attendees and notifications
3. **Error Handling**: Handle 404, 403, and 422 errors gracefully
4. **Loading States**: Show loading indicators while deleting
5. **Refresh List**: Refresh the meeting list after successful deletion
6. **User Feedback**: Show success/error messages to the user
7. **Calendar Updates**: If using a calendar view, refresh it after deletion

---

## Example: Delete Button with Confirmation

```javascript
function DeleteMeetingButton({ meeting, token, onDeleted }) {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    // Show confirmation with details
    const confirmed = window.confirm(
      `Are you sure you want to delete "${meeting.meeting_title}"?\n\n` +
      `This will permanently delete:\n` +
      `- The meeting\n` +
      `- ${meeting.attendees.length} attendee(s)\n` +
      `- ${meeting.notifications.length} notification(s)\n\n` +
      `This action cannot be undone.`
    );

    if (!confirmed) return;

    setLoading(true);

    try {
      const response = await fetch('/api/meeting/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: meeting.id })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete meeting');
      }

      // Show success message
      alert('Meeting deleted successfully');
      
      // Call callback to refresh list
      if (onDeleted) {
        onDeleted();
      }
    } catch (error) {
      alert('Error: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleDelete}
      disabled={loading}
      className="btn btn-danger"
      style={{
        backgroundColor: '#dc3545',
        color: 'white',
        border: 'none',
        padding: '8px 16px',
        borderRadius: '4px',
        cursor: loading ? 'not-allowed' : 'pointer'
      }}
    >
      {loading ? 'Deleting...' : 'Delete'}
    </button>
  );
}
```

---

## Example: Delete with Modal Confirmation

```javascript
import React, { useState } from 'react';

function DeleteMeetingModal({ meeting, token, onDelete, onClose }) {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    setLoading(true);

    try {
      const response = await fetch('/api/meeting/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: meeting.id })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete meeting');
      }

      // Call success callback
      if (onDelete) {
        onDelete();
      }
      
      // Close modal
      if (onClose) {
        onClose();
      }
    } catch (error) {
      alert('Error: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2>Delete Meeting</h2>
        <p>
          Are you sure you want to delete <strong>"{meeting.meeting_title}"</strong>?
        </p>
        <p>
          This will permanently delete:
        </p>
        <ul>
          <li>The meeting</li>
          <li>{meeting.attendees.length} attendee(s)</li>
          <li>{meeting.notifications.length} notification(s)</li>
        </ul>
        <p style={{ color: 'red', fontWeight: 'bold' }}>
          This action cannot be undone.
        </p>
        <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
          <button onClick={onClose} disabled={loading}>
            Cancel
          </button>
          <button
            onClick={handleDelete}
            disabled={loading}
            style={{
              backgroundColor: '#dc3545',
              color: 'white',
              border: 'none',
              padding: '8px 16px',
              borderRadius: '4px'
            }}
          >
            {loading ? 'Deleting...' : 'Delete Meeting'}
          </button>
        </div>
      </div>
    </div>
  );
}

// Usage
function MeetingCard({ meeting, token, onDeleted }) {
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  return (
    <div>
      <h3>{meeting.meeting_title}</h3>
      <button onClick={() => setShowDeleteModal(true)}>Delete</button>
      
      {showDeleteModal && (
        <DeleteMeetingModal
          meeting={meeting}
          token={token}
          onDelete={onDeleted}
          onClose={() => setShowDeleteModal(false)}
        />
      )}
    </div>
  );
}
```

---

## Example: Batch Delete Multiple Meetings

```javascript
// Delete multiple meetings with confirmation
async function deleteMultipleMeetings(meetingIds, token) {
  const confirmed = window.confirm(
    `Are you sure you want to delete ${meetingIds.length} meeting(s)?\n\nThis action cannot be undone.`
  );

  if (!confirmed) {
    return { cancelled: true };
  }

  const results = [];
  
  for (const meetingId of meetingIds) {
    try {
      const response = await fetch('/api/meeting/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: meetingId })
      });

      const data = await response.json();
      results.push({
        id: meetingId,
        success: response.ok,
        message: data.message
      });
    } catch (error) {
      results.push({
        id: meetingId,
        success: false,
        error: error.message
      });
    }
  }

  return results;
}

// Usage
const meetingIds = [1, 2, 3];
const results = await deleteMultipleMeetings(meetingIds, token);
const successCount = results.filter(r => r.success).length;
console.log(`Deleted ${successCount} out of ${meetingIds.length} meetings`);
```

---

## Example: Soft Delete Alternative (Future Enhancement)

If you want to implement soft delete in the future (deleting but keeping the record), you would need to:

1. Add `deleted_at` column to meetings table
2. Use `SoftDeletes` trait in Meeting model
3. Filter out deleted meetings in index/show endpoints

For now, the current implementation performs hard delete (permanent removal).

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/meeting/delete` with Bearer token and `{"id": 1}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **List Meetings**: `/api/meeting/index` - Get paginated list of meetings
- **Get Meeting**: `/api/meeting/show` - Get single meeting by ID
- **Save Meeting**: `/api/meeting/save` - Create or update meeting
- **Contacts Delete**: `/api/contacts/delete` - Similar endpoint for contacts
- **Survey Delete**: `/api/survey/delete` - Similar endpoint for surveys

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

