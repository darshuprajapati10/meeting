# Survey State API Documentation

## Overview
This API endpoint retrieves survey statistics for the authenticated user's organization, including total surveys count, active surveys (Published), total responses, and draft surveys count. This endpoint is perfect for displaying dashboard statistics cards showing survey overview metrics. All statistics are scoped to the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/state`  
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

### Basic Request (No Parameters Required)

```json
{}
```

**Note:** This endpoint does not require any request parameters. Simply send an empty JSON object `{}`.

---

## Response Format

### Success Response (200)

```json
{
  "data": {
    "total_surveys": 4,
    "active_surveys": 2,
    "total_responses": 262,
    "draft_surveys": 1
  },
  "message": "Statistics retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Statistics data object |
| `data.total_surveys` | integer | Total count of all surveys in user's organization |
| `data.active_surveys` | integer | Count of surveys with "Published" status (active surveys) |
| `data.total_responses` | integer | Total count of all responses to surveys in the organization |
| `data.draft_surveys` | integer | Count of surveys with "Draft" status |
| `message` | string | Success message |

---

## Field Specifications

### total_surveys
- **Type:** `integer`
- **Description:** Total count of all surveys in the authenticated user's organization
- **Example:** `4`, `24`, `50`, `100`
- **Note:** Includes surveys from all statuses (Draft, Published, Archived)

### active_surveys
- **Type:** `integer`
- **Description:** Count of surveys with "Published" status (active/live surveys)
- **Example:** `2`, `10`, `25`
- **Note:** Only counts surveys where `status` field equals "Published" (case-sensitive). These are surveys that are currently active and available for responses.

### total_responses
- **Type:** `integer`
- **Description:** Total count of all responses to surveys in the organization
- **Example:** `262`, `1000`, `5000`
- **Note:** 
  - Counts all responses from the `survey_responses` table
  - Only includes responses to surveys that belong to the user's organization
  - If the `survey_responses` table doesn't exist, this will return `0`
  - Each response represents a single submission to a survey

### draft_surveys
- **Type:** `integer`
- **Description:** Count of surveys with "Draft" status
- **Example:** `1`, `8`, `12`
- **Note:** Only counts surveys where `status` field equals "Draft" (case-sensitive). These are surveys that are not yet published.

---

## Example Usage

### JavaScript (Fetch API)

```javascript
async function getSurveyStatistics() {
  try {
    const response = await fetch('http://your-domain/api/survey/state', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer your-auth-token',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const result = await response.json();
    
    if (response.ok) {
      console.log('Total Surveys:', result.data.total_surveys);
      console.log('Active Surveys:', result.data.active_surveys);
      console.log('Total Responses:', result.data.total_responses);
      console.log('Draft Surveys:', result.data.draft_surveys);
    } else {
      console.error('Error:', result.message);
    }
  } catch (error) {
    console.error('Network error:', error);
  }
}

getSurveyStatistics();
```

### TypeScript (Fetch API)

```typescript
interface SurveyStatistics {
  data: {
    total_surveys: number;
    active_surveys: number;
    total_responses: number;
    draft_surveys: number;
  };
  message: string;
}

async function getSurveyStatistics(): Promise<SurveyStatistics> {
  const response = await fetch('http://your-domain/api/survey/state', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer your-auth-token',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({})
  });

  if (!response.ok) {
    throw new Error('Failed to fetch survey statistics');
  }

  return await response.json();
}

// Usage
getSurveyStatistics()
  .then((stats) => {
    console.log('Total Surveys:', stats.data.total_surveys);
    console.log('Active Surveys:', stats.data.active_surveys);
    console.log('Total Responses:', stats.data.total_responses);
    console.log('Draft Surveys:', stats.data.draft_surveys);
  })
  .catch((error) => {
    console.error('Error:', error);
  });
```

### Axios

```javascript
import axios from 'axios';

