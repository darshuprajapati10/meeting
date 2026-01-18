# Survey Step Show API Documentation

## Overview
This API endpoint retrieves a single survey step by its ID. It returns the complete survey step data including all associated survey fields (questions). The survey step must belong to a survey in the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey-step/show`  
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
  "id": 5
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | Survey Step ID to retrieve |

---

## Response Format

### Success Response (200)

```json
{
  "data": {
    "id": 5,
    "survey_id": 1,
    "step": "Step 1",
    "tagline": "Introduction and basic information",
    "order": 1,
    "survey_fields": [
      {
        "id": 10,
        "organization_id": 1,
        "survey_id": 1,
        "survey_step_id": 5,
        "name": "What is your name?",
        "type": "Short Answer",
        "description": "Enter your full name",
        "is_required": true,
        "options": [],
        "order": 1
      },
      {
        "id": 11,
        "organization_id": 1,
        "survey_id": 1,
        "survey_step_id": 5,
        "name": "Feedback",
        "type": "Paragraph",
        "description": "Please provide your feedback",
        "is_required": false,
        "options": [],
        "order": 2
      }
    ],
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  },
  "message": "Survey step retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Complete survey step data (SurveyStepResource) |
| `data.id` | integer | Survey Step ID |
| `data.survey_id` | integer | Survey ID that this step belongs to |
| `data.step` | string | Step title/name |
| `data.tagline` | string\|null | Step description/tagline |
| `data.order` | integer | Step order/sequence within the survey |
| `data.survey_fields` | array | Array of survey fields/questions in this step |
| `data.created_at` | string | Creation timestamp (ISO 8601) |
| `data.updated_at` | string | Last update timestamp (ISO 8601) |
| `message` | string | Success message |

### Survey Field Structure

Each field in `survey_fields` contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Field ID |
| `organization_id` | integer | Organization ID |
| `survey_id` | integer | Survey ID |
| `survey_step_id` | integer | Survey Step ID |
| `name` | string | Field/question title |
| `type` | string | Field type (Short Answer, Paragraph, Choice, etc.) |
| `description` | string\|null | Field description/help text |
| `is_required` | boolean | Whether field is required |
| `options` | array | Options array (for choice-based fields) |
| `order` | integer | Field order/sequence within the step |

**Note:** Fields are ordered by their `order` field value.

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get survey step by ID
async function getSurveyStepById(stepId, token) {
  try {
    const response = await fetch('http://your-api-url/api/survey-step/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: stepId
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve survey step');
    }

    return data.data; // Returns survey step object with fields
  } catch (error) {
    console.error('Error fetching survey step:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const surveyStep = await getSurveyStepById(5, token);
console.log('Survey Step:', surveyStep);
console.log('Fields:', surveyStep.survey_fields);
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

// Get survey step by ID
async function getSurveyStepById(stepId) {
  try {
    const response = await apiClient.post('/survey-step/show', {
      id: stepId
    });
    
    return response.data.data; // Returns survey step object
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const surveyStep = await getSurveyStepById(5);
console.log('Survey Step:', surveyStep);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface SurveyField {
  id: number;
  organization_id: number;
  survey_id: number;
  survey_step_id: number;
  name: string;
  type: string;
  description: string | null;
  is_required: boolean;
  options: (number | string)[];
  order: number;
}

interface SurveyStep {
  id: number;
  survey_id: number;
  step: string;
  tagline: string | null;
  order: number;
  survey_fields: SurveyField[];
  created_at: string;
  updated_at: string;
}

interface SurveyStepShowResponse {
  data: SurveyStep;
  message: string;
}

export function useSurveyStepShow(token: string, stepId: number | null) {
  const [surveyStep, setSurveyStep] = useState<SurveyStep | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchSurveyStep = async () => {
      if (!stepId) {
        setSurveyStep(null);
        return;
      }

      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<SurveyStepShowResponse>(
          '/api/survey-step/show',
          { id: stepId },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setSurveyStep(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching survey step:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token && stepId) {
      fetchSurveyStep();
    }
  }, [token, stepId]);

  return { surveyStep, loading, error };
}

// Usage in component
function SurveyStepView({ token, stepId }: { token: string; stepId: number }) {
  const { surveyStep, loading, error } = useSurveyStepShow(token, stepId);

  if (loading) return <div>Loading survey step...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!surveyStep) return <div>No survey step found</div>;

  return (
    <div>
      <h1>Step {surveyStep.order}: {surveyStep.step}</h1>
      {surveyStep.tagline && <p>{surveyStep.tagline}</p>}
      
      <h2>Questions ({surveyStep.survey_fields.length})</h2>
      {surveyStep.survey_fields.map((field, index) => (
        <div key={field.id}>
          <h3>{index + 1}. {field.name}</h3>
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
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState, useEffect } from 'react';

function SurveyStepDetail({ stepId, token, onEdit }) {
  const [surveyStep, setSurveyStep] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchSurveyStep();
  }, [stepId]);

  const fetchSurveyStep = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/survey-step/show', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: stepId })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to retrieve survey step');
      }

      setSurveyStep(data.data);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching survey step:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div>Loading survey step...</div>;
  }

  if (error) {
    return <div>Error: {error}</div>;
  }

  if (!surveyStep) {
    return <div>Survey step not found</div>;
  }

  return (
    <div style={{ padding: '20px' }}>
      <div style={{ marginBottom: '20px' }}>
        <h1>Step {surveyStep.order}: {surveyStep.step}</h1>
        {surveyStep.tagline && (
          <p style={{ color: '#666', fontSize: '16px' }}>
            {surveyStep.tagline}
          </p>
        )}
        <button onClick={() => onEdit(surveyStep)} style={{ marginTop: '10px' }}>
          Edit Step
        </button>
      </div>

      <div>
        <h2>Questions ({surveyStep.survey_fields.length})</h2>
        {surveyStep.survey_fields.length === 0 ? (
          <p style={{ color: '#999', fontStyle: 'italic' }}>
            No questions in this step yet.
          </p>
        ) : (
          surveyStep.survey_fields.map((field, fieldIndex) => (
            <div key={field.id} style={{
              marginBottom: '20px',
              padding: '15px',
              backgroundColor: '#f9f9f9',
              borderRadius: '4px',
              border: '1px solid #e0e0e0'
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
                <h3 style={{ margin: 0 }}>
                  {fieldIndex + 1}. {field.name}
                  {field.is_required && (
                    <span style={{ color: 'red', marginLeft: '5px' }}>*</span>
                  )}
                </h3>
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

              <div style={{ 
                marginTop: '10px', 
                fontSize: '12px', 
                color: '#999',
                display: 'flex',
                gap: '15px'
              }}>
                <span>Order: {field.order}</span>
                {field.is_required && <span>Required</span>}
              </div>
            </div>
          ))
        )}
      </div>

      <div style={{ marginTop: '20px', fontSize: '12px', color: '#666' }}>
        Created: {new Date(surveyStep.created_at).toLocaleString()}
        {surveyStep.updated_at !== surveyStep.created_at && (
          <span> | Updated: {new Date(surveyStep.updated_at).toLocaleString()}</span>
        )}
      </div>
    </div>
  );
}

// Usage
function SurveyStepPage({ stepId, token }) {
  const handleEdit = (step) => {
    // Navigate to edit page or open edit modal
    console.log('Edit step:', step);
  };

  return (
    <SurveyStepDetail 
      stepId={stepId} 
      token={token} 
      onEdit={handleEdit}
    />
  );
}
```

---

## Integration Example: Load Survey Step for Editing

```javascript
// Load survey step data for editing form
async function loadSurveyStepForEdit(stepId, token) {
  try {
    const response = await fetch('/api/survey-step/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ id: stepId })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve survey step');
    }

    // Transform data for edit form
    const stepData = {
      id: data.data.id,
      survey_id: data.data.survey_id,
      step: data.data.step,
      tagline: data.data.tagline,
      order: data.data.order
    };

    return stepData;
  } catch (error) {
    console.error('Error loading survey step:', error);
    throw error;
  }
}

// Usage
const stepData = await loadSurveyStepForEdit(5, token);
// Use stepData to populate edit form
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/survey-step/show" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 5
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Survey step retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Survey step not found, doesn't belong to user's organization, or no organization found |
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

### Survey Step Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\SurveyStep] 999"
}
```

### No Organization (404)

```json
{
  "message": "No organization found. Please create an organization first."
}
```

### Survey Step ID Doesn't Exist (422)

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
- The response includes the complete survey step structure with all associated fields
- All relationships are eagerly loaded (fields are included)
- This is useful for displaying the full step or populating edit forms

### 2. Organization Scope
- Only survey steps from surveys in the authenticated user's organization can be retrieved
- If the survey step ID exists but belongs to a survey in a different organization, you'll get a 404 error
- Users cannot access survey steps from other organizations

### 3. Survey Fields
- The `survey_fields` array contains all fields (questions) associated with this step
- Fields are ordered by their `order` field value
- Fields can be empty if the step has no questions yet

### 4. Field Ordering
- Fields within the step are ordered by their `order` field
- Use these order values to maintain the correct sequence in your UI

### 5. Field Types
- The `type` field contains the question type (e.g., "Short Answer", "Paragraph", "Choice")
- For choice-based types, the `options` array will contain the available choices
- Use the type to render the appropriate input component in your form

### 6. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the survey_steps table
- Must belong to a survey in the user's organization

### 7. Survey Fields Management
- Survey fields are managed separately through the survey save API
- This endpoint only retrieves the step and its associated fields
- To modify fields, use the survey save API which manages the entire survey structure

---

## Use Cases

### 1. View Step Details
Display all information about a specific survey step including its questions:

```javascript
const step = await getSurveyStepById(5, token);
console.log(`Step: ${step.step}`);
console.log(`Questions: ${step.survey_fields.length}`);
```

### 2. Pre-populate Edit Form
Load step data to edit:

```javascript
const step = await getSurveyStepById(5, token);
// Use step.step, step.tagline, step.order for form fields
```

### 3. Display Step in Survey Builder
Show step with all its questions in a survey builder interface:

```javascript
const step = await getSurveyStepById(5, token);
// Render step.step as title, step.tagline as description
// Render each field in step.survey_fields
```

### 4. Validate Step Before Deletion
Check if step has fields before allowing deletion:

```javascript
const step = await getSurveyStepById(5, token);
if (step.survey_fields.length > 0) {
  alert('Cannot delete step with questions. Remove questions first.');
}
```

---

## Best Practices

1. **Loading States**: Show loading indicators while fetching survey step data
2. **Error Handling**: Handle 404 and 422 errors gracefully
3. **Data Preprocessing**: Transform the API response data to match your form structure
4. **Caching**: Consider caching survey step data to avoid unnecessary API calls
5. **Field Rendering**: Use the field `type` to render appropriate input components
6. **Order Preservation**: Maintain field order using the `order` field value

---

## Example: Pre-populate Edit Form

```javascript
function SurveyStepEditForm({ stepId, token }) {
  const [formData, setFormData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadStep = async () => {
      try {
        const response = await fetch('/api/survey-step/show', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ id: stepId })
        });

        const data = await response.json();
        
        if (response.ok) {
          setFormData({
            id: data.data.id,
            survey_id: data.data.survey_id,
            step: data.data.step,
            tagline: data.data.tagline,
            order: data.data.order
          });
        }
      } catch (error) {
        console.error('Error loading step:', error);
      } finally {
        setLoading(false);
      }
    };

    loadStep();
  }, [stepId, token]);

  if (loading) return <div>Loading...</div>;
  if (!formData) return <div>Survey step not found</div>;

  return (
    <form>
      <input
        type="text"
        value={formData.step}
        onChange={(e) => setFormData({...formData, step: e.target.value})}
        placeholder="Step name"
      />
      <textarea
        value={formData.tagline || ''}
        onChange={(e) => setFormData({...formData, tagline: e.target.value})}
        placeholder="Step description"
      />
      <input
        type="number"
        value={formData.order}
        onChange={(e) => setFormData({...formData, order: parseInt(e.target.value)})}
        min="0"
      />
      {/* Submit button */}
    </form>
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/survey-step/show` with Bearer token and `{"id": 5}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

### Test Cases

1. **Valid Step ID**: Should return 200 with step data
2. **Invalid Step ID**: Should return 404 or 422
3. **Missing ID**: Should return 422 validation error
4. **Unauthorized**: Should return 401 without token
5. **Step from Different Organization**: Should return 404

---

## Related Endpoints

- **List Survey Steps**: `/api/survey-step/index` - Get paginated list of survey steps for a survey
- **Save Survey Step**: `/api/survey-step/save` - Create or update survey step
- **Delete Survey Step**: `/api/survey-step/delete` - Delete survey step
- **Show Survey**: `/api/survey/show` - Get survey details including all steps
- **Save Survey**: `/api/survey/save` - Save entire survey with steps and fields

---

## Troubleshooting

### Issue: "Survey step not found or you do not have permission to access it"

**Solution:** Ensure that:
- The `id` exists in the database
- The survey step belongs to a survey in your organization
- You're using the correct authentication token

### Issue: "No organization found"

**Solution:** The authenticated user must be associated with an organization. Create an organization first or ensure your user account is properly linked to an organization.

### Issue: Validation errors for ID

**Solution:** Ensure the `id` field is provided and is a valid integer:
- `id` must be present in the request body
- `id` must be an integer
- `id` must exist in the survey_steps table

### Issue: Empty survey_fields array

**Solution:** This is normal if the step has no questions yet. Fields are managed separately through the survey save API.

---

## Version History

- **v1.0** (2025-01-15) - Initial release of Survey Step Show API

