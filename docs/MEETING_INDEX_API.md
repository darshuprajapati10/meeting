# Meeting Index API Documentation

## Overview
This API endpoint retrieves a paginated list of all meetings from the user's organization. It supports pagination, search, and filtering by meeting status and date. Meetings are ordered by date and time (earliest first). Perfect for displaying meetings in a calendar, table, or list view with pagination controls.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/meeting/index`  
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
  "search": "Project Kickoff"
}
```

### Request with Status Filter

```json
{
  "page": 1,
  "per_page": 15,
  "status": "Scheduled"
}
```

### Request with Date Filter

```json
{
  "page": 1,
  "per_page": 15,
  "date": "2025-11-15"
}
```

### Request with All Parameters

```json
{
  "page": 2,
  "per_page": 20,
  "search": "Project",
  "status": "Scheduled",
  "date": "2025-11-15"
}
```

---

## Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number (starts from 1) |
| `per_page` | integer | No | 15 | Number of meetings per page (1-100) |
| `search` | string | No | - | Search term (searches in meeting_title and agenda_notes) |
| `status` | string | No | - | Filter by meeting status (Created, Scheduled, Completed, Cancelled) |
| `date` | string (date) | No | - | Filter by specific date (format: YYYY-MM-DD) |

---

## Response Format

### Success Response (200)

```json
{
  "data": [
    {
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
        "survey_name": "Project Survey"
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
        }
      ],
      "created_at": "2025-11-04T10:30:00.000000Z",
      "updated_at": "2025-11-04T10:30:00.000000Z"
    },
    {
      "id": 2,
      "organization_id": 1,
      "meeting_title": "Team Standup",
      "status": "Created",
      "date": "2025-11-16",
      "time": "09:00",
      "duration": 15,
      "meeting_type": "Video Call",
      "custom_location": null,
      "survey_id": null,
      "survey": null,
      "agenda_notes": "Daily standup meeting.",
      "created_by": 1,
      "attendees": [
        {
          "id": 3,
          "first_name": "Bob",
          "last_name": "Johnson",
          "email": "bob@example.com",
          "phone": "5555555555"
        }
      ],
      "notifications": [],
      "created_at": "2025-11-04T11:15:00.000000Z",
      "updated_at": "2025-11-04T11:15:00.000000Z"
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
  "message": "Meetings retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | array | Array of meeting objects (MeetingResource) |
| `meta` | object | Pagination metadata |
| `meta.current_page` | integer | Current page number |
| `meta.from` | integer\|null | First item number on current page (null if empty) |
| `meta.last_page` | integer | Last page number (total pages) |
| `meta.per_page` | integer | Number of items per page |
| `meta.to` | integer\|null | Last item number on current page (null if empty) |
| `meta.total` | integer | Total number of meetings |
| `message` | string | Success message |

### Meeting Object Structure

Each meeting object in the `data` array contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Meeting ID |
| `organization_id` | integer | Organization ID |
| `meeting_title` | string | Meeting title/name |
| `status` | string | Meeting status (Created, Scheduled, Completed, Cancelled) |
| `date` | string | Meeting date (YYYY-MM-DD format) |
| `time` | string | Meeting time (HH:MM format) |
| `duration` | integer | Meeting duration in minutes |
| `meeting_type` | string | Type of meeting (Video Call, In-Person Meeting, Phone Call, Online Meeting) |
| `custom_location` | string\|null | Custom location or meeting link |
| `survey_id` | integer\|null | ID of attached survey (if any) |
| `survey` | object\|null | Survey object (if survey_id exists) |
| `agenda_notes` | string\|null | Meeting agenda, talking points, or notes |
| `created_by` | integer | User ID who created the meeting |
| `attendees` | array | Array of attendee contact objects |
| `notifications` | array | Array of notification rules |
| `created_at` | string | Creation timestamp (ISO 8601) |
| `updated_at` | string | Last update timestamp (ISO 8601) |

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

---

## Pagination Example (3 Pages)

If you have 35 meetings with 15 per page, you'll have 3 pages:

**Page 1:** Meetings 1-15
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

**Page 2:** Meetings 16-30
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

**Page 3:** Meetings 31-35
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
// Get meetings list
async function getMeetings(params, token) {
  try {
    const response = await fetch('http://your-api-url/api/meeting/index', {
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
        ...(params.status && { status: params.status }),
        ...(params.date && { date: params.date })
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve meetings');
    }

    return data; // Returns { data: [...], meta: {...}, message: "..." }
  } catch (error) {
    console.error('Error fetching meetings:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');

// Basic request
const meetings = await getMeetings({}, token);

// With pagination
const page2 = await getMeetings({ page: 2, perPage: 20 }, token);

// With search
const searchResults = await getMeetings({ search: 'Project' }, token);

// With status filter
const scheduled = await getMeetings({ status: 'Scheduled' }, token);

// With date filter
const todayMeetings = await getMeetings({ date: '2025-11-15' }, token);

// All parameters
const all = await getMeetings({ 
  page: 1, 
  perPage: 15, 
  search: 'Project',
  status: 'Scheduled',
  date: '2025-11-15'
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

// Get meetings list
async function getMeetings(page = 1, perPage = 15, search = '', status = '', date = '') {
  try {
    const response = await apiClient.post('/meeting/index', {
      page,
      per_page: perPage,
      ...(search && { search }),
      ...(status && { status }),
      ...(date && { date })
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
const result = await getMeetings(1, 15, 'Project', 'Scheduled', '2025-11-15');
console.log('Meetings:', result.data);
console.log('Pagination:', result.meta);
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

interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
}

interface MeetingsResponse {
  data: Meeting[];
  meta: PaginationMeta;
  message: string;
}

export function useMeetings(
  token: string,
  page: number = 1,
  perPage: number = 15,
  search: string = '',
  status: string = '',
  date: string = ''
) {
  const [meetings, setMeetings] = useState<Meeting[]>([]);
  const [pagination, setPagination] = useState<PaginationMeta | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchMeetings = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<MeetingsResponse>(
          '/api/meeting/index',
          {
            page,
            per_page: perPage,
            ...(search && { search }),
            ...(status && { status }),
            ...(date && { date })
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setMeetings(response.data.data);
        setPagination(response.data.meta);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching meetings:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      fetchMeetings();
    }
  }, [token, page, perPage, search, status, date]);

  return { meetings, pagination, loading, error };
}

// Usage in component
function MeetingsList() {
  const token = localStorage.getItem('auth_token') || '';
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [date, setDate] = useState('');
  
  const { meetings, pagination, loading, error } = useMeetings(
    token, 
    page, 
    15, 
    search, 
    status,
    date
  );

  if (loading) return <div>Loading meetings...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div>
      <input
        type="text"
        placeholder="Search meetings..."
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
        <option value="Created">Created</option>
        <option value="Scheduled">Scheduled</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
      </select>
      <input
        type="date"
        value={date}
        onChange={(e) => {
          setDate(e.target.value);
          setPage(1);
        }}
      />

      {meetings.map(meeting => (
        <div key={meeting.id}>
          <h3>{meeting.meeting_title}</h3>
          <p>Date: {meeting.date} at {meeting.time}</p>
          <p>Status: {meeting.status}</p>
          <p>Attendees: {meeting.attendees.length}</p>
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

function MeetingsTable({ token }) {
  const [meetings, setMeetings] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [date, setDate] = useState('');

  useEffect(() => {
    fetchMeetings();
  }, [page, perPage, search, status, date]);

  const fetchMeetings = async () => {
    setLoading(true);
    
    try {
      const response = await fetch('/api/meeting/index', {
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
          ...(status && { status }),
          ...(date && { date })
        })
      });

      const data = await response.json();
      
      if (response.ok) {
        setMeetings(data.data);
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
    setPage(1);
  };

  const handleStatusChange = (value) => {
    setStatus(value);
    setPage(1);
  };

  const handleDateChange = (value) => {
    setDate(value);
    setPage(1);
  };

  if (loading && meetings.length === 0) {
    return <div>Loading meetings...</div>;
  }

  return (
    <div>
      {/* Search and Filters */}
      <div style={{ marginBottom: '20px', display: 'flex', gap: '10px' }}>
        <input
          type="text"
          placeholder="Search meetings by title or agenda..."
          value={search}
          onChange={(e) => handleSearchChange(e.target.value)}
          style={{
            padding: '8px',
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
            borderRadius: '4px',
            border: '1px solid #ddd'
          }}
        >
          <option value="">All Status</option>
          <option value="Created">Created</option>
          <option value="Scheduled">Scheduled</option>
          <option value="Completed">Completed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
        <input
          type="date"
          value={date}
          onChange={(e) => handleDateChange(e.target.value)}
          style={{
            padding: '8px',
            borderRadius: '4px',
            border: '1px solid #ddd'
          }}
        />
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

      {/* Meetings Table */}
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr style={{ backgroundColor: '#f5f5f5' }}>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Title</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Date & Time</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Duration</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Type</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Status</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Attendees</th>
            <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>Location</th>
          </tr>
        </thead>
        <tbody>
          {meetings.length === 0 ? (
            <tr>
              <td colSpan="7" style={{ padding: '20px', textAlign: 'center' }}>
                No meetings found
              </td>
            </tr>
          ) : (
            meetings.map(meeting => (
              <tr key={meeting.id}>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>{meeting.meeting_title}</td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                  {meeting.date} {meeting.time}
                </td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>{meeting.duration} min</td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>{meeting.meeting_type}</td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
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
                </td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                  {meeting.attendees.length} attendee(s)
                </td>
                <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                  {meeting.custom_location || '-'}
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
            {pagination.total > 0 && ` (${pagination.total} total meetings)`}
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
```

---

## cURL Examples

### Basic Request

```bash
curl -X POST "http://your-api-url/api/meeting/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### Request with Pagination

```bash
curl -X POST "http://your-api-url/api/meeting/index" \
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
curl -X POST "http://your-api-url/api/meeting/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "search": "Project Kickoff"
  }'
```

### Request with Status Filter

```bash
curl -X POST "http://your-api-url/api/meeting/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "status": "Scheduled"
  }'
```

### Request with Date Filter

```bash
curl -X POST "http://your-api-url/api/meeting/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "date": "2025-11-15"
  }'
```

### Request with All Parameters

```bash
curl -X POST "http://your-api-url/api/meeting/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "search": "Project",
    "status": "Scheduled",
    "date": "2025-11-15"
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Meetings retrieved successfully |
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
  "message": "No organization found. Please create a meeting first."
}
```

---

## Important Notes

### 1. Pagination
- Default: 15 meetings per page
- Minimum: 1 per page
- Maximum: 100 per page
- Page numbers start from 1 (not 0)

### 2. Search
- Searches in: `meeting_title` and `agenda_notes`
- Case-insensitive partial match
- Example: searching "project" will find "Project Kickoff Meeting" and "project planning"

### 3. Status Filter
- Valid values: `Created`, `Scheduled`, `Completed`, `Cancelled`
- Case-sensitive (must match exactly)
- If an invalid status is provided, it will return empty results

### 4. Date Filter
- Format: `YYYY-MM-DD` (e.g., "2025-11-15")
- Filters meetings on the exact date specified
- Returns meetings that match the date exactly

### 5. Ordering
- Meetings are ordered by `date` in ascending order (earliest first)
- When dates are the same, ordered by `time` in ascending order (earliest time first)
- This ensures meetings are displayed chronologically

### 6. Response Includes Full Structure
- Each meeting in the response includes all `attendees` and `notifications`
- This means the response can be large if meetings have many attendees
- Consider implementing lazy loading in the UI for better performance

### 7. Organization Scope
- Only meetings from the authenticated user's organization are returned
- If the user doesn't have an organization, an empty response is returned

---

## Best Practices

1. **Debounce Search:** Implement debouncing when users type in the search field to avoid excessive API calls
2. **Loading States:** Show loading indicators while fetching meetings
3. **Empty States:** Display helpful messages when no meetings are found
4. **Pagination UI:** Provide clear navigation controls (Previous/Next buttons, page numbers)
5. **Status Badges:** Use visual indicators (colors, badges) to distinguish meeting statuses
6. **Date Filtering:** Use date pickers for better UX when filtering by date
7. **Calendar View:** Consider implementing a calendar view for better meeting visualization
8. **Upcoming Meetings:** Use date filter to show today's or upcoming meetings
9. **Error Handling:** Handle network errors and API errors gracefully
10. **Cache Results:** Consider caching meeting list results to reduce API calls

---

## Example: Calendar View Integration

```javascript
// Get meetings for a specific date range
async function getMeetingsForDateRange(startDate, endDate, token) {
  const meetings = [];
  
  // Get meetings for each date in range
  const currentDate = new Date(startDate);
  const end = new Date(endDate);
  
  while (currentDate <= end) {
    const dateStr = currentDate.toISOString().split('T')[0];
    const response = await getMeetings({ date: dateStr }, token);
    meetings.push(...response.data);
    
    currentDate.setDate(currentDate.getDate() + 1);
  }
  
  return meetings;
}

// Usage: Get all meetings for this week
const today = new Date();
const weekStart = new Date(today);
weekStart.setDate(today.getDate() - today.getDay()); // Start of week

const weekEnd = new Date(weekStart);
weekEnd.setDate(weekStart.getDate() + 6); // End of week

const weekMeetings = await getMeetingsForDateRange(
  weekStart.toISOString().split('T')[0],
  weekEnd.toISOString().split('T')[0],
  token
);
```

---

## Example: Upcoming Meetings Widget

```javascript
// Get upcoming meetings (next 7 days)
async function getUpcomingMeetings(token) {
  const upcomingMeetings = [];
  const today = new Date();
  
  for (let i = 0; i < 7; i++) {
    const date = new Date(today);
    date.setDate(today.getDate() + i);
    const dateStr = date.toISOString().split('T')[0];
    
    const response = await getMeetings({ 
      date: dateStr,
      status: 'Scheduled'
    }, token);
    
    upcomingMeetings.push(...response.data);
  }
  
  // Sort by date and time
  return upcomingMeetings.sort((a, b) => {
    const dateCompare = a.date.localeCompare(b.date);
    if (dateCompare !== 0) return dateCompare;
    return a.time.localeCompare(b.time);
  });
}
```

---

## Example: Search with Debounce

```javascript
import { useState, useEffect, useCallback } from 'react';
import debounce from 'lodash/debounce';

function MeetingSearch({ token, onSearch }) {
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
      placeholder="Search meetings by title or agenda..."
      value={searchTerm}
      onChange={(e) => setSearchTerm(e.target.value)}
    />
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/meeting/index` with Bearer token and request body
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **Save Meeting**: `/api/meeting/save` - Create or update a meeting
- **Show Meeting**: `/api/meeting/show` - Get single meeting details
- **Delete Meeting**: `/api/meeting/delete` - Delete a meeting
- **Contact Index**: `/api/contacts/index` - Similar pagination pattern for contacts
- **Survey Index**: `/api/survey/index` - Similar pagination pattern for surveys

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

