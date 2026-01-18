# Calendar State API Documentation

## Overview
This API endpoint retrieves calendar statistics for meetings, including total meetings count, meetings scheduled for the current week, and meetings scheduled for today. This endpoint is perfect for displaying dashboard statistics cards showing meeting overview metrics. All statistics are scoped to the authenticated user's organizations.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/calendar/state`  
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
    "total_meetings": 1,
    "this_week": 0,
    "today": 0
  },
  "message": "Statistics retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Statistics data object |
| `data.total_meetings` | integer | Total count of all meetings in user's organizations |
| `data.this_week` | integer | Count of meetings scheduled in the current week (Monday to Sunday) |
| `data.today` | integer | Count of meetings scheduled for today |
| `message` | string | Success message |

---

## Field Specifications

### total_meetings
- **Type:** `integer`
- **Description:** Total count of all meetings across all organizations that the authenticated user belongs to
- **Example:** `1`, `25`, `100`
- **Note:** Includes meetings from all statuses (Created, Scheduled, Completed, Cancelled)

### this_week
- **Type:** `integer`
- **Description:** Count of meetings scheduled within the current week
- **Week Definition:** Monday (start of week) to Sunday (end of week) based on ISO week standard
- **Example:** `0`, `5`, `12`
- **Note:** Only counts meetings where the `date` field falls within the current week range

### today
- **Type:** `integer`
- **Description:** Count of meetings scheduled for the current date (today)
- **Example:** `0`, `2`, `8`
- **Note:** Only counts meetings where the `date` field matches today's date

---

## Example Usage

### JavaScript (Fetch API)

```javascript
async function getCalendarStatistics() {
  try {
    const response = await fetch('http://your-domain/api/calendar/state', {
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
      console.log('Total Meetings:', result.data.total_meetings);
      console.log('This Week:', result.data.this_week);
      console.log('Today:', result.data.today);
    } else {
      console.error('Error:', result.message);
    }
  } catch (error) {
    console.error('Network error:', error);
  }
}

getCalendarStatistics();
```

### TypeScript (Fetch API)

```typescript
interface CalendarStatistics {
  data: {
    total_meetings: number;
    this_week: number;
    today: number;
  };
  message: string;
}

async function getCalendarStatistics(): Promise<CalendarStatistics> {
  const response = await fetch('http://your-domain/api/calendar/state', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer your-auth-token',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({})
  });

  if (!response.ok) {
    throw new Error('Failed to fetch calendar statistics');
  }

  return await response.json();
}

// Usage
getCalendarStatistics()
  .then((stats) => {
    console.log('Total Meetings:', stats.data.total_meetings);
    console.log('This Week:', stats.data.this_week);
    console.log('Today:', stats.data.today);
  })
  .catch((error) => {
    console.error('Error:', error);
  });
```

### Axios

```javascript
import axios from 'axios';

async function getCalendarStatistics() {
  try {
    const response = await axios.post(
      'http://your-domain/api/calendar/state',
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
    console.log('Total Meetings:', data.total_meetings);
    console.log('This Week:', data.this_week);
    console.log('Today:', data.today);
  } catch (error) {
    console.error('Error:', error.response?.data?.message || error.message);
  }
}

getCalendarStatistics();
```

### React Hook (Custom Hook)

```javascript
import { useState, useEffect } from 'react';

function useCalendarStatistics() {
  const [statistics, setStatistics] = useState({
    total_meetings: 0,
    this_week: 0,
    today: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchStatistics() {
      try {
        setLoading(true);
        const response = await fetch('http://your-domain/api/calendar/state', {
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
function CalendarDashboard() {
  const { statistics, loading, error } = useCalendarStatistics();

  if (loading) return <div>Loading statistics...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <div>Total Meetings: {statistics.total_meetings}</div>
      <div>This Week: {statistics.this_week}</div>
      <div>Today: {statistics.today}</div>
    </div>
  );
}
```

### React Component Example

```javascript
import React, { useState, useEffect } from 'react';

function CalendarStatisticsCards() {
  const [stats, setStats] = useState({
    total_meetings: 0,
    this_week: 0,
    today: 0
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStatistics = async () => {
      try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('http://your-domain/api/calendar/state', {
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
    <div style={{ display: 'flex', gap: '20px', padding: '20px' }}>
      <div style={{
        backgroundColor: 'white',
        padding: '20px',
        borderRadius: '8px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
        minWidth: '200px'
      }}>
        <h3 style={{ margin: '0 0 10px 0', color: '#666' }}>Total Meetings</h3>
        <div style={{ fontSize: '32px', fontWeight: 'bold', color: '#2c3e50' }}>
          {stats.total_meetings}
        </div>
      </div>

      <div style={{
        backgroundColor: 'white',
        padding: '20px',
        borderRadius: '8px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
        minWidth: '200px'
      }}>
        <h3 style={{ margin: '0 0 10px 0', color: '#666' }}>This Week</h3>
        <div style={{ fontSize: '32px', fontWeight: 'bold', color: '#9b59b6' }}>
          {stats.this_week}
        </div>
      </div>

      <div style={{
        backgroundColor: 'white',
        padding: '20px',
        borderRadius: '8px',
        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
        minWidth: '200px'
      }}>
        <h3 style={{ margin: '0 0 10px 0', color: '#666' }}>Today</h3>
        <div style={{ fontSize: '32px', fontWeight: 'bold', color: '#2c3e50' }}>
          {stats.today}
        </div>
      </div>
    </div>
  );
}

export default CalendarStatisticsCards;
```

