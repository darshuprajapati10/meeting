# Survey Delete API Documentation

## Overview
This API endpoint allows you to delete a survey by its ID. The survey must belong to the authenticated user's organization or the user must be the creator of the survey. Once deleted, the survey along with all its steps and fields are permanently removed from the database through cascade delete.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/delete`  
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
  "id": 2
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | Survey ID to delete |

---

## Response Format

### Success Response (200)

```json
{
  "message": "Survey deleted successfully."
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
// Delete survey by ID
async function deleteSurvey(surveyId, token) {
  try {
    const response = await fetch('http://your-api-url/api/survey/delete', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: surveyId
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to delete survey');
    }

    return data; // Returns { message: "Survey deleted successfully." }
  } catch (error) {
    console.error('Error deleting survey:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
await deleteSurvey(2, token);
console.log('Survey deleted successfully');
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

// Delete survey by ID
async function deleteSurvey(surveyId) {
  try {
    const response = await apiClient.post('/survey/delete', {
      id: surveyId
    });
    
    return response.data; // Returns { message: "Survey deleted successfully." }
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await deleteSurvey(2);
console.log(result.message);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface DeleteSurveyResponse {
  message: string;
}

export function useDeleteSurvey(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const deleteSurvey = async (surveyId: number) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<DeleteSurveyResponse>(
        '/api/survey/delete',
        { id: surveyId },
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

  return { deleteSurvey, loading, error };
}

// Usage in component
function SurveyList({ token }: { token: string }) {
  const { deleteSurvey, loading, error } = useDeleteSurvey(token);

  const handleDelete = async (surveyId: number) => {
    if (window.confirm('Are you sure you want to delete this survey? This will also delete all steps and fields.')) {
      try {
        const result = await deleteSurvey(surveyId);
        alert(result.message);
        // Refresh the survey list
        window.location.reload();
      } catch (error) {
        alert('Error deleting survey: ' + error.message);
      }
    }
  };

  return (
    <div>
      {/* Your survey list */}
      <button onClick={() => handleDelete(2)} disabled={loading}>
        {loading ? 'Deleting...' : 'Delete Survey'}
      </button>
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function SurveyDeleteButton({ survey, token, onDeleteSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleDelete = async () => {
    if (!window.confirm(`Are you sure you want to delete "${survey.survey_name}"? This will permanently delete all steps and fields.`)) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/survey/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: survey.id })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete survey');
      }

      // Call success callback to refresh list
      if (onDeleteSuccess) {
        onDeleteSuccess();
      }

      alert(data.message || 'Survey deleted successfully');
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

// Usage in survey list
function SurveysTable({ surveys, token, onSurveyDeleted }) {
  return (
    <table>
      <thead>
        <tr>
          <th>Survey Name</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {surveys.map(survey => (
          <tr key={survey.id}>
            <td>{survey.survey_name}</td>
            <td>{survey.status}</td>
            <td>
              <SurveyDeleteButton
                survey={survey}
                token={token}
                onDeleteSuccess={onSurveyDeleted}
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
async function deleteSurveyWithConfirmation(surveyId, surveyName, token) {
  // Show confirmation dialog
  const confirmed = window.confirm(
    `Are you sure you want to delete "${surveyName}"?\n\nThis will permanently delete:\n- The survey\n- All steps\n- All fields\n\nThis action cannot be undone.`
  );

  if (!confirmed) {
    return { cancelled: true };
  }

  try {
    const response = await fetch('/api/survey/delete', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ id: surveyId })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to delete survey');
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
const result = await deleteSurveyWithConfirmation(2, 'Customer Satisfaction Survey', token);
if (result.success) {
  console.log('Deleted:', result.message);
} else if (!result.cancelled) {
  console.error('Error:', result.error);
}
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/survey/delete" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 2
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Survey deleted successfully |
| `401` | Unauthorized (missing or invalid token) |
| `403` | Forbidden - User doesn't have permission to delete this survey |
| `404` | Survey not found or doesn't belong to user's organization |
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

### Survey Not Found (404)

```json
{
  "message": "Survey not found."
}
```

### Permission Denied (403)

```json
{
  "message": "You do not have permission to delete this survey."
}
```

### Survey ID Doesn't Exist (422)

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
- ⚠️ **Warning**: Deleting a survey is **permanent** and cannot be undone
- The survey will be permanently removed from the database
- All associated survey steps and fields will also be deleted automatically (cascade delete)
- Consider implementing a confirmation dialog in your frontend

### 2. Cascade Delete
- When a survey is deleted, all related data is automatically deleted:
  - All `survey_steps` records are deleted
  - All `survey_fields` records are deleted
- This happens automatically through database foreign key constraints with `cascadeOnDelete`
- No need to manually delete steps and fields

### 3. Organization Scope
- Only surveys from the authenticated user's organization can be deleted
- If the survey ID exists but belongs to a different organization, you'll get a 403 error

### 4. Creator Permission
- Users can also delete surveys they created, even if they're not in the same organization
- The API checks both:
  - Survey belongs to user's organization, OR
  - User is the creator (created_by matches user ID)

### 5. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the surveys table

### 6. Security
- Survey deletion is restricted to:
  - Surveys from the user's organization, OR
  - Surveys created by the user
- Users cannot delete surveys from other organizations unless they created them

---

## Best Practices

1. **Confirmation Dialog**: Always show a confirmation dialog before deleting
2. **Warning Message**: Inform users that deletion is permanent and will delete all steps and fields
3. **Error Handling**: Handle 404, 403, and 422 errors gracefully
4. **Loading States**: Show loading indicators while deleting
5. **Refresh List**: Refresh the survey list after successful deletion
6. **User Feedback**: Show success/error messages to the user

---

## Example: Delete Button with Confirmation

```javascript
function DeleteSurveyButton({ survey, token, onDeleted }) {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    // Show confirmation with details
    const confirmed = window.confirm(
      `Are you sure you want to delete "${survey.survey_name}"?\n\n` +
      `This will permanently delete:\n` +
      `- The survey\n` +
      `- ${survey.survey_steps.length} step(s)\n` +
      `- All associated fields\n\n` +
      `This action cannot be undone.`
    );

    if (!confirmed) return;

    setLoading(true);

    try {
      const response = await fetch('/api/survey/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: survey.id })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete survey');
      }

      // Show success message
      alert('Survey deleted successfully');
      
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
    >
      {loading ? 'Deleting...' : 'Delete'}
    </button>
  );
}
```

---

## Example: Soft Delete Alternative (Future Enhancement)

If you want to implement soft delete in the future (deleting but keeping the record), you would need to:

1. Add `deleted_at` column to surveys table
2. Use SoftDeletes trait in Survey model
3. Filter out deleted surveys in index/show endpoints

For now, the current implementation performs hard delete (permanent removal).

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/survey/delete` with Bearer token and `{"id": 2}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **List Surveys**: `/api/survey/index` - Get paginated list of surveys
- **Get Survey**: `/api/survey/show` - Get single survey by ID
- **Save Survey**: `/api/survey/save` - Create or update survey

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

