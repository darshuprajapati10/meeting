# Survey Step Save API Documentation

## Overview
This API endpoint allows you to create or update a survey step with its associated survey fields (questions). The same endpoint handles both operations - if an `id` is provided, it updates the existing survey step; otherwise, it creates a new one. When updating, if `survey_fields` are provided, all existing fields will be deleted and replaced with the new ones.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey-step/save`  
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

### Creating a New Survey Step

When creating a survey step, **do not** include the `id` field:

```json
{
  "survey_id": 1,
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
}
```

### Updating an Existing Survey Step

When updating a survey step, **include** the `id` field. Note: If `survey_fields` are provided, all existing fields will be deleted and replaced:

```json
{
  "id": 5,
  "survey_id": 1,
  "step": "Updated Step 1",
  "tagline": "Updated introduction",
  "order": 1,
  "survey_fields": [
    {
      "name": "Updated Question",
      "type": "Short Answer",
      "description": "Updated description",
      "is_required": true,
      "options": null,
      "order": 1
    }
  ]
}
```

### Creating Step Without Fields

You can create a survey step without fields (fields can be added later):

```json
{
  "survey_id": 1,
  "step": "Step 1",
  "tagline": "Introduction",
  "order": 1
}
```

---

## Field Specifications

### Survey Step Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | Survey Step ID (only for updates) | Must exist in survey_steps table |
| `survey_id` | integer | **Yes** | Survey ID that this step belongs to | Must exist in surveys table |
| `step` | string | **Yes** | Step title/name | Max 255 characters |
| `tagline` | string | **Yes** | Step description/tagline | Max 255 characters |
| `order` | integer | **Yes** | Step order/sequence within the survey | Must be an integer |
| `survey_fields` | array | No | Array of survey fields/questions | Optional, can be empty or omitted |

### Survey Field Level

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `name` | string | **Yes** | Field/question title | Max 255 characters |
| `type` | string | **Yes** | Field type | Max 255 characters (e.g., "Short Answer", "Paragraph", "Choice") |
| `description` | string | **Yes** | Field description/help text | Required for each field |
| `is_required` | boolean | **Yes** | Whether field is required | Must be true or false |
| `options` | array | No | Options array (for choice-based fields) | Can be null or array |
| `order` | integer | No | Field order/sequence within the step | Must be >= 0 if provided |

### Important Notes on `survey_fields`

The `survey_fields` field is flexible and will be normalized automatically:
- ✅ **Valid:** `[{"name": "...", "type": "...", ...}]` (array)
- ✅ **Valid:** `null` or omitted (step created without fields)
- ✅ **Valid:** `[]` (empty array, step created without fields)
- ✅ **Valid:** JSON string like `'[{"name":"..."}]'` (will be decoded)
- ✅ **Valid:** Single object `{"name": "..."}` (will be wrapped in array)

**Recommendation:** Always send `survey_fields` as an array of objects.

---

## Response Examples

### Success - Survey Step Created (201)

```json
{
  "data": {
    "id": 5,
    "step": "Step 1",
    "tagline": "Introduction and basic information",
    "survey_fields": [
      {
        "id": 10,
        "name": "What is your name?",
        "type": "Short Answer",
        "description": "Enter your full name",
        "is_required": true,
        "options": []
      },
      {
        "id": 11,
        "name": "Feedback",
        "type": "Paragraph",
        "description": "Please provide your feedback",
        "is_required": false,
        "options": []
      }
    ]
  },
  "message": "Survey step created successfully."
}
```

### Success - Survey Step Updated (200)

```json
{
  "data": {
    "id": 5,
    "step": "Updated Step 1",
    "tagline": "Updated introduction",
    "survey_fields": [
      {
        "id": 12,
        "name": "Updated Question",
        "type": "Short Answer",
        "description": "Updated description",
        "is_required": true,
        "options": []
      }
    ]
  },
  "message": "Survey step updated successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Survey step data (SurveyStepResource) |
| `data.id` | integer | Survey Step ID |
| `data.step` | string | Step title/name |
| `data.tagline` | string\|null | Step description/tagline |
| `data.survey_fields` | array | Array of survey fields/questions in this step |
| `message` | string | Success message |

### Survey Field Structure in Response

Each field in `survey_fields` contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Field ID |
| `name` | string | Field/question title |
| `type` | string | Field type (Short Answer, Paragraph, Choice, etc.) |
| `description` | string\|null | Field description/help text |
| `is_required` | boolean | Whether field is required |
| `options` | array | Options array (for choice-based fields) |

---

## Validation Rules

### Survey Step Validation

- `id`: Optional, must be integer, must exist in survey_steps table
- `survey_id`: **Required**, must be integer, must exist in surveys table
- `step`: **Required**, must be string, max 255 characters
- `tagline`: **Required**, must be string, max 255 characters
- `order`: **Required**, must be integer

### Survey Fields Validation

When `survey_fields` is provided (as array), each field must have:
- `name`: **Required**, must be string, max 255 characters
- `type`: **Required**, must be string, max 255 characters
- `description`: **Required**, must be string
- `is_required`: **Required**, must be boolean
- `options`: Optional, must be array if provided
- `order`: Optional, must be integer >= 0 if provided

---

## Error Responses

### Validation Error (422)

```json
{
  "message": "Validation failed",
  "errors": {
    "survey_id": [
      "The survey id field is required."
    ],
    "step": [
      "The step field is required."
    ],
    "tagline": [
      "The tagline field is required."
    ],
    "order": [
      "The order field is required."
    ],
    "survey_fields.0.name": [
      "The name field is required for each survey field."
    ],
    "survey_fields.0.type": [
      "The type field is required for each survey field."
    ],
    "survey_fields.0.description": [
      "The description field is required for each survey field."
    ],
    "survey_fields.0.is_required": [
      "The is_required field is required for each survey field."
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

### Survey Step Not Found (404)

When updating with invalid step ID:

```json
{
  "message": "Survey step not found or you do not have permission to update it."
}
```

### No Organization (404)

```json
{
  "message": "No organization found. Please create an organization first."
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
  "message": "Error saving survey step: [error details]"
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Create or update survey step
async function saveSurveyStep(stepData, token) {
  try {
    const response = await fetch('http://your-api-url/api/survey-step/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(stepData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to save survey step');
    }

    return data.data; // Returns survey step object
  } catch (error) {
    console.error('Error saving survey step:', error);
    throw error;
  }
}

// Create new step
const newStep = {
  survey_id: 1,
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
};

const createdStep = await saveSurveyStep(newStep, token);
console.log('Created step:', createdStep);

// Update existing step
const updatedStep = {
  id: 5,
  survey_id: 1,
  step: "Updated Step 1",
  tagline: "Updated introduction",
  order: 1,
  survey_fields: [
    {
      name: "Updated Question",
      type: "Short Answer",
      description: "Updated description",
      is_required: true,
      options: null,
      order: 1
    }
  ]
};

const savedStep = await saveSurveyStep(updatedStep, token);
console.log('Updated step:', savedStep);
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

// Save survey step
async function saveSurveyStep(stepData) {
  try {
    const response = await apiClient.post('/survey-step/save', stepData);
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
const stepData = {
  survey_id: 1,
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
};

const step = await saveSurveyStep(stepData);
console.log('Saved step:', step);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface SurveyField {
  name: string;
  type: string;
  description: string;
  is_required: boolean;
  options?: (number | string)[] | null;
  order?: number;
}

interface SurveyStepData {
  id?: number;
  survey_id: number;
  step: string;
  tagline: string;
  order: number;
  survey_fields?: SurveyField[];
}

interface SurveyStep {
  id: number;
  step: string;
  tagline: string | null;
  survey_fields: Array<{
    id: number;
    name: string;
    type: string;
    description: string | null;
    is_required: boolean;
    options: (number | string)[];
  }>;
}

export function useSurveyStepSave(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const saveStep = async (stepData: SurveyStepData): Promise<SurveyStep> => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<{ data: SurveyStep; message: string }>(
        '/api/survey-step/save',
        stepData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
        }
      );

      return response.data.data;
    } catch (err: any) {
      const errorData = err.response?.data || err.message;
      setError(errorData);
      throw errorData;
    } finally {
      setLoading(false);
    }
  };

  return { saveStep, loading, error };
}

