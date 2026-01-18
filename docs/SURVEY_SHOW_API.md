# Survey Show API Documentation

## Overview
This API endpoint retrieves a single survey by its ID. It returns the complete survey data including all steps and fields for viewing or editing. The survey must belong to the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/show`  
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
| `id` | integer | **Yes** | Survey ID to retrieve |

---

## Response Format

### Success Response (200)

```json
{
  "data": {
    "id": 2,
    "organization_id": 1,
    "survey_name": "Customer Satisfaction Survey",
    "description": "Survey to measure customer satisfaction",
    "status": "Draft",
    "survey_steps": [
      {
        "id": 3,
        "survey_id": 2,
        "step": "Step 1",
        "tagline": "Introduction and basic information",
        "order": 1,
        "survey_fields": [
          {
            "id": 4,
            "organization_id": 1,
            "survey_id": 2,
            "name": "What is your name?",
            "type": "Short Answer",
            "description": "Enter your full name",
            "is_required": false,
            "options": []
          },
          {
            "id": 5,
            "organization_id": 1,
            "survey_id": 2,
            "name": "Feedback",
            "type": "Paragraph",
            "description": "Please provide your feedback",
            "is_required": false,
            "options": []
          }
        ]
      },
      {
        "id": 4,
        "survey_id": 2,
        "step": "Step 2",
        "tagline": "Rating",
        "order": 2,
        "survey_fields": [
          {
            "id": 6,
            "organization_id": 1,
            "survey_id": 2,
            "name": "Rate us",
            "type": "Choice",
            "description": "How satisfied are you?",
            "is_required": false,
            "options": [1, 2, 3, 4, 5]
          }
        ]
      }
    ],
    "created_at": "2025-10-13T14:30:06.000000Z",
    "updated_at": "2025-10-13T14:30:06.000000Z"
  },
  "message": "Survey retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Complete survey data (SurveyResource) |
| `data.id` | integer | Survey ID |
| `data.organization_id` | integer | Organization ID |
| `data.survey_name` | string | Survey name/title |
| `data.description` | string\|null | Survey description |
| `data.status` | string | Survey status (Draft, Published, Archived) |
| `data.survey_steps` | array | Array of survey steps |
| `data.created_at` | string | Creation timestamp (ISO 8601) |
| `data.updated_at` | string | Last update timestamp (ISO 8601) |
| `message` | string | Success message |

### Survey Step Structure

Each step in `survey_steps` contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Step ID |
| `survey_id` | integer | Survey ID |
| `step` | string | Step title/name |
| `tagline` | string\|null | Step description/tagline |
| `order` | integer | Step order/sequence |
| `survey_fields` | array | Array of survey fields/questions |

### Survey Field Structure

Each field in `survey_fields` contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Field ID |
| `organization_id` | integer | Organization ID |
| `survey_id` | integer | Survey ID |
| `name` | string | Field/question title |
| `type` | string | Field type (Short Answer, Paragraph, Choice, etc.) |
| `description` | string\|null | Field description/help text |
| `is_required` | boolean | Whether field is required |
| `options` | array | Options array (for choice-based fields) |

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get survey by ID
async function getSurveyById(surveyId, token) {
  try {
    const response = await fetch('http://your-api-url/api/survey/show', {
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
      throw new Error(data.message || 'Failed to retrieve survey');
    }

    return data.data; // Returns survey object with steps and fields
  } catch (error) {
    console.error('Error fetching survey:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const survey = await getSurveyById(2, token);
console.log('Survey:', survey);
console.log('Steps:', survey.survey_steps);
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

// Get survey by ID
async function getSurveyById(surveyId) {
  try {
    const response = await apiClient.post('/survey/show', {
      id: surveyId
    });
    
    return response.data.data; // Returns survey object
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const survey = await getSurveyById(2);
console.log('Survey:', survey);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface SurveyField {
  id: number;
  organization_id: number;
  survey_id: number;
  name: string;
  type: string;
  description: string | null;
  is_required: boolean;
  options: (number | string)[];
}

interface SurveyStep {
  id: number;
  survey_id: number;
  step: string;
  tagline: string | null;
  order: number;
  survey_fields: SurveyField[];
}

interface Survey {
  id: number;
  organization_id: number;
  survey_name: string;
  description: string | null;
  status: string;
  survey_steps: SurveyStep[];
  created_at: string;
  updated_at: string;
}

interface SurveyShowResponse {
  data: Survey;
  message: string;
}

export function useSurveyShow(token: string, surveyId: number | null) {
  const [survey, setSurvey] = useState<Survey | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchSurvey = async () => {
      if (!surveyId) {
        setSurvey(null);
        return;
      }

      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<SurveyShowResponse>(
          '/api/survey/show',
          { id: surveyId },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setSurvey(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching survey:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token && surveyId) {
      fetchSurvey();
    }
  }, [token, surveyId]);

  return { survey, loading, error };
}

// Usage in component
function SurveyView({ token, surveyId }: { token: string; surveyId: number }) {
  const { survey, loading, error } = useSurveyShow(token, surveyId);

  if (loading) return <div>Loading survey...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!survey) return <div>No survey found</div>;

  return (
    <div>
      <h1>{survey.survey_name}</h1>
      <p>{survey.description}</p>
      <p>Status: {survey.status}</p>
      
      {survey.survey_steps.map((step, index) => (
        <div key={step.id}>
          <h2>Step {index + 1}: {step.step}</h2>
          <p>{step.tagline}</p>
          
          {step.survey_fields.map(field => (
            <div key={field.id}>
              <h3>{field.name}</h3>
              <p>Type: {field.type}</p>
              {field.description && <p>{field.description}</p>}
              {field.is_required && <span>Required</span>}
              {field.options && field.options.length > 0 && (
                <ul>
                  {field.options.map((option, idx) => (
                    <li key={idx}>{option}</li>
                  ))}
                </ul>
              )}
            </div>
          ))}
        </div>
      ))}
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState, useEffect } from 'react';

function SurveyDetail({ surveyId, token, onEdit }) {
  const [survey, setSurvey] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchSurvey();
  }, [surveyId]);

  const fetchSurvey = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/survey/show', {
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
        throw new Error(data.message || 'Failed to retrieve survey');
      }

      setSurvey(data.data);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching survey:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div>Loading survey...</div>;
  }

  if (error) {
    return <div>Error: {error}</div>;
  }

  if (!survey) {
    return <div>Survey not found</div>;
  }

  return (
    <div style={{ padding: '20px' }}>
      <div style={{ marginBottom: '20px' }}>
        <h1>{survey.survey_name}</h1>
        <p>{survey.description || 'No description'}</p>
        <span style={{
          padding: '4px 8px',
          borderRadius: '4px',
          backgroundColor: survey.status === 'Published' ? '#d4edda' : 
                          survey.status === 'Draft' ? '#fff3cd' : '#f8d7da',
          color: survey.status === 'Published' ? '#155724' : 
                 survey.status === 'Draft' ? '#856404' : '#721c24'
        }}>
          {survey.status}
        </span>
        <button onClick={() => onEdit(survey)} style={{ marginLeft: '10px' }}>
          Edit
        </button>
      </div>

      {survey.survey_steps.map((step, stepIndex) => (
        <div key={step.id} style={{ 
          marginBottom: '30px', 
          padding: '20px', 
          border: '1px solid #ddd', 
          borderRadius: '8px' 
        }}>
          <div style={{ marginBottom: '15px' }}>
            <h2>Step {step.order}: {step.step}</h2>
            {step.tagline && <p style={{ color: '#666' }}>{step.tagline}</p>}
          </div>

          <div>
            <h3>Questions ({step.survey_fields.length})</h3>
            {step.survey_fields.map((field, fieldIndex) => (
              <div key={field.id} style={{
                marginBottom: '20px',
                padding: '15px',
                backgroundColor: '#f9f9f9',
                borderRadius: '4px'
              }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
                  <h4 style={{ margin: 0 }}>
                    {fieldIndex + 1}. {field.name}
                    {field.is_required && (
                      <span style={{ color: 'red', marginLeft: '5px' }}>*</span>
                    )}
                  </h4>
                  <span style={{
                    padding: '2px 8px',
                    backgroundColor: '#e9ecef',
                    borderRadius: '4px',
                    fontSize: '12px'
                  }}>
                    {field.type}
                  </span>
                </div>
                
                {field.description && (
                  <p style={{ color: '#666', fontSize: '14px', margin: '5px 0' }}>
                    {field.description}
                  </p>
                )}

                {field.options && field.options.length > 0 && (
                  <div style={{ marginTop: '10px' }}>
                    <strong>Options:</strong>
                    <ul style={{ margin: '5px 0', paddingLeft: '20px' }}>
                      {field.options.map((option, idx) => (
                        <li key={idx}>{option}</li>
                      ))}
                    </ul>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      ))}

      <div style={{ marginTop: '20px', fontSize: '12px', color: '#666' }}>
        Created: {new Date(survey.created_at).toLocaleString()}
        {survey.updated_at !== survey.created_at && (
          <span> | Updated: {new Date(survey.updated_at).toLocaleString()}</span>
        )}
      </div>
    </div>
  );
}

// Usage
function SurveyPage({ surveyId, token }) {
  const handleEdit = (survey) => {
    // Navigate to edit page or open edit modal
    console.log('Edit survey:', survey);
  };

  return (
    <SurveyDetail 
      surveyId={surveyId} 
      token={token} 
      onEdit={handleEdit}
    />
  );
}
```

---

## Integration Example: Load Survey for Editing

```javascript
// Load survey data for editing form
async function loadSurveyForEdit(surveyId, token) {
  try {
    const response = await fetch('/api/survey/show', {
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
      throw new Error(data.message || 'Failed to retrieve survey');
    }

    // Transform data for edit form
    const surveyData = {
      id: data.data.id,
      survey_name: data.data.survey_name,
      description: data.data.description,
      status: data.data.status,
      survey_steps: data.data.survey_steps.map(step => ({
        step: step.step,
        tagline: step.tagline,
        order: step.order,
        survey_fields: step.survey_fields.map(field => ({
          name: field.name,
          type: field.type,
          description: field.description,
          is_required: field.is_required,
          options: field.options,
          order: field.order || 0
        }))
      }))
    };

    return surveyData;
  } catch (error) {
    console.error('Error loading survey:', error);
    throw error;
  }
}

// Usage
const surveyData = await loadSurveyForEdit(2, token);
// Use surveyData to populate edit form
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/survey/show" \
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
| `200` | Success - Survey retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Survey not found or doesn't belong to user's organization, or no organization found |
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
  "message": "No query results for model [App\\Models\\Survey] 999"
}
```

### No Organization (404)

```json
{
  "message": "No organization found. Please create a survey first."
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

### 1. Complete Data Structure
- The response includes the complete survey structure with all steps and fields
- All relationships are eagerly loaded (steps and fields are included)
- This is useful for displaying the full survey or populating edit forms

### 2. Organization Scope
- Only surveys from the authenticated user's organization can be retrieved
- If the survey ID exists but belongs to a different organization, you'll get a 404 error
- Users cannot access surveys from other organizations

### 3. Response Size
- The response can be large if the survey has many steps and fields
- Consider implementing lazy loading or pagination for very large surveys in the UI

### 4. Field Ordering
- Steps are ordered by their `order` field
- Fields within each step are also ordered by their `order` field
- Use these order values to maintain the correct sequence in your UI

### 5. Field Types
- The `type` field contains the question type (e.g., "Short Answer", "Paragraph", "Choice")
- For choice-based types, the `options` array will contain the available choices
- Use the type to render the appropriate input component in your form

### 6. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the surveys table

---

## Best Practices

1. **Loading States**: Show loading indicators while fetching survey data
2. **Error Handling**: Handle 404 and 422 errors gracefully
3. **Data Preprocessing**: Transform the API response data to match your form structure
4. **Caching**: Consider caching survey data to avoid unnecessary API calls
5. **Lazy Loading**: For large surveys, consider lazy loading steps/fields
6. **Form Population**: Use the response data directly to populate edit forms

---

## Example: Pre-populate Edit Form

```javascript
function SurveyEditForm({ surveyId, token }) {
  const [formData, setFormData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadSurvey = async () => {
      try {
        const response = await fetch('/api/survey/show', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ id: surveyId })
        });

        const data = await response.json();
        
        if (response.ok) {
          // Transform API response to form data format
          setFormData({
            id: data.data.id,
            survey_name: data.data.survey_name,
            description: data.data.description,
            status: data.data.status,
            survey_steps: data.data.survey_steps.map(step => ({
              step: step.step,
              tagline: step.tagline,
              order: step.order,
              survey_fields: step.survey_fields.map(field => ({
                name: field.name,
                type: field.type,
                description: field.description,
                is_required: field.is_required,
                options: field.options,
                order: field.order
              }))
            }))
          });
        }
      } catch (error) {
        console.error('Error loading survey:', error);
      } finally {
        setLoading(false);
      }
    };

    loadSurvey();
  }, [surveyId, token]);

  if (loading) return <div>Loading...</div>;
  if (!formData) return <div>Survey not found</div>;

  return (
    <form>
      <input
        type="text"
        value={formData.survey_name}
        onChange={(e) => setFormData({...formData, survey_name: e.target.value})}
      />
      {/* Render other form fields */}
    </form>
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/survey/show` with Bearer token and `{"id": 2}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **List Surveys**: `/api/survey/index` - Get paginated list of surveys
- **Save Survey**: `/api/survey/save` - Create or update survey
- **Delete Survey**: `/api/survey/delete` - Delete survey

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

