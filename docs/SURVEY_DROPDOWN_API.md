# Survey Dropdown API Documentation

## Overview
This API endpoint retrieves a simplified list of surveys for use in dropdown/select components. It returns only the `id` and `name` (survey_name) of surveys saved by the user in their organization. This is particularly useful for selecting surveys to attach to meetings when creating/editing meetings.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/dropdown`  
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

This endpoint accepts an empty request body (optional). You can send an empty JSON object or no body at all:

```json
{}
```

Or simply send no body (empty POST request).

---

## Response Format

### Success Response (200)

```json
{
  "data": [
    {
      "id": 1,
      "name": "Customer Satisfaction Survey"
    },
    {
      "id": 2,
      "name": "Employee Feedback Survey"
    },
    {
      "id": 3,
      "name": "Product Feedback Survey"
    }
  ],
  "message": "Surveys retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | array | Array of survey objects with `id` and `name` |
| `data[].id` | integer | Survey ID - use this when saving survey_id |
| `data[].name` | string | Survey name (survey_name) |
| `message` | string | Success message |

---

## Important Notes

### 1. Survey ID Usage
- The `id` field in the response is what you should use for `survey_id` when saving/updating meetings via `/api/meeting/save`
- Example: If dropdown returns `{id: 5, name: "Customer Survey"}`, use `survey_id: 5` when saving a meeting

### 2. Organization Scope
- Only surveys from the authenticated user's organization are returned
- If the user has no organization, an empty array is returned

### 3. Sorted Results
- Surveys are sorted alphabetically by survey name
- This makes them easy to find in dropdown lists

### 4. Name Format
- The `name` field is the `survey_name` from the survey
- Use this directly in dropdown options for user-friendly display

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get surveys for dropdown
async function getSurveysForDropdown(token) {
  try {
    const response = await fetch('http://your-api-url/api/survey/dropdown', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve surveys');
    }

    return data.data; // Returns array of {id, name} objects
  } catch (error) {
    console.error('Error fetching surveys:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const surveys = await getSurveysForDropdown(token);
console.log('Surveys:', surveys);
// Output: [{id: 1, name: "Customer Satisfaction Survey"}, {id: 2, name: "Employee Feedback"}, ...]
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

// Get surveys for dropdown
async function getSurveysForDropdown() {
  try {
    const response = await apiClient.post('/survey/dropdown', {});
    return response.data.data; // Returns array of {id, name} objects
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const surveys = await getSurveysForDropdown();
console.log('Surveys:', surveys);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface SurveyDropdown {
  id: number;
  name: string;
}

interface SurveysDropdownResponse {
  data: SurveyDropdown[];
  message: string;
}

export function useSurveysDropdown(token: string) {
  const [surveys, setSurveys] = useState<SurveyDropdown[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchSurveys = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<SurveysDropdownResponse>(
          '/api/survey/dropdown',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setSurveys(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching surveys:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      fetchSurveys();
    }
  }, [token]);

  return { surveys, loading, error };
}

// Usage in component
function SurveySelect() {
  const token = localStorage.getItem('auth_token') || '';
  const { surveys, loading, error } = useSurveysDropdown(token);
  const [selectedSurveyId, setSelectedSurveyId] = useState<number | null>(null);

  if (loading) return <div>Loading surveys...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <select 
      value={selectedSurveyId || ''} 
      onChange={(e) => setSelectedSurveyId(Number(e.target.value) || null)}
    >
      <option value="">No survey</option>
      {surveys.map(survey => (
        <option key={survey.id} value={survey.id}>
          {survey.name}
        </option>
      ))}
    </select>
  );
}
```

### React Component Example (Meeting Form with Survey Selection)

```typescript
import React, { useState, useEffect } from 'react';

interface SurveyDropdown {
  id: number;
  name: string;
}

function MeetingForm({ token }: { token: string }) {
  const [surveys, setSurveys] = useState<SurveyDropdown[]>([]);
  const [selectedSurveyId, setSelectedSurveyId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  // Fetch surveys for dropdown
  useEffect(() => {
    async function fetchSurveys() {
      try {
        const response = await fetch('/api/survey/dropdown', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        const data = await response.json();
        setSurveys(data.data);
      } catch (error) {
        console.error('Error fetching surveys:', error);
      }
    }

    fetchSurveys();
  }, [token]);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);

    try {
      const formData = {
        meeting_title: e.currentTarget.meetingTitle.value,
        status: e.currentTarget.status.value,
        date: e.currentTarget.date.value,
        time: e.currentTarget.time.value,
        duration: parseInt(e.currentTarget.duration.value),
        meeting_type: e.currentTarget.meetingType.value,
        custom_location: e.currentTarget.customLocation.value || null,
        survey_id: selectedSurveyId || null, // Use the id from dropdown
        agenda_notes: e.currentTarget.agendaNotes.value || null,
        // ... other fields
      };

      const response = await fetch('/api/meeting/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();
      console.log('Meeting saved:', result);
    } catch (error) {
      console.error('Error saving meeting:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input name="meetingTitle" placeholder="Meeting Title" required />
      <input name="date" type="date" required />
      <input name="time" type="time" required />
      
      <div>
        <label>Attach Survey (Optional):</label>
        <select 
          value={selectedSurveyId || ''} 
          onChange={(e) => setSelectedSurveyId(Number(e.target.value) || null)}
        >
          <option value="">No survey</option>
          {surveys.map(survey => (
            <option key={survey.id} value={survey.id}>
              {survey.name}
            </option>
          ))}
        </select>
        {surveys.length === 0 && (
          <small>No surveys available. Create a survey first to attach it to meetings.</small>
        )}
      </div>
      
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Meeting'}
      </button>
    </form>
  );
}
```

---

## Integration with Meeting Save API

This endpoint is typically used to populate the `survey_id` field when saving meetings:

```javascript
// Step 1: Fetch surveys for dropdown
async function setupSurveyDropdown(token) {
  const response = await fetch('/api/survey/dropdown', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({})
  });
  
  const data = await response.json();
  return data.data; // [{id: 1, name: "Customer Survey"}, ...]
}

// Step 2: When saving a meeting, use the selected survey's id as survey_id
async function saveMeeting(meetingData, selectedSurveyId, token) {
  const response = await fetch('/api/meeting/save', {
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
      // ... other fields
      survey_id: selectedSurveyId // Use the id from dropdown
    })
  });
  
  return await response.json();
}

// Usage
const surveys = await setupSurveyDropdown(token);
// User selects a survey from dropdown, get the id
const selectedSurveyId = 1; // This is the survey ID
await saveMeeting(meetingData, selectedSurveyId, token);
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/survey/dropdown" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Surveys retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `500` | Server error |

---

## Error Responses

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

### No Organization (200 with empty array)

```json
{
  "data": [],
  "message": "No organization found. Please create a survey first."
}
```

### Server Error (500)

```json
{
  "message": "Server Error"
}
```

---

## Important Notes

### 1. Survey ID Usage
- The `id` field in the response should be used for the `survey_id` field when saving meetings
- Example: If dropdown returns `{id: 5, name: "Customer Survey"}`, use `survey_id: 5` when saving

### 2. Empty Results
- If no surveys exist yet, the `data` array will be empty `[]`
- This is normal - users need to create surveys first before they can be attached to meetings

### 3. Organization Scope
- Only surveys from the user's organization are returned
- Surveys from other organizations won't appear

### 4. Name Display
- The `name` field is the survey's `survey_name`
- Use this directly in dropdown options for user-friendly display

### 5. POST Method
- This endpoint uses `POST` instead of `GET` for consistency with other endpoints

### 6. Optional Field
- The `survey_id` field in meetings is optional
- Users can create meetings without attaching a survey
- Always provide a "No survey" or empty option in the dropdown

---

## Best Practices

1. **Cache Results**: Since surveys don't change frequently, consider caching the results
2. **Show Loading State**: Display a loading indicator while fetching surveys
3. **Handle Empty State**: Show a message when no surveys are available
4. **Update on Save**: After saving a new survey, refresh the dropdown to include it
5. **Optional Field**: Always make survey_id optional - users may not want to attach a survey
6. **Default Option**: Always include a "No survey" option as the first/default option

---

## Complete Example: Meeting Form with Survey Selection

```javascript
import React, { useState, useEffect } from 'react';

function MeetingForm({ token }) {
  const [surveys, setSurveys] = useState([]);
  const [selectedSurveyId, setSelectedSurveyId] = useState(null);
  const [loading, setLoading] = useState(false);

  // Fetch surveys on mount
  useEffect(() => {
    async function fetchSurveys() {
      try {
        const response = await fetch('/api/survey/dropdown', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        const data = await response.json();
        setSurveys(data.data);
      } catch (error) {
        console.error('Error fetching surveys:', error);
      }
    }

    fetchSurveys();
  }, [token]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const formData = {
        meeting_title: e.target.meetingTitle.value,
        status: e.target.status.value,
        date: e.target.date.value,
        time: e.target.time.value,
        duration: parseInt(e.target.duration.value),
        meeting_type: e.target.meetingType.value,
        custom_location: e.target.customLocation.value || null,
        survey_id: selectedSurveyId || null, // Survey ID from dropdown
        agenda_notes: e.target.agendaNotes.value || null,
        attendees: [],
        notifications: []
      };

      const response = await fetch('/api/meeting/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();
      
      if (response.ok) {
        // Optionally refresh surveys list if a new survey was created
        const refreshResponse = await fetch('/api/survey/dropdown', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });
        const refreshData = await refreshResponse.json();
        setSurveys(refreshData.data);
        
        // Reset form
        e.target.reset();
        setSelectedSurveyId(null);
      }
    } catch (error) {
      console.error('Error saving meeting:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input name="meetingTitle" placeholder="Meeting Title" required />
      <input name="date" type="date" required />
      <input name="time" type="time" required />
      
      <div>
        <label>Attach Survey (Optional):</label>
        <select 
          value={selectedSurveyId || ''} 
          onChange={(e) => setSelectedSurveyId(Number(e.target.value) || null)}
        >
          <option value="">No survey</option>
          {surveys.map(survey => (
            <option key={survey.id} value={survey.id}>
              {survey.name}
            </option>
          ))}
        </select>
        {surveys.length === 0 && (
          <small>No surveys available. Create a survey first to attach it to meetings.</small>
        )}
      </div>
      
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Meeting'}
      </button>
    </form>
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/survey/dropdown` with Bearer token
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **Save Survey**: `/api/survey/save` - Create or update a survey
- **Survey Index**: `/api/survey/index` - Get paginated list of surveys with full details
- **Save Meeting**: `/api/meeting/save` - Create or update a meeting (uses survey_id)
- **Contacts Dropdown**: `/api/contacts/dropdown` - Similar dropdown endpoint for contacts

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