// Usage in component
function SurveyStepForm({ surveyId, token }: { surveyId: number; token: string }) {
  const { saveStep, loading, error } = useSurveyStepSave(token);
  const [formData, setFormData] = useState<SurveyStepData>({
    survey_id: surveyId,
    step: '',
    tagline: '',
    order: 1,
    survey_fields: []
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const savedStep = await saveStep(formData);
      console.log('Step saved:', savedStep);
      // Handle success (e.g., show success message, redirect, etc.)
    } catch (err) {
      console.error('Error saving step:', err);
      // Handle error (e.g., show error message)
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        value={formData.step}
        onChange={(e) => setFormData({...formData, step: e.target.value})}
        placeholder="Step name"
        required
      />
      <textarea
        value={formData.tagline}
        onChange={(e) => setFormData({...formData, tagline: e.target.value})}
        placeholder="Step description"
        required
      />
      <input
        type="number"
        value={formData.order}
        onChange={(e) => setFormData({...formData, order: parseInt(e.target.value)})}
        min="0"
        required
      />
      {/* Add survey fields management UI here */}
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Step'}
      </button>
      {error && <div>Error: {error.message}</div>}
    </form>
  );
}
```

---

## cURL Example

### Create New Survey Step

```bash
curl -X POST "http://your-api-url/api/survey-step/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "survey_id": 1,
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
      }
    ]
  }'
