# Survey Index API Documentation

## Overview
This API endpoint retrieves a paginated list of all surveys from the user's organization. It supports pagination, search, and filtering by survey status. Perfect for displaying surveys in a table or list view with pagination controls.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/index`  
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

### Basic Request (No Parameters)

```json
{}
```

### Request with Pagination

```json
{
  "page": 1,
  "per_page": 15
}
```

### Request with Search

```json
{
  "page": 1,
  "per_page": 15,
  "search": "Customer"
}
```

### Request with Status Filter

```json
{
  "page": 1,
  "per_page": 15,
  "status": "Draft"
}
```

### Request with All Parameters

```json
{
  "page": 2,
  "per_page": 20,
  "search": "Customer",
  "status": "Published"
}
```

---

## Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number (starts from 1) |
| `per_page` | integer | No | 15 | Number of surveys per page (1-100) |
| `search` | string | No | - | Search term (searches in survey_name and description) |
| `status` | string | No | - | Filter by survey status (Draft, Published, Archived) |

---

## Response Format

### Success Response (200)

```json
{
  "data": [
    {
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
    {
      "id": 3,
      "organization_id": 1,
      "survey_name": "Employee Feedback",
      "description": "Annual employee feedback survey",
      "status": "Published",
      "survey_steps": [
        {
          "id": 5,
          "survey_id": 3,
          "step": "Introduction",
          "tagline": "Getting Started",
          "order": 1,
          "survey_fields": [
            {
              "id": 7,
              "organization_id": 1,
              "survey_id": 3,
              "name": "Employee ID",
              "type": "Short Answer",
              "description": "Enter your employee ID",
              "is_required": true,
              "options": []
            }
          ]
        }
      ],
      "created_at": "2025-10-12T10:15:00.000000Z",
      "updated_at": "2025-10-12T10:15:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 2,
    "total": 2
  },
  "message": "Surveys retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | array | Array of survey objects (SurveyResource) |
| `meta` | object | Pagination metadata |
| `meta.current_page` | integer | Current page number |
| `meta.from` | integer\|null | First item number on current page (null if empty) |
| `meta.last_page` | integer | Last page number (total pages) |
| `meta.per_page` | integer | Number of items per page |
| `meta.to` | integer\|null | Last item number on current page (null if empty) |
| `meta.total` | integer | Total number of surveys |
| `message` | string | Success message |

### Survey Object Structure

Each survey object in the `data` array contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Survey ID |
| `organization_id` | integer | Organization ID |
| `survey_name` | string | Survey name/title |
| `description` | string\|null | Survey description |
| `status` | string | Survey status (Draft, Published, Archived) |
| `survey_steps` | array | Array of survey steps |
| `created_at` | string | Creation timestamp (ISO 8601) |
| `updated_at` | string | Last update timestamp (ISO 8601) |

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

## Pagination Example (3 Pages)

If you have 35 surveys with 15 per page, you'll have 3 pages:

**Page 1:** Surveys 1-15
```json
{
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 35
  }
}
```

**Page 2:** Surveys 16-30
```json
{
  "meta": {
    "current_page": 2,
    "from": 16,
    "last_page": 3,
    "per_page": 15,
    "to": 30,
    "total": 35
  }
}
```

**Page 3:** Surveys 31-35
```json
{
  "meta": {
    "current_page": 3,
    "from": 31,
    "last_page": 3,
    "per_page": 15,
    "to": 35,
    "total": 35
  }
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get surveys list
async function getSurveys(params, token) {
  try {
    const response = await fetch('http://your-api-url/api/survey/index', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        page: params.page || 1,
        per_page: params.perPage || 15,
        ...(params.search && { search: params.search }),
        ...(params.status && { status: params.status })
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve surveys');
    }

    return data; // Returns { data: [...], meta: {...}, message: "..." }
  } catch (error) {
    console.error('Error fetching surveys:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');

// Basic request
const surveys = await getSurveys({}, token);

// With pagination
const page2 = await getSurveys({ page: 2, perPage: 20 }, token);

// With search
const searchResults = await getSurveys({ search: 'Customer' }, token);

// With status filter
const drafts = await getSurveys({ status: 'Draft' }, token);

// All parameters
const all = await getSurveys({ 
  page: 1, 
  perPage: 15, 
  search: 'Customer',
  status: 'Published'
}, token);
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

// Get surveys list
async function getSurveys(page = 1, perPage = 15, search = '', status = '') {
  try {
    const response = await apiClient.post('/survey/index', {
      page,
      per_page: perPage,
      ...(search && { search }),
      ...(status && { status })
    });
    
    return response.data; // Returns { data: [...], meta: {...}, message: "..." }
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await getSurveys(1, 15, 'Customer', 'Published');
console.log('Surveys:', result.data);
console.log('Pagination:', result.meta);
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

interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
}

interface SurveysResponse {
  data: Survey[];
  meta: PaginationMeta;
  message: string;
}

export function useSurveys(
  token: string,
  page: number = 1,
  perPage: number = 15,
  search: string = '',
  status: string = ''
) {
  const [surveys, setSurveys] = useState<Survey[]>([]);
  const [pagination, setPagination] = useState<PaginationMeta | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchSurveys = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<SurveysResponse>(
          '/api/survey/index',
          {
            page,
            per_page: perPage,
            ...(search && { search }),
            ...(status && { status })
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setSurveys(response.data.data);
        setPagination(response.data.meta);
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
  }, [token, page, perPage, search, status]);

  return { surveys, pagination, loading, error };
}

// Usage in component
function SurveysList() {
  const token = localStorage.getItem('auth_token') || '';
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  
  const { surveys, pagination, loading, error } = useSurveys(
    token, 
    page, 
    15, 
    search, 
    status
  );

  if (loading) return <div>Loading surveys...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div>
      <input
        type="text"
        placeholder="Search surveys..."
        value={search}
        onChange={(e) => {
          setSearch(e.target.value);
          setPage(1);
        }}
      />
      <select
        value={status}
        onChange={(e) => {
          setStatus(e.target.value);
          setPage(1);
        }}
      >
        <option value="">All Status</option>
        <option value="Draft">Draft</option>
        <option value="Published">Published</option>
        <option value="Archived">Archived</option>
      </select>

      {surveys.map(survey => (
        <div key={survey.id}>
          <h3>{survey.survey_name}</h3>
          <p>{survey.description}</p>
          <span>Status: {survey.status}</span>
        </div>
      ))}

      {pagination && (
        <div>
          <button
            disabled={page === 1}
            onClick={() => setPage(page - 1)}
          >
            Previous
          </button>
          <span>Page {pagination.current_page} of {pagination.last_page}</span>
          <button
            disabled={page === pagination.last_page}
            onClick={() => setPage(page + 1)}
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState, useEffect } from 'react';

function SurveysTable({ token }) {
  const [surveys, setSurveys] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    fetchSurveys();
  }, [page, perPage, search, status]);

  const fetchSurveys = async () => {
    setLoading(true);
    
    try {
      const response = await fetch('/api/survey/index', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          page,
          per_page: perPage,
          ...(search && { search }),
          ...(status && { status })
        })
      });

      const data = await response.json();
      
      if (response.ok) {
        setSurveys(data.data);
        setPagination(data.meta);
      } else {
        console.error('Error:', data.message);
      }
    } catch (error) {
      console.error('Network error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= pagination.last_page) {
      setPage(newPage);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  const handleSearchChange = (value) => {
    setSearch(value);
    setPage(1); // Reset to first page when searching
  };

  const handleStatusChange = (value) => {
    setStatus(value);
    setPage(1); // Reset to first page when filtering
  };

  if (loading && surveys.length === 0) {
    return <div>Loading surveys...</div>;
  }

  return (
    <div>
      {/* Search and Filter */}
      <div style={{ marginBottom: '20px' }}>
        <input
          type="text"
          placeholder="Search surveys by name or description..."
          value={search}
          onChange={(e) => handleSearchChange(e.target.value)}
          style={{
            padding: '8px',
            marginRight: '10px',
            width: '300px',
            borderRadius: '4px',
            border: '1px solid #ddd'
          }}
        />
        <select
          value={status}
          onChange={(e) => handleStatusChange(e.target.value)}
          style={{
            padding: '8px',
            marginRight: '10px',
            borderRadius: '4px',
            border: '1px solid #ddd'
          }}
        >
          <option value="">All Status</option>
          <option value="Draft">Draft</option>
          <option value="Published">Published</option>
          <option value="Archived">Archived</option>
        </select>
        <select
          value={perPage}
          onChange={(e) => {
            setPerPage(Number(e.target.value));
            setPage(1);
          }}
          style={{
            padding: '8px',
            borderRadius: '4px',
            border: '1px solid #ddd'
          }}
        >
          <option value="10">10 per page</option>
          <option value="15">15 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
          <option value="100">100 per page</option>
        </select>
      </div>

      {/* Surveys Table */}
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr style={{ backgroundColor: '#f5f5f5' }}>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>ID</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Survey Name</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Description</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Status</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Steps</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Created</th>
          </tr>
        </thead>
        <tbody>
          {surveys.length === 0 ? (
            <tr>
              <td colSpan="6" style={{ padding: '20px', textAlign: 'center' }}>
                No surveys found
              </td>
            </tr>
          ) : (
            surveys.map(survey => (
              <tr key={survey.id}>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>{survey.id}</td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>{survey.survey_name}</td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                  {survey.description || '-'}
                </td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
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
                </td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                  {survey.survey_steps.length} step(s)
                </td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                  {new Date(survey.created_at).toLocaleDateString()}
                </td>
              </tr>
            ))
          )}
        </tbody>
      </table>

      {/* Pagination */}
      {pagination && pagination.last_page > 1 && (
        <div style={{ marginTop: '20px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '10px' }}>
          <button
            onClick={() => handlePageChange(page - 1)}
            disabled={page === 1}
            style={{
              padding: '8px 16px',
              borderRadius: '4px',
              border: '1px solid #ddd',
              cursor: page === 1 ? 'not-allowed' : 'pointer',
              backgroundColor: page === 1 ? '#f5f5f5' : 'white'
            }}
          >
            Previous
          </button>
          <span>
            Page {pagination.current_page} of {pagination.last_page}
            {pagination.total > 0 && ` (${pagination.total} total surveys)`}
          </span>
          <button
            onClick={() => handlePageChange(page + 1)}
            disabled={page === pagination.last_page}
            style={{
              padding: '8px 16px',
              borderRadius: '4px',
              border: '1px solid #ddd',
              cursor: page === pagination.last_page ? 'not-allowed' : 'pointer',
              backgroundColor: page === pagination.last_page ? '#f5f5f5' : 'white'
            }}
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
}

// Usage
function App() {
  const token = localStorage.getItem('auth_token');
  return <SurveysTable token={token} />;
}
```

---

## cURL Examples

### Basic Request

```bash
curl -X POST "http://your-api-url/api/survey/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### Request with Pagination

```bash
curl -X POST "http://your-api-url/api/survey/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 2,
    "per_page": 20
  }'
```

### Request with Search

```bash
curl -X POST "http://your-api-url/api/survey/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "search": "Customer"
  }'
```

### Request with Status Filter

```bash
curl -X POST "http://your-api-url/api/survey/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "status": "Draft"
  }'
```

### Request with All Parameters

```bash
curl -X POST "http://your-api-url/api/survey/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "search": "Customer",
    "status": "Published"
  }'
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

### No Organization (200 with empty data)

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "from": null,
    "last_page": 1,
    "per_page": 15,
    "to": null,
    "total": 0
  },
  "message": "No organization found. Please create a survey first."
}
```

---

## Important Notes

### 1. Pagination
- Default: 15 surveys per page
- Minimum: 1 per page
- Maximum: 100 per page
- Page numbers start from 1 (not 0)

### 2. Search
- Searches in: `survey_name` and `description`
- Case-insensitive partial match
- Example: searching "customer" will find "Customer Satisfaction Survey" and "customer feedback"

### 3. Status Filter
- Valid values: `Draft`, `Published`, `Archived`
- Case-sensitive (must match exactly)
- If an invalid status is provided, it will return empty results

### 4. Ordering
- Surveys are ordered by `created_at` in descending order (newest first)
- Most recently created surveys appear first

### 5. Response Includes Full Structure
- Each survey in the response includes all `survey_steps` and `survey_fields`
- This means the response can be large if surveys have many steps and fields
- Consider implementing lazy loading in the UI for better performance

### 6. Organization Scope
- Only surveys from the authenticated user's organization are returned
- If the user doesn't have an organization, an empty response is returned

---

## Best Practices

1. **Debounce Search:** Implement debouncing when users type in the search field to avoid excessive API calls
2. **Loading States:** Show loading indicators while fetching surveys
3. **Empty States:** Display helpful messages when no surveys are found
4. **Pagination UI:** Provide clear navigation controls (Previous/Next buttons, page numbers)
5. **Status Badges:** Use visual indicators (colors, badges) to distinguish survey statuses
6. **Error Handling:** Handle network errors and API errors gracefully
7. **Cache Results:** Consider caching survey list results to reduce API calls

---

## Example: Search with Debounce

```javascript
import { useState, useEffect, useCallback } from 'react';
import debounce from 'lodash/debounce';

function SurveySearch({ token, onSearch }) {
  const [searchTerm, setSearchTerm] = useState('');

  // Debounce search API call
  const debouncedSearch = useCallback(
    debounce((value) => {
      onSearch(value);
    }, 500),
    [onSearch]
  );

  useEffect(() => {
    debouncedSearch(searchTerm);
  }, [searchTerm, debouncedSearch]);

  return (
    <input
      type="text"
      placeholder="Search surveys..."
      value={searchTerm}
      onChange={(e) => setSearchTerm(e.target.value)}
    />
  );
}
```

---

## Example: Filter by Multiple Statuses

If you need to filter by multiple statuses (e.g., show both Draft and Published), make separate API calls:

```javascript
// Get all surveys (no status filter)
const allSurveys = await getSurveys({ page: 1, perPage: 100 });

// Or make separate calls and combine results
const drafts = await getSurveys({ status: 'Draft' });
const published = await getSurveys({ status: 'Published' });
const combined = [...drafts.data, ...published.data];
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/survey/index` with Bearer token and request body
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **Save Survey**: `/api/survey/save` - Create or update a survey
- **Contact Index**: `/api/contacts/index` - Similar pagination pattern for contacts

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