async function getSurveyStatistics() {
  try {
    const response = await axios.post(
      'http://your-domain/api/survey/state',
      {},
      {
        headers: {
          'Authorization': 'Bearer your-auth-token',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );

    const { data } = response.data;
    console.log('Total Surveys:', data.total_surveys);
    console.log('Active Surveys:', data.active_surveys);
    console.log('Total Responses:', data.total_responses);
    console.log('Draft Surveys:', data.draft_surveys);
  } catch (error) {
    console.error('Error:', error.response?.data?.message || error.message);
  }
}

getSurveyStatistics();
```

### React Hook (Custom Hook)

```javascript
import { useState, useEffect } from 'react';

function useSurveyStatistics() {
  const [statistics, setStatistics] = useState({
    total_surveys: 0,
    active_surveys: 0,
    total_responses: 0,
    draft_surveys: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchStatistics() {
      try {
        setLoading(true);
        const response = await fetch('http://your-domain/api/survey/state', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        if (!response.ok) {
          throw new Error('Failed to fetch statistics');
        }

        const result = await response.json();
        setStatistics(result.data);
        setError(null);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }

    fetchStatistics();
  }, []);

  return { statistics, loading, error };
}

// Usage in Component
function SurveyDashboard() {
  const { statistics, loading, error } = useSurveyStatistics();

  if (loading) return <div>Loading statistics...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <div>Total Surveys: {statistics.total_surveys}</div>
      <div>Active Surveys: {statistics.active_surveys}</div>
      <div>Total Responses: {statistics.total_responses}</div>
      <div>Draft Surveys: {statistics.draft_surveys}</div>
    </div>
  );
}
```

### React Component Example

```javascript
import React, { useState, useEffect } from 'react';

function SurveyStatisticsCards() {
  const [stats, setStats] = useState({
    total_surveys: 0,
    active_surveys: 0,
    total_responses: 0,
    draft_surveys: 0
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStatistics = async () => {
      try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('http://your-domain/api/survey/state', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        const result = await response.json();
        setStats(result.data);
      } catch (error) {
        console.error('Error fetching statistics:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStatistics();
  }, []);

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <div style={{ display: 'flex', gap: '20px', padding: '20px', flexWrap: 'wrap' }}>
      <StatCard
        title="Total Surveys"
        value={stats.total_surveys}
        icon="📄"
        color="#2c3e50"
      />
      <StatCard
        title="Active Surveys"
        value={stats.active_surveys}
        icon="📊"
        color="#3498db"
      />
      <StatCard
        title="Total Responses"
        value={stats.total_responses}
        icon="👥"
        color="#9b59b6"
      />
      <StatCard
        title="Draft Surveys"
        value={stats.draft_surveys}
        icon="✏️"
        color="#e67e22"
      />
    </div>
  );
}

function StatCard({ title, value, icon, color }) {
  return (
    <div style={{
      backgroundColor: 'white',
      padding: '24px',
      borderRadius: '8px',
      boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
      minWidth: '200px',
      flex: '1'
    }}>
      <div style={{ fontSize: '32px', marginBottom: '8px' }}>
        {icon}
      </div>
      <div style={{
        fontSize: '36px',
        fontWeight: 'bold',
        color: color,
        marginBottom: '8px'
      }}>
        {value}
      </div>
      <div style={{
        fontSize: '14px',
        color: '#666',
        fontWeight: 'normal'
      }}>
        {title}
      </div>
    </div>
  );
}

export default SurveyStatisticsCards;
```

### cURL

```bash
curl -X POST http://your-domain/api/survey/state \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### PHP (Guzzle HTTP)

```php
<?php

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://your-domain',
    'headers' => [
        'Authorization' => 'Bearer your-auth-token',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ]
]);

try {
    $response = $client->post('/api/survey/state', [
        'json' => []
    ]);

    $data = json_decode($response->getBody(), true);
    
    echo "Total Surveys: " . $data['data']['total_surveys'] . "\n";
    echo "Active Surveys: " . $data['data']['active_surveys'] . "\n";
    echo "Total Responses: " . $data['data']['total_responses'] . "\n";
    echo "Draft Surveys: " . $data['data']['draft_surveys'] . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Python (Requests)

```python
import requests

url = "http://your-domain/api/survey/state"
headers = {
    "Authorization": "Bearer your-auth-token",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
payload = {}

response = requests.post(url, json=payload, headers=headers)

if response.status_code == 200:
    data = response.json()
    print(f"Total Surveys: {data['data']['total_surveys']}")
    print(f"Active Surveys: {data['data']['active_surveys']}")
    print(f"Total Responses: {data['data']['total_responses']}")
    print(f"Draft Surveys: {data['data']['draft_surveys']}")
else:
    print(f"Error: {response.status_code}")
    print(response.json())
```

---

## Important Notes

### 1. Active Surveys
- The `active_surveys` count includes only surveys with "Published" status
- These are surveys that are currently live and available for responses
- Status must exactly match "Published" (case-sensitive)

### 2. Total Responses
- The `total_responses` count includes all responses from the `survey_responses` table
- Only counts responses to surveys that belong to the user's organization
- If the `survey_responses` table doesn't exist in the database, this will return `0`
- Each response represents a single submission/answer to a survey
- Responses are counted across all surveys in the organization

### 3. Organization Scope
- Statistics include surveys from the authenticated user's **first organization** only
- If user belongs to multiple organizations, only the first organization is used
- If user has no organizations, returns zeros for all statistics

### 4. No Parameters Required
- This endpoint does not accept any request parameters
- Simply send an empty JSON object `{}`
- All calculations are based on the current data in the database

### 5. Survey Status
- `total_surveys` includes surveys with **all statuses**:
  - Draft
  - Published
  - Archived
- `active_surveys` only includes surveys with status "Published" (case-sensitive)
- `draft_surveys` only includes surveys with status "Draft" (case-sensitive)
- `total_responses` counts responses regardless of survey status

### 6. Date Format
- Survey creation dates are stored in `created_at` timestamp format
- Comparisons are done using date/time ranges
- All date calculations use server timezone

---

## Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | Success - Statistics retrieved successfully |
| `401` | Unauthorized - Invalid or missing authentication token |
| `500` | Internal Server Error - Server error occurred |

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 500 Internal Server Error

```json
{
  "message": "Server Error"
}
```

---

## Best Practices

1. **Caching:** Consider caching statistics for a short period (e.g., 1-5 minutes) to reduce database queries, especially if this endpoint is called frequently

2. **Polling:** If displaying real-time statistics, poll this endpoint at reasonable intervals (e.g., every 30 seconds to 1 minute) rather than continuously

3. **Error Handling:** Always handle network errors and API errors gracefully in your frontend

4. **Loading States:** Show loading indicators while fetching statistics

5. **Empty States:** Handle cases where all statistics are zero (no surveys)

6. **Auto-refresh:** Consider auto-refreshing statistics when:
   - User creates a new survey
   - User updates a survey status
   - User deletes a survey
   - Page becomes visible (using Page Visibility API)

7. **Timezone Considerations:** Be aware that statistics are calculated based on server timezone. If your application needs client timezone support, you may need to pass timezone information in future API versions

---

## Integration Example: Complete Dashboard

```javascript
import React, { useState, useEffect } from 'react';

function SurveyDashboard() {
  const [statistics, setStatistics] = useState({
    total_surveys: 0,
    active_surveys: 0,
    total_responses: 0,
    draft_surveys: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchStatistics = async () => {
    try {
      setLoading(true);
      setError(null);

      const token = localStorage.getItem('auth_token');
      const response = await fetch('http://your-domain/api/survey/state', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({})
      });

      if (!response.ok) {
        throw new Error('Failed to fetch statistics');
      }

      const result = await response.json();
      setStatistics(result.data);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching survey statistics:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchStatistics();
    
    // Auto-refresh every 60 seconds
    const interval = setInterval(fetchStatistics, 60000);
    
    return () => clearInterval(interval);
  }, []);

  if (loading) {
    return (
      <div style={{ padding: '20px', textAlign: 'center' }}>
        Loading statistics...
      </div>
    );
  }

  if (error) {
    return (
      <div style={{ padding: '20px', color: 'red' }}>
        Error: {error}
        <button onClick={fetchStatistics} style={{ marginLeft: '10px' }}>
          Retry
        </button>
      </div>
    );
  }

  return (
    <div style={{ padding: '20px' }}>
      <h2>Survey Statistics</h2>
      <div style={{ display: 'flex', gap: '20px', flexWrap: 'wrap' }}>
        <StatCard
          title="Total Surveys"
          value={statistics.total_surveys}
          icon="📄"
          color="#2c3e50"
        />
        <StatCard
          title="Active Surveys"
          value={statistics.active_surveys}
          icon="📊"
          color="#3498db"
        />
        <StatCard
          title="Total Responses"
          value={statistics.total_responses}
          icon="👥"
          color="#9b59b6"
        />
        <StatCard
          title="Draft Surveys"
          value={statistics.draft_surveys}
          icon="✏️"
          color="#e67e22"
        />
      </div>
    </div>
  );
}

function StatCard({ title, value, icon, color }) {
  return (
    <div style={{
      backgroundColor: 'white',
      padding: '24px',
      borderRadius: '8px',
      boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
      minWidth: '200px',
      flex: '1'
    }}>
      <div style={{ fontSize: '32px', marginBottom: '8px' }}>
        {icon}
      </div>
      <div style={{
        fontSize: '36px',
        fontWeight: 'bold',
        color: color,
        marginBottom: '8px'
      }}>
        {value}
      </div>
      <div style={{
        fontSize: '14px',
        color: '#666',
        fontWeight: 'normal'
      }}>
        {title}
      </div>
    </div>
  );
}

export default SurveyDashboard;
```

---

## Related Endpoints

- **Survey Index:** `POST /api/survey/index` - Get paginated list of surveys
- **Survey Show:** `POST /api/survey/show` - Get single survey by ID
- **Survey Save:** `POST /api/survey/save` - Create or update a survey
- **Survey Delete:** `POST /api/survey/delete` - Delete a survey
- **Survey Dropdown:** `POST /api/survey/dropdown` - Get surveys dropdown list

---

## Support

For issues or questions regarding this API endpoint, please contact the development team or refer to the main API documentation.

---

**Last Updated:** November 2025