### cURL

```bash
curl -X POST http://your-domain/api/calendar/state \
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
    $response = $client->post('/api/calendar/state', [
        'json' => []
    ]);

    $data = json_decode($response->getBody(), true);
    
    echo "Total Meetings: " . $data['data']['total_meetings'] . "\n";
    echo "This Week: " . $data['data']['this_week'] . "\n";
    echo "Today: " . $data['data']['today'] . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Python (Requests)

```python
import requests

url = "http://your-domain/api/calendar/state"
headers = {
    "Authorization": "Bearer your-auth-token",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
payload = {}

response = requests.post(url, json=payload, headers=headers)

if response.status_code == 200:
    data = response.json()
    print(f"Total Meetings: {data['data']['total_meetings']}")
    print(f"This Week: {data['data']['this_week']}")
    print(f"Today: {data['data']['today']}")
else:
    print(f"Error: {response.status_code}")
    print(response.json())
```

---

## Important Notes

### 1. Week Calculation
- The `this_week` count uses ISO week standard
- Week starts on **Monday** and ends on **Sunday**
- Uses `startOfWeek()` and `endOfWeek()` from Carbon (Laravel's date library)
- Example: If today is Wednesday, Nov 6, 2025, the week is Nov 4 (Monday) to Nov 10 (Sunday), 2025

### 2. Today Calculation
- The `today` count uses the server's current date/time
- Compares meeting `date` field with today's date (ignores time)
- Example: If today is Nov 6, 2025, it counts all meetings with `date = 2025-11-06`

### 3. Organization Scope
- Statistics include meetings from **all organizations** the authenticated user belongs to
- If user belongs to multiple organizations, counts are aggregated across all
- If user has no organizations, returns zeros for all statistics

### 4. No Parameters Required
- This endpoint does not accept any request parameters
- Simply send an empty JSON object `{}`
- All calculations are based on the current date/time on the server

### 5. Meeting Status
- `total_meetings` includes meetings with **all statuses**:
  - Created
  - Scheduled
  - Completed
  - Cancelled
- `this_week` and `today` also include all statuses (not filtered by status)

### 6. Date Format
- Meeting dates are stored in `YYYY-MM-DD` format
- Comparisons are done using date-only (time is ignored)
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

5. **Empty States:** Handle cases where all statistics are zero (no meetings)

6. **Auto-refresh:** Consider auto-refreshing statistics when:
   - User creates a new meeting
   - User updates a meeting date
   - User deletes a meeting
   - Page becomes visible (using Page Visibility API)

7. **Timezone Considerations:** Be aware that statistics are calculated based on server timezone. If your application needs client timezone support, you may need to pass timezone information in future API versions

---

## Integration Example: Complete Dashboard

```javascript
import React, { useState, useEffect } from 'react';

function CalendarDashboard() {
  const [statistics, setStatistics] = useState({
    total_meetings: 0,
    this_week: 0,
    today: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchStatistics = async () => {
    try {
      setLoading(true);
      setError(null);

      const token = localStorage.getItem('auth_token');
      const response = await fetch('http://your-domain/api/calendar/state', {
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
      console.error('Error fetching calendar statistics:', err);
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
      <h2>Calendar Statistics</h2>
      <div style={{ display: 'flex', gap: '20px', flexWrap: 'wrap' }}>
        <StatCard
          title="Total Meetings"
          value={statistics.total_meetings}
          color="#2c3e50"
        />
        <StatCard
          title="This Week"
          value={statistics.this_week}
          color="#9b59b6"
        />
        <StatCard
          title="Today"
          value={statistics.today}
          color="#2c3e50"
        />
      </div>
    </div>
  );
}

function StatCard({ title, value, color }) {
  return (
    <div style={{
      backgroundColor: 'white',
      padding: '24px',
      borderRadius: '8px',
      boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
      minWidth: '200px',
      flex: '1'
    }}>
      <h3 style={{
        margin: '0 0 12px 0',
        color: '#666',
        fontSize: '14px',
        fontWeight: 'normal'
      }}>
        {title}
      </h3>
      <div style={{
        fontSize: '36px',
        fontWeight: 'bold',
        color: color
      }}>
        {value}
      </div>
    </div>
  );
}

export default CalendarDashboard;
```

---

## Related Endpoints

- **Meeting Index:** `POST /api/meeting/index` - Get paginated list of meetings
- **Current Month:** `POST /api/meeting/current-month` - Get calendar month view
- **Current Week:** `POST /api/meeting/current-week` - Get calendar week view
- **Current Day:** `POST /api/meeting/current-day` - Get meetings for a specific day

---

## Support

For issues or questions regarding this API endpoint, please contact the development team or refer to the main API documentation.

---

**Last Updated:** November 2025

