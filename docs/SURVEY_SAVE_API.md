# Survey Save API Documentation

## Overview
This API endpoint allows you to create or update a survey with multiple steps and fields. The same endpoint handles both operations - if an `id` is provided, it updates the existing survey (and replaces all steps and fields); otherwise, it creates a new one. Surveys can have multiple steps, and each step can have multiple fields (questions).

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/save`  
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

### Creating a New Survey

When creating a survey, **do not** include the `id` field:

```json
{
  "survey_name": "Customer Satisfaction Survey",
  "description": "Survey to measure customer satisfaction",
  "status": "Draft",
  "survey_steps": [
    {
      "step": "Step 1",
      "tagline": "Introduction and basic information",
      "order": 1,
      "survey_fields": [
        {
          "name": "What is your name?",
          "type": "Short Answer",
          "description": "Enter your full name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Feedback",
          "type": "Paragraph",
          "description": "Please provide your feedback",
          "is_required": false,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Step 2",
      "tagline": "Rating",
      "order": 2,
      "survey_fields": [
        {
          "name": "Rate us",
          "type": "Choice",
          "description": "How satisfied are you?",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        }
      ]
    }
  ]
}
```

### Updating an Existing Survey

When updating a survey, **include** the `id` field. Note: All existing steps and fields will be deleted and replaced with the new data:

```json
{
  "id": 2,
  "survey_name": "Updated Survey Name",
  "description": "Updated description",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Updated Step",
      "tagline": "Updated tagline",
      "order": 1,
      "survey_fields": [
        {
          "name": "Updated Question",
          "type": "Short Answer",
          "description": "Updated description",
          "is_required": false,
          "options": null,
          "order": 1
        }
      ]
    }
  ]
}
```

---

## Field Specifications

### Survey Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | Survey ID (only for updates) | Must exist in surveys table |
| `survey_name` | string | **Yes** | Survey title/name | Max 255 characters |
| `description` | string | No | Survey description | No max length |
| `status` | string | **Yes** | Survey status | Must be: `Draft`, `Published`, or `Archived` |
| `survey_steps` | array | **Yes** | Array of survey steps | Must contain at least one step |

### Survey Step Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `step` | string | **Yes** | Step title/name | Max 255 characters |
| `tagline` | string | No | Step description/tagline | No max length |
| `order` | integer | **Yes** | Step order/sequence | Must be unique within survey |
| `survey_fields` | array | **Yes** | Array of fields/questions | Must contain at least one field |

### Survey Field Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `name` | string | **Yes** | Field/question title | Max 255 characters |
| `type` | string | **Yes** | Field type | See supported types below |
| `description` | string | No | Field description/help text | No max length |
| `is_required` | boolean | No | Whether field is required | Default: `false` |
| `options` | array | No | Options for choice-based fields | Array of values (numbers or strings) |
| `order` | integer | No | Field order/sequence | Default: `0` |

### Supported Field Types

The following field types are supported:

- `Short Answer` - Single line text input
- `Paragraph` - Multi-line text input
- `Email` - Email input with validation
- `Multiple Choice` - Single selection from options
- `Checkboxes` - Multiple selections from options
- `Dropdown` - Dropdown selection from options
- `Choice` - Single choice selection (same as Multiple Choice)
- `Rating Scale` - Rating scale (use options array for scale values)
- `Date` - Date picker
- `Number` - Numeric input
- `File Upload` - File upload field

**Note:** For choice-based types (`Multiple Choice`, `Checkboxes`, `Dropdown`, `Choice`, `Rating Scale`), you must provide an `options` array.

---

## Response Format

### Success Response - Survey Created (201)

```json
{
  "data": {
    "id": 2,
    "organization_id": 1,
    "survey_name": "Gattu",
    "description": "Sarkari",
    "status": "Draft",
    "survey_steps": [
      {
        "id": 3,
        "survey_id": 2,
        "step": "abc",
        "tagline": "Intro",
        "order": 1,
        "survey_fields": [
          {
            "id": 4,
            "organization_id": 1,
            "survey_id": 2,
            "name": "etgfte",
            "type": "Short Answer",
            "description": "eqtyg4wty",
            "is_required": false,
            "options": [1, 2]
          },
          {
            "id": 5,
            "organization_id": 1,
            "survey_id": 2,
            "name": "Feedback",
            "type": "Paragraph",
            "description": "desc2",
            "is_required": false,
            "options": [1, 2]
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
            "description": "desc3",
            "is_required": false,
            "options": [1, 2, 3, 4, 5]
          }
        ]
      }
    ],
    "created_at": "2025-10-13T14:30:06.000000Z",
    "updated_at": "2025-10-13T14:30:06.000000Z"
  },
  "message": "Survey created successfully."
}
```

### Success Response - Survey Updated (200)

```json
{
  "data": {
    "id": 2,
    "organization_id": 1,
    "survey_name": "Updated Survey Name",
    "description": "Updated description",
    "status": "Published",
    "survey_steps": [
      {
        "id": 7,
        "survey_id": 2,
        "step": "Updated Step",
        "tagline": "Updated tagline",
        "order": 1,
        "survey_fields": [
          {
            "id": 8,
            "organization_id": 1,
            "survey_id": 2,
            "name": "Updated Question",
            "type": "Short Answer",
            "description": "Updated description",
            "is_required": false,
            "options": []
          }
        ]
      }
    ],
    "created_at": "2025-10-13T14:30:06.000000Z",
    "updated_at": "2025-10-13T15:45:00.000000Z"
  },
  "message": "Survey updated successfully."
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Save survey (create or update)
async function saveSurvey(surveyData, token) {
  try {
    const response = await fetch('http://your-api-url/api/survey/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(surveyData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to save survey');
    }

    return data; // Returns { data: {...}, message: "..." }
  } catch (error) {
    console.error('Error saving survey:', error);
    throw error;
  }
}