```

### Update Existing Survey Step

```bash
curl -X POST "http://your-api-url/api/survey-step/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 5,
    "survey_id": 1,
    "step": "Updated Step 1",
    "tagline": "Updated introduction",
    "order": 1,
    "survey_fields": [
      {
        "name": "Updated Question",
        "type": "Short Answer",
        "description": "Updated description",
        "is_required": true,
        "options": null,
        "order": 1
      }
    ]
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `201` | Success - Survey step created successfully |
| `200` | Success - Survey step updated successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Survey not found, survey step not found, or no organization found |
| `422` | Validation error (missing or invalid parameters) |
| `500` | Server error |

---

## Important Notes

### 1. Survey Fields Replacement
- When updating a survey step, if `survey_fields` is provided, **all existing fields will be deleted** and replaced with the new ones
- If you want to keep existing fields, you must include them in the `survey_fields` array
- If `survey_fields` is not provided during update, existing fields will remain unchanged

### 2. Organization Scope
- The survey must belong to the authenticated user's organization
- You cannot create steps for surveys in other organizations
- The system automatically verifies organization ownership

### 3. Survey Fields Normalization
- `survey_fields` can be provided as an array, JSON string, or single object
- The system automatically normalizes the input
- Empty arrays or null values are allowed (step created without fields)

### 4. Field Ordering
- The `order` field in survey_fields is optional
- If not provided, fields will be created in the order they appear in the array
- Use the `order` field to explicitly set field sequence

### 5. Transaction Safety
- All operations (step creation/update and field creation) are wrapped in a database transaction
- If any part fails, all changes are rolled back
- This ensures data consistency

### 6. ID Validation
- When updating, the `id` must exist in the survey_steps table
- The step must belong to the specified `survey_id`
- The survey must belong to your organization

---

## Use Cases

### 1. Create Step with Fields
Create a new survey step with multiple questions:

```javascript
const stepData = {
  survey_id: 1,
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
    },
    {
      name: "Feedback",
      type: "Paragraph",
      description: "Please provide your feedback",
      is_required: false,
      options: null,
      order: 2
    }
  ]
};

const step = await saveSurveyStep(stepData, token);
```

### 2. Create Step Without Fields
Create an empty step and add fields later:

```javascript
const stepData = {
  survey_id: 1,
  step: "Step 1",
  tagline: "Introduction",
  order: 1
};

const step = await saveSurveyStep(stepData, token);
```

### 3. Update Step and Replace Fields
Update step details and replace all fields:

```javascript
const stepData = {
  id: 5,
  survey_id: 1,
  step: "Updated Step 1",
  tagline: "Updated introduction",
  order: 1,
  survey_fields: [
    {
      name: "New Question",
      type: "Short Answer",
      description: "New description",
      is_required: true,
      options: null,
      order: 1
    }
  ]
};

const step = await saveSurveyStep(stepData, token);
```

### 4. Update Step Without Changing Fields
Update only step details, keep existing fields:

```javascript
const stepData = {
  id: 5,
  survey_id: 1,
  step: "Updated Step 1",
  tagline: "Updated introduction",
  order: 1
  // survey_fields not provided - existing fields remain
};

const step = await saveSurveyStep(stepData, token);
```

---

## Best Practices

1. **Always Include Required Fields**: Ensure all required fields (`survey_id`, `step`, `tagline`, `order`) are provided
2. **Handle Validation Errors**: Check for validation errors and display them to users
3. **Transaction Safety**: Be aware that field updates replace all existing fields
4. **Field Ordering**: Use the `order` field to maintain proper field sequence
5. **Error Handling**: Always handle 404, 422, and 500 errors gracefully
6. **Loading States**: Show loading indicators during save operations
7. **Success Feedback**: Provide user feedback when step is saved successfully

---

## Related Endpoints

- **Show Survey Step**: `/api/survey-step/show` - Get survey step details
- **List Survey Steps**: `/api/survey-step/index` - Get paginated list of survey steps
- **Delete Survey Step**: `/api/survey-step/delete` - Delete survey step
- **Show Survey**: `/api/survey/show` - Get survey details including all steps
- **Save Survey**: `/api/survey/save` - Save entire survey with steps and fields

---

## Troubleshooting

### Issue: "Survey step not found or you do not have permission to update it"

**Solution:** Ensure that:
- The `id` exists in the database
- The step belongs to the specified `survey_id`
- The survey belongs to your organization
- You're using the correct authentication token

### Issue: "No organization found"

**Solution:** The authenticated user must be associated with an organization. Create an organization first or ensure your user account is properly linked to an organization.

### Issue: Validation errors for required fields

**Solution:** Ensure all required fields are provided:
- `survey_id` must be present and valid
- `step` must be present and not empty
- `tagline` must be present and not empty
- `order` must be present and be an integer
- For each field in `survey_fields`: `name`, `type`, `description`, and `is_required` are required

### Issue: Fields are deleted when updating

**Solution:** This is expected behavior. When you provide `survey_fields` during update, all existing fields are replaced. To keep existing fields, include them in the `survey_fields` array.

---

## Version History

- **v1.0** (2025-01-15) - Initial release of Survey Step Save API