// Create new survey
const newSurvey = {
  survey_name: "Customer Satisfaction Survey",
  description: "Survey to measure customer satisfaction",
  status: "Draft",
  survey_steps: [
    {
      step: "Step 1",
      tagline: "Introduction",
      order: 1,
      survey_fields: [
        {
          name: "What is your name?",
          type: "Short Answer",
          description: "Enter your full name",
          is_required: true,
          options: null,
          order: 1
        }
      ]
    }
  ]
};

const token = localStorage.getItem('auth_token');
const result = await saveSurvey(newSurvey, token);
console.log('Survey created:', result.data);

// Update existing survey
const updatedSurvey = {
  id: 2,
  survey_name: "Updated Survey Name",
  description: "Updated description",
  status: "Published",
  survey_steps: [
    // ... steps data
  ]
};
const updateResult = await saveSurvey(updatedSurvey, token);
console.log('Survey updated:', updateResult.data);
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

// Save survey
async function saveSurvey(surveyData) {
  try {
    const response = await apiClient.post('/survey/save', surveyData);
    return response.data; // Returns { data: {...}, message: "..." }
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const surveyData = {
  survey_name: "Customer Satisfaction Survey",
  description: "Survey description",
  status: "Draft",
  survey_steps: [
    {
      step: "Step 1",
      tagline: "Introduction",
      order: 1,
      survey_fields: [
        {
          name: "What is your name?",
          type: "Short Answer",
          is_required: true,
          order: 1
        }
      ]
    }
  ]
};

const result = await saveSurvey(surveyData);
console.log(result.message);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface SurveyField {
  name: string;
  type: string;
  description?: string;
  is_required?: boolean;
  options?: (number | string)[];
  order?: number;
}

interface SurveyStep {
  step: string;
  tagline?: string;
  order: number;
  survey_fields: SurveyField[];
}

interface SurveyData {
  id?: number;
  survey_name: string;
  description?: string;
  status: 'Draft' | 'Published' | 'Archived';
  survey_steps: SurveyStep[];
}

interface SurveyResponse {
  data: {
    id: number;
    organization_id: number;
    survey_name: string;
    description: string | null;
    status: string;
    survey_steps: Array<{
      id: number;
      survey_id: number;
      step: string;
      tagline: string | null;
      order: number;
      survey_fields: Array<{
        id: number;
        organization_id: number;
        survey_id: number;
        name: string;
        type: string;
        description: string | null;
        is_required: boolean;
        options: (number | string)[];
      }>;
    }>;
    created_at: string;
    updated_at: string;
  };
  message: string;
}

export function useSaveSurvey(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const saveSurvey = async (surveyData: SurveyData) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<SurveyResponse>(
        '/api/survey/save',
        surveyData,
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

  return { saveSurvey, loading, error };
}

// Usage in component
function SurveyForm({ token }: { token: string }) {
  const { saveSurvey, loading, error } = useSaveSurvey(token);

  const handleSubmit = async (formData: SurveyData) => {
    try {
      const result = await saveSurvey(formData);
      alert(result.message);
      // Redirect or refresh survey list
    } catch (error: any) {
      alert('Error: ' + error.message);
    }
  };

  return (
    <div>
      {/* Your form UI */}
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function SurveyForm({ token, onSaveSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({
    survey_name: '',
    description: '',
    status: 'Draft',
    survey_steps: []
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/survey/save', {
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
        throw new Error(data.message || 'Failed to save survey');
      }

      if (onSaveSuccess) {
        onSaveSuccess(data.data);
      }

      alert(data.message || 'Survey saved successfully');
    } catch (err) {
      setError(err.message);
      alert('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const addStep = () => {
    setFormData(prev => ({
      ...prev,
      survey_steps: [
        ...prev.survey_steps,
        {
          step: `Step ${prev.survey_steps.length + 1}`,
          tagline: '',
          order: prev.survey_steps.length + 1,
          survey_fields: []
        }
      ]
    }));
  };

  const addField = (stepIndex) => {
    const updatedSteps = [...formData.survey_steps];
    updatedSteps[stepIndex].survey_fields.push({
      name: '',
      type: 'Short Answer',
      description: '',
      is_required: false,
      options: null,
      order: updatedSteps[stepIndex].survey_fields.length + 1
    });
    setFormData(prev => ({ ...prev, survey_steps: updatedSteps }));
  };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>Survey Name *</label>
        <input
          type="text"
          value={formData.survey_name}
          onChange={(e) => setFormData(prev => ({ ...prev, survey_name: e.target.value }))}
          required
        />
      </div>

      <div>
        <label>Description</label>
        <textarea
          value={formData.description}
          onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
        />
      </div>

      <div>
        <label>Status *</label>
        <select
          value={formData.status}
          onChange={(e) => setFormData(prev => ({ ...prev, status: e.target.value }))}
          required
        >
          <option value="Draft">Draft</option>
          <option value="Published">Published</option>
          <option value="Archived">Archived</option>
        </select>
      </div>

      {/* Steps and Fields UI */}
      <div>
        {formData.survey_steps.map((step, stepIndex) => (
          <div key={stepIndex}>
            <h3>Step {stepIndex + 1}</h3>
            <input
              type="text"
              placeholder="Step title"
              value={step.step}
              onChange={(e) => {
                const updatedSteps = [...formData.survey_steps];
                updatedSteps[stepIndex].step = e.target.value;
                setFormData(prev => ({ ...prev, survey_steps: updatedSteps }));
              }}
            />
            {/* Add fields UI here */}
          </div>
        ))}
        <button type="button" onClick={addStep}>Add Step</button>
      </div>

      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Survey'}
      </button>

      {error && <div style={{ color: 'red' }}>{error}</div>}
    </form>
  );
}
```

---

## cURL Example

### Create New Survey

```bash
curl -X POST "http://your-api-url/api/survey/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "survey_name": "Customer Satisfaction Survey",
    "description": "Survey description",
    "status": "Draft",
    "survey_steps": [
      {
        "step": "Step 1",
        "tagline": "Introduction",
        "order": 1,
        "survey_fields": [
          {
            "name": "What is your name?",
            "type": "Short Answer",
            "description": "Enter your full name",
            "is_required": true,
            "options": null,
            "order": 1
          }
        ]
      }
    ]
  }'
```

### Update Existing Survey

```bash
curl -X POST "http://your-api-url/api/survey/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 2,
    "survey_name": "Updated Survey",
    "description": "Updated description",
    "status": "Published",
    "survey_steps": [
      {
        "step": "Updated Step",
        "tagline": "Updated tagline",
        "order": 1,
        "survey_fields": [
          {
            "name": "Updated Question",
            "type": "Short Answer",
            "is_required": false,
            "order": 1
          }
        ]
      }
    ]
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `201` | Success - Survey created successfully |
| `200` | Success - Survey updated successfully |
| `401` | Unauthorized (missing or invalid token) |
| `422` | Validation error (missing or invalid parameters) |
| `404` | Survey not found (when updating with invalid ID) |
| `500` | Server error |

---

## Error Responses

### Validation Error (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "survey_name": [
      "The survey name field is required."
    ],
    "survey_steps.0.step": [
      "The survey steps.0.step field is required."
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

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

### Server Error (500)

```json
{
  "message": "Error saving survey: [error details]"
}
```

---

## Important Notes

### 1. Update Behavior
- ⚠️ **Important:** When updating a survey (with `id`), all existing steps and fields are **deleted** and replaced with the new data
- This is a complete replacement, not a partial update
- If you want to preserve existing steps/fields, make sure to include all of them in the request

### 2. Organization Scope
- Surveys are automatically associated with the authenticated user's organization
- Users can only create/update surveys within their own organization
- If no organization exists, one will be created automatically

### 3. Field Types and Options
- For choice-based field types (`Multiple Choice`, `Checkboxes`, `Dropdown`, `Choice`, `Rating Scale`), provide an `options` array
- The `options` array can contain numbers or strings (e.g., `[1, 2, 3, 4, 5]` or `["Option 1", "Option 2"]`)
- For non-choice field types, set `options` to `null` or omit it

### 4. Order Fields
- Both `survey_steps` and `survey_fields` support an `order` field
- Orders don't need to be sequential (e.g., 1, 2, 3), but they should be unique within the same survey/step
- Lower numbers appear first

### 5. Required Fields
- `survey_name` is **required**
- `status` is **required** and must be one of: `Draft`, `Published`, `Archived`
- Each step must have at least one `survey_field`
- Each `survey_field` must have a `name` and `type`

---

## Best Practices

1. **Validate on Frontend:** Always validate required fields and data structure before sending the request
2. **Handle Large Surveys:** For surveys with many steps/fields, consider implementing pagination or lazy loading in the UI
3. **Order Management:** Use consistent ordering (1, 2, 3...) for better UX and easier reordering
4. **Error Handling:** Always handle validation errors and show user-friendly messages
5. **Loading States:** Show loading indicators during save operations
6. **Transaction Safety:** The API uses database transactions, so either all data is saved or nothing is saved (atomic operation)

---

## Example: Complete Survey Structure

```json
{
  "survey_name": "Customer Feedback Survey",
  "description": "Comprehensive customer feedback survey",
  "status": "Draft",
  "survey_steps": [
    {
      "step": "Step 1: Introduction",
      "tagline": "Basic Information",
      "order": 1,
      "survey_fields": [
        {
          "name": "What is your name?",
          "type": "Short Answer",
          "description": "Please enter your full name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "What is your email?",
          "type": "Email",
          "description": "We'll use this to contact you",
          "is_required": true,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Step 2: Feedback",
      "tagline": "Your Opinion Matters",
      "order": 2,
      "survey_fields": [
        {
          "name": "How satisfied are you?",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "What can we improve?",
          "type": "Paragraph",
          "description": "Please provide detailed feedback",
          "is_required": false,
          "options": null,
          "order": 2
        },
        {
          "name": "Which features do you use?",
          "type": "Checkboxes",
          "description": "Select all that apply",
          "is_required": false,
          "options": ["Feature A", "Feature B", "Feature C"],
          "order": 3
        }
      ]
    }
  ]
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/survey/save` with Bearer token and request body
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## 20 Complete Survey Data Examples

Here are 20 ready-to-use survey examples covering different use cases:

### Example 1: Employee Satisfaction Survey

```json
{
  "survey_name": "Employee Satisfaction Survey 2024",
  "description": "Annual employee satisfaction and feedback survey",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Personal Information",
      "tagline": "Let's start with some basic information",
      "order": 1,
      "survey_fields": [
        {
          "name": "Employee ID",
          "type": "Short Answer",
          "description": "Enter your employee ID",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Department",
          "type": "Dropdown",
          "description": "Select your department",
          "is_required": true,
          "options": ["IT", "HR", "Finance", "Marketing", "Operations"],
          "order": 2
        }
      ]
    },
    {
      "step": "Satisfaction Rating",
      "tagline": "How satisfied are you?",
      "order": 2,
      "survey_fields": [
        {
          "name": "Overall job satisfaction",
          "type": "Rating Scale",
          "description": "Rate from 1 to 10",
          "is_required": true,
          "options": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
          "order": 1
        },
        {
          "name": "Feedback",
          "type": "Paragraph",
          "description": "Any additional comments?",
          "is_required": false,
          "options": null,
          "order": 2
        }
      ]
    }
  ]
}
```

### Example 2: Customer Product Feedback

```json
{
  "survey_name": "Product Feedback Survey",
  "description": "Help us improve our product",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Product Experience",
      "tagline": "Share your experience",
      "order": 1,
      "survey_fields": [
        {
          "name": "Which product did you purchase?",
          "type": "Multiple Choice",
          "description": "Select one option",
          "is_required": true,
          "options": ["Product A", "Product B", "Product C", "Product D"],
          "order": 1
        },
        {
          "name": "Purchase date",
          "type": "Date",
          "description": "When did you purchase?",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Features you liked",
          "type": "Checkboxes",
          "description": "Select all that apply",
          "is_required": false,
          "options": ["Design", "Performance", "Price", "Support", "Documentation"],
          "order": 3
        }
      ]
    },
    {
      "step": "Rating",
      "tagline": "Rate your experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "Overall rating",
          "type": "Choice",
          "description": "How would you rate?",
          "is_required": true,
          "options": ["Excellent", "Good", "Average", "Poor", "Very Poor"],
          "order": 1
        },
        {
          "name": "Recommendation",
          "type": "Paragraph",
          "description": "Would you recommend this to others? Why?",
          "is_required": false,
          "options": null,
          "order": 2
        }
      ]
    }
  ]
}
```

### Example 3: Event Registration Form

```json
{
  "survey_name": "Tech Conference 2024 Registration",
  "description": "Register for our annual tech conference",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Personal Details",
      "tagline": "Your information",
      "order": 1,
      "survey_fields": [
        {
          "name": "Full Name",
          "type": "Short Answer",
          "description": "Enter your full name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Email Address",
          "type": "Email",
          "description": "We'll send confirmation here",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Phone Number",
          "type": "Number",
          "description": "Your contact number",
          "is_required": true,
          "options": null,
          "order": 3
        }
      ]
    },
    {
      "step": "Event Preferences",
      "tagline": "Tell us your interests",
      "order": 2,
      "survey_fields": [
        {
          "name": "Sessions you want to attend",
          "type": "Checkboxes",
          "description": "Select all sessions you're interested in",
          "is_required": true,
          "options": ["AI/ML", "Web Development", "Mobile Apps", "DevOps", "Cloud Computing"],
          "order": 1
        },
        {
          "name": "Dietary requirements",
          "type": "Dropdown",
          "description": "Select your dietary preference",
          "is_required": false,
          "options": ["None", "Vegetarian", "Vegan", "Gluten-free", "Halal"],
          "order": 2
        }
      ]
    }
  ]
}
```

### Example 4: Health Assessment Survey

```json
{
  "survey_name": "Annual Health Assessment",
  "description": "General health and wellness assessment",
  "status": "Draft",
  "survey_steps": [
    {
      "step": "Basic Information",
      "tagline": "Let's start",
      "order": 1,
      "survey_fields": [
        {
          "name": "Age",
          "type": "Number",
          "description": "Enter your age",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Gender",
          "type": "Multiple Choice",
          "description": "Select your gender",
          "is_required": true,
          "options": ["Male", "Female", "Other", "Prefer not to say"],
          "order": 2
        }
      ]
    },
    {
      "step": "Health Questions",
      "tagline": "Your health information",
      "order": 2,
      "survey_fields": [
        {
          "name": "Exercise frequency",
          "type": "Dropdown",
          "description": "How often do you exercise?",
          "is_required": true,
          "options": ["Daily", "3-4 times/week", "1-2 times/week", "Rarely", "Never"],
          "order": 1
        },
        {
          "name": "Sleep hours per night",
          "type": "Number",
          "description": "Average hours of sleep",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Health concerns",
          "type": "Paragraph",
          "description": "Any specific health concerns?",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 5: Course Feedback Survey

```json
{
  "survey_name": "Online Course Evaluation",
  "description": "Help us improve our courses",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Course Information",
      "tagline": "Which course did you take?",
      "order": 1,
      "survey_fields": [
        {
          "name": "Course Name",
          "type": "Short Answer",
          "description": "Enter course name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Instructor Name",
          "type": "Short Answer",
          "description": "Who was your instructor?",
          "is_required": true,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Feedback",
      "tagline": "Your feedback matters",
      "order": 2,
      "survey_fields": [
        {
          "name": "Course rating",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "What did you like?",
          "type": "Paragraph",
          "description": "Tell us what worked well",
          "is_required": false,
          "options": null,
          "order": 2
        },
        {
          "name": "What can be improved?",
          "type": "Paragraph",
          "description": "Suggestions for improvement",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 6: Restaurant Feedback

```json
{
  "survey_name": "Restaurant Experience Survey",
  "description": "How was your dining experience?",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Visit Details",
      "tagline": "Tell us about your visit",
      "order": 1,
      "survey_fields": [
        {
          "name": "Visit date",
          "type": "Date",
          "description": "When did you visit?",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Number of guests",
          "type": "Number",
          "description": "How many people?",
          "is_required": true,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Experience Rating",
      "tagline": "Rate your experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "Food quality",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "Service quality",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Would you visit again?",
          "type": "Choice",
          "description": "Select your answer",
          "is_required": true,
          "options": ["Yes", "No", "Maybe"],
          "order": 3
        },
        {
          "name": "Additional comments",
          "type": "Paragraph",
          "description": "Any other feedback?",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    }
  ]
}
```

### Example 7: Job Application Form

```json
{
  "survey_name": "Software Developer Application",
  "description": "Apply for software developer position",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Personal Information",
      "tagline": "Your details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Full Name",
          "type": "Short Answer",
          "description": "Enter your full name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Email",
          "type": "Email",
          "description": "Your email address",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Phone",
          "type": "Number",
          "description": "Contact number",
          "is_required": true,
          "options": null,
          "order": 3
        }
      ]
    },
    {
      "step": "Professional Background",
      "tagline": "Your experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "Years of experience",
          "type": "Number",
          "description": "Years in software development",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Programming languages you know",
          "type": "Checkboxes",
          "description": "Select all that apply",
          "is_required": true,
          "options": ["JavaScript", "Python", "Java", "C++", "PHP", "Ruby", "Go"],
          "order": 2
        },
        {
          "name": "Resume",
          "type": "File Upload",
          "description": "Upload your resume (PDF)",
          "is_required": true,
          "options": null,
          "order": 3
        },
        {
          "name": "Cover letter",
          "type": "Paragraph",
          "description": "Tell us why you're interested",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    }
  ]
}
```

### Example 8: Market Research Survey

```json
{
  "survey_name": "Consumer Preferences Survey",
  "description": "Help us understand consumer preferences",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Demographics",
      "tagline": "Basic information",
      "order": 1,
      "survey_fields": [
        {
          "name": "Age range",
          "type": "Dropdown",
          "description": "Select your age range",
          "is_required": true,
          "options": ["18-25", "26-35", "36-45", "46-55", "56+"],
          "order": 1
        },
        {
          "name": "Location",
          "type": "Short Answer",
          "description": "City or region",
          "is_required": true,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Shopping Habits",
      "tagline": "Your shopping preferences",
      "order": 2,
      "survey_fields": [
        {
          "name": "Where do you shop?",
          "type": "Checkboxes",
          "description": "Select all that apply",
          "is_required": true,
          "options": ["Online", "Mall", "Local stores", "Supermarkets", "Other"],
          "order": 1
        },
        {
          "name": "Shopping frequency",
          "type": "Choice",
          "description": "How often do you shop?",
          "is_required": true,
          "options": ["Daily", "Weekly", "Monthly", "Rarely"],
          "order": 2
        },
        {
          "name": "Budget per month",
          "type": "Number",
          "description": "Average monthly shopping budget",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 9: Website Usability Survey

```json
{
  "survey_name": "Website Usability Feedback",
  "description": "Help us improve our website",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Website Experience",
      "tagline": "Your experience matters",
      "order": 1,
      "survey_fields": [
        {
          "name": "Ease of navigation",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "Design appeal",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Page load speed",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 3
        },
        {
          "name": "What features do you use most?",
          "type": "Checkboxes",
          "description": "Select all that apply",
          "is_required": false,
          "options": ["Search", "Navigation", "User Account", "Checkout", "Help Section"],
          "order": 4
        },
        {
          "name": "Suggestions for improvement",
          "type": "Paragraph",
          "description": "How can we improve?",
          "is_required": false,
          "options": null,
          "order": 5
        }
      ]
    }
  ]
}
```

### Example 10: Training Program Assessment

```json
{
  "survey_name": "Training Program Evaluation",
  "description": "Evaluate our training program",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Program Details",
      "tagline": "Program information",
      "order": 1,
      "survey_fields": [
        {
          "name": "Program Name",
          "type": "Short Answer",
          "description": "Which program did you attend?",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Training date",
          "type": "Date",
          "description": "When was the training?",
          "is_required": true,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Evaluation",
      "tagline": "Your feedback",
      "order": 2,
      "survey_fields": [
        {
          "name": "Content quality",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "Trainer effectiveness",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Would you recommend this program?",
          "type": "Choice",
          "description": "Select your answer",
          "is_required": true,
          "options": ["Yes", "No", "Maybe"],
          "order": 3
        },
        {
          "name": "What did you learn?",
          "type": "Paragraph",
          "description": "Share key takeaways",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    }
  ]
}
```

### Example 11: Subscription Service Feedback

```json
{
  "survey_name": "Subscription Service Survey",
  "description": "How is your subscription experience?",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Subscription Info",
      "tagline": "Your subscription details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Subscription plan",
          "type": "Multiple Choice",
          "description": "Which plan are you on?",
          "is_required": true,
          "options": ["Basic", "Pro", "Enterprise", "Free"],
          "order": 1
        },
        {
          "name": "Subscription start date",
          "type": "Date",
          "description": "When did you subscribe?",
          "is_required": true,
          "options": null,
          "order": 2
        }
      ]
    },
    {
      "step": "Satisfaction",
      "tagline": "Rate your experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "Value for money",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "Feature satisfaction",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Features you want",
          "type": "Paragraph",
          "description": "What features would you like to see?",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 12: Travel Experience Survey

```json
{
  "survey_name": "Travel Experience Feedback",
  "description": "Share your travel experience",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Trip Details",
      "tagline": "Your trip information",
      "order": 1,
      "survey_fields": [
        {
          "name": "Destination",
          "type": "Short Answer",
          "description": "Where did you travel?",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Travel dates",
          "type": "Date",
          "description": "Start date of trip",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Travel type",
          "type": "Multiple Choice",
          "description": "What type of travel?",
          "is_required": true,
          "options": ["Business", "Leisure", "Family", "Solo", "Group"],
          "order": 3
        }
      ]
    },
    {
      "step": "Experience Rating",
      "tagline": "Rate your experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "Overall experience",
          "type": "Rating Scale",
          "description": "Rate from 1 to 10",
          "is_required": true,
          "options": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
          "order": 1
        },
        {
          "name": "Accommodation rating",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "What did you enjoy most?",
          "type": "Paragraph",
          "description": "Share your favorite moments",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 13: Support Ticket Feedback

```json
{
  "survey_name": "Customer Support Feedback",
  "description": "How was your support experience?",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Ticket Information",
      "tagline": "Support ticket details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Ticket ID",
          "type": "Short Answer",
          "description": "Enter your ticket number",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Issue category",
          "type": "Dropdown",
          "description": "What was the issue?",
          "is_required": true,
          "options": ["Technical", "Billing", "Account", "Feature Request", "Other"],
          "order": 2
        }
      ]
    },
    {
      "step": "Support Rating",
      "tagline": "Rate your support experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "Response time",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "Solution effectiveness",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Support agent helpfulness",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 3
        },
        {
          "name": "Additional comments",
          "type": "Paragraph",
          "description": "Any other feedback?",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    }
  ]
}
```

### Example 14: School Parent Survey

```json
{
  "survey_name": "Parent-Teacher Communication Survey",
  "description": "Help us improve communication",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Parent Information",
      "tagline": "Your details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Parent Name",
          "type": "Short Answer",
          "description": "Enter your name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Child's grade",
          "type": "Dropdown",
          "description": "Select grade",
          "is_required": true,
          "options": ["1st Grade", "2nd Grade", "3rd Grade", "4th Grade", "5th Grade"],
          "order": 2
        },
        {
          "name": "Email",
          "type": "Email",
          "description": "Your email address",
          "is_required": true,
          "options": null,
          "order": 3
        }
      ]
    },
    {
      "step": "Feedback",
      "tagline": "Your opinions",
      "order": 2,
      "survey_fields": [
        {
          "name": "Communication frequency",
          "type": "Choice",
          "description": "How often do you want updates?",
          "is_required": true,
          "options": ["Daily", "Weekly", "Monthly", "As needed"],
          "order": 1
        },
        {
          "name": "Preferred communication method",
          "type": "Checkboxes",
          "description": "Select all that apply",
          "is_required": true,
          "options": ["Email", "SMS", "App", "Phone call", "In-person"],
          "order": 2
        },
        {
          "name": "Suggestions",
          "type": "Paragraph",
          "description": "How can we improve?",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 15: App Download Survey

```json
{
  "survey_name": "Mobile App Download Survey",
  "description": "Why did you download our app?",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Download Information",
      "tagline": "Tell us about your download",
      "order": 1,
      "survey_fields": [
        {
          "name": "App name",
          "type": "Short Answer",
          "description": "Which app did you download?",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Download date",
          "type": "Date",
          "description": "When did you download?",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "How did you find us?",
          "type": "Multiple Choice",
          "description": "Select one option",
          "is_required": true,
          "options": ["App Store", "Google Play", "Friend referral", "Advertisement", "Website", "Other"],
          "order": 3
        }
      ]
    },
    {
      "step": "Experience",
      "tagline": "Your app experience",
      "order": 2,
      "survey_fields": [
        {
          "name": "App usability",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 1
        },
        {
          "name": "App features",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Would you recommend this app?",
          "type": "Choice",
          "description": "Select your answer",
          "is_required": true,
          "options": ["Yes", "No", "Maybe"],
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 16: Volunteer Registration

```json
{
  "survey_name": "Community Volunteer Registration",
  "description": "Join us as a volunteer",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Personal Information",
      "tagline": "Your details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Full Name",
          "type": "Short Answer",
          "description": "Enter your name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Email",
          "type": "Email",
          "description": "Your email",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Phone",
          "type": "Number",
          "description": "Contact number",
          "is_required": true,
          "options": null,
          "order": 3
        }
      ]
    },
    {
      "step": "Volunteer Preferences",
      "tagline": "How would you like to help?",
      "order": 2,
      "survey_fields": [
        {
          "name": "Areas of interest",
          "type": "Checkboxes",
          "description": "Select all that interest you",
          "is_required": true,
          "options": ["Education", "Environment", "Healthcare", "Community Service", "Event Planning"],
          "order": 1
        },
        {
          "name": "Availability",
          "type": "Multiple Choice",
          "description": "When are you available?",
          "is_required": true,
          "options": ["Weekdays", "Weekends", "Both", "Flexible"],
          "order": 2
        },
        {
          "name": "Why do you want to volunteer?",
          "type": "Paragraph",
          "description": "Tell us your motivation",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 17: Membership Renewal

```json
{
  "survey_name": "Gym Membership Renewal Survey",
  "description": "Renew your membership",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Member Information",
      "tagline": "Your membership details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Member ID",
          "type": "Short Answer",
          "description": "Enter your member ID",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Current plan",
          "type": "Dropdown",
          "description": "Select your current plan",
          "is_required": true,
          "options": ["Basic", "Standard", "Premium", "VIP"],
          "order": 2
        }
      ]
    },
    {
      "step": "Renewal Preferences",
      "tagline": "Renewal options",
      "order": 2,
      "survey_fields": [
        {
          "name": "Renewal duration",
          "type": "Multiple Choice",
          "description": "How long would you like to renew?",
          "is_required": true,
          "options": ["1 Month", "3 Months", "6 Months", "12 Months"],
          "order": 1
        },
        {
          "name": "Overall satisfaction",
          "type": "Rating Scale",
          "description": "Rate from 1 to 5",
          "is_required": true,
          "options": [1, 2, 3, 4, 5],
          "order": 2
        },
        {
          "name": "Feedback",
          "type": "Paragraph",
          "description": "Any comments or suggestions?",
          "is_required": false,
          "options": null,
          "order": 3
        }
      ]
    }
  ]
}
```

### Example 18: Newsletter Subscription

```json
{
  "survey_name": "Newsletter Subscription Form",
  "description": "Subscribe to our newsletter",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Subscription Details",
      "tagline": "Join our community",
      "order": 1,
      "survey_fields": [
        {
          "name": "Full Name",
          "type": "Short Answer",
          "description": "Enter your name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Email",
          "type": "Email",
          "description": "Your email address",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Newsletter topics",
          "type": "Checkboxes",
          "description": "What topics interest you?",
          "is_required": true,
          "options": ["Technology", "Business", "Health", "Education", "Entertainment", "Sports"],
          "order": 3
        },
        {
          "name": "Frequency",
          "type": "Choice",
          "description": "How often would you like updates?",
          "is_required": true,
          "options": ["Daily", "Weekly", "Monthly"],
          "order": 4
        }
      ]
    }
  ]
}
```

### Example 19: Complaint Form

```json
{
  "survey_name": "Customer Complaint Form",
  "description": "We're here to help",
  "status": "Published",
  "survey_steps": [
    {
      "step": "Customer Information",
      "tagline": "Your details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Full Name",
          "type": "Short Answer",
          "description": "Enter your name",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Email",
          "type": "Email",
          "description": "Your email",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Phone",
          "type": "Number",
          "description": "Contact number",
          "is_required": true,
          "options": null,
          "order": 3
        },
        {
          "name": "Order/Transaction ID",
          "type": "Short Answer",
          "description": "If applicable",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    },
    {
      "step": "Complaint Details",
      "tagline": "Tell us about your issue",
      "order": 2,
      "survey_fields": [
        {
          "name": "Complaint category",
          "type": "Dropdown",
          "description": "What is your complaint about?",
          "is_required": true,
          "options": ["Product Quality", "Service", "Delivery", "Billing", "Other"],
          "order": 1
        },
        {
          "name": "Issue description",
          "type": "Paragraph",
          "description": "Please describe the issue in detail",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Date of issue",
          "type": "Date",
          "description": "When did this occur?",
          "is_required": true,
          "options": null,
          "order": 3
        },
        {
          "name": "Expected resolution",
          "type": "Paragraph",
          "description": "How would you like us to resolve this?",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    }
  ]
}
```

### Example 20: Research Study Participation

```json
{
  "survey_name": "Medical Research Study Participation",
  "description": "Participate in our research study",
  "status": "Draft",
  "survey_steps": [
    {
      "step": "Participant Information",
      "tagline": "Your details",
      "order": 1,
      "survey_fields": [
        {
          "name": "Participant ID",
          "type": "Short Answer",
          "description": "Enter your participant ID",
          "is_required": true,
          "options": null,
          "order": 1
        },
        {
          "name": "Age",
          "type": "Number",
          "description": "Enter your age",
          "is_required": true,
          "options": null,
          "order": 2
        },
        {
          "name": "Gender",
          "type": "Multiple Choice",
          "description": "Select your gender",
          "is_required": true,
          "options": ["Male", "Female", "Other", "Prefer not to say"],
          "order": 3
        }
      ]
    },
    {
      "step": "Health Information",
      "tagline": "Medical history",
      "order": 2,
      "survey_fields": [
        {
          "name": "Any medical conditions?",
          "type": "Choice",
          "description": "Do you have any medical conditions?",
          "is_required": true,
          "options": ["Yes", "No", "Prefer not to say"],
          "order": 1
        },
        {
          "name": "Current medications",
          "type": "Paragraph",
          "description": "List any current medications",
          "is_required": false,
          "options": null,
          "order": 2
        },
        {
          "name": "Consent to participate",
          "type": "Choice",
          "description": "Do you consent to participate?",
          "is_required": true,
          "options": ["Yes", "No"],
          "order": 3
        },
        {
          "name": "Additional notes",
          "type": "Paragraph",
          "description": "Any additional information?",
          "is_required": false,
          "options": null,
          "order": 4
        }
      ]
    }
  ]
}
```

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

