# Contacts State API Documentation

## Overview
This API endpoint retrieves contact statistics for the authenticated user's organization, including total contacts count, contacts created this month, recently added contacts (today), and favorite contacts count. This endpoint is perfect for displaying dashboard statistics cards showing contact overview metrics. All statistics are scoped to the authenticated user's organization, and favorites are user-specific.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/state`  
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
    "total_contacts": 51,
    "this_month": 12,
    "recently_added": 5,
    "favorites": 3
  },
  "message": "Statistics retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Statistics data object |
| `data.total_contacts` | integer | Total count of all contacts in user's organization |
| `data.this_month` | integer | Count of contacts created in the current month |
| `data.recently_added` | integer | Count of contacts created today |
| `data.favorites` | integer | Count of contacts marked as favorite by current user |
| `message` | string | Success message |

---

## Field Specifications

### total_contacts
- **Type:** `integer`
- **Description:** Total count of all contacts in the authenticated user's organization
- **Example:** `51`, `100`, `250`
- **Note:** Includes all contacts regardless of any filters or status

### this_month
- **Type:** `integer`
- **Description:** Count of contacts created in the current month
- **Month Definition:** From the 1st day of the current month to the last day of the current month
- **Example:** `12`, `25`, `0`
- **Note:** Only counts contacts where `created_at` falls within the current month

### recently_added
- **Type:** `integer`
- **Description:** Count of contacts created today (recently added)
- **Example:** `5`, `10`, `0`
- **Note:** Only counts contacts where `created_at` falls within today's date range (from start of day to end of day)

### favorites
- **Type:** `integer`
- **Description:** Count of contacts marked as favorite by the current authenticated user
- **Example:** `3`, `8`, `0`
- **Note:** 
  - User-specific: Each user sees their own favorite count
  - Only counts contacts where `is_favourite` is `true` in the `contact_favourites` table
  - Requires a record in `contact_favourites` table with `user_id` matching the authenticated user

---

## Example Usage

### JavaScript (Fetch API)

```javascript
async function getContactStatistics() {
  try {
    const response = await fetch('http://your-domain/api/contacts/state', {
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
      console.log('Total Contacts:', result.data.total_contacts);
      console.log('This Month:', result.data.this_month);
      console.log('Recently Added:', result.data.recently_added);
      console.log('Favorites:', result.data.favorites);
    } else {
      console.error('Error:', result.message);
    }
  } catch (error) {
    console.error('Network error:', error);
  }
}

getContactStatistics();
```

### TypeScript (Fetch API)

```typescript
interface ContactStatistics {
  data: {
    total_contacts: number;
    this_month: number;
    recently_added: number;
    favorites: number;
  };
  message: string;
}

async function getContactStatistics(): Promise<ContactStatistics> {
  const response = await fetch('http://your-domain/api/contacts/state', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer your-auth-token',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({})
  });

  if (!response.ok) {
    throw new Error('Failed to fetch contact statistics');
  }

  return await response.json();
}

// Usage
getContactStatistics()
  .then((stats) => {
    console.log('Total Contacts:', stats.data.total_contacts);
    console.log('This Month:', stats.data.this_month);
    console.log('Recently Added:', stats.data.recently_added);
    console.log('Favorites:', stats.data.favorites);
  })
  .catch((error) => {
    console.error('Error:', error);
  });
```

### Axios

```javascript
import axios from 'axios';

async function getContactStatistics() {
  try {
    const response = await axios.post(
      'http://your-domain/api/contacts/state',
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
    console.log('Total Contacts:', data.total_contacts);
    console.log('This Month:', data.this_month);
    console.log('Recently Added:', data.recently_added);
    console.log('Favorites:', data.favorites);
  } catch (error) {
    console.error('Error:', error.response?.data?.message || error.message);
  }
}

getContactStatistics();
```

### React Hook (Custom Hook)

```javascript
import { useState, useEffect } from 'react';

function useContactStatistics() {
  const [statistics, setStatistics] = useState({
    total_contacts: 0,
    this_month: 0,
    recently_added: 0,
    favorites: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchStatistics() {
      try {
        setLoading(true);
        const response = await fetch('http://your-domain/api/contacts/state', {
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
function ContactDashboard() {
  const { statistics, loading, error } = useContactStatistics();

  if (loading) return <div>Loading statistics...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <div>Total Contacts: {statistics.total_contacts}</div>
      <div>This Month: {statistics.this_month}</div>
      <div>Recently Added: {statistics.recently_added}</div>
      <div>Favorites: {statistics.favorites}</div>
    </div>
  );
}
```

### React Component Example

```javascript
import React, { useState, useEffect } from 'react';

function ContactStatisticsCards() {
  const [stats, setStats] = useState({
    total_contacts: 0,
    this_month: 0,
    recently_added: 0,
    favorites: 0
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStatistics = async () => {
      try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('http://your-domain/api/contacts/state', {
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
        title="Total Contacts"
        value={stats.total_contacts}
        icon="👥"
        color="#2c3e50"
      />
      <StatCard
        title="This Month"
        value={stats.this_month}
        icon="📅"
        color="#3498db"
      />
      <StatCard
        title="Recently Add"
        value={stats.recently_added}
        icon="➕"
        color="#9b59b6"
      />
      <StatCard
        title="Favorites"
        value={stats.favorites}
        icon="❤️"
        color="#e74c3c"
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

export default ContactStatisticsCards;
```

### cURL

```bash
curl -X POST http://your-domain/api/contacts/state \
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
    $response = $client->post('/api/contacts/state', [
        'json' => []
    ]);

    $data = json_decode($response->getBody(), true);
    
    echo "Total Contacts: " . $data['data']['total_contacts'] . "\n";
    echo "This Month: " . $data['data']['this_month'] . "\n";
    echo "Recently Added: " . $data['data']['recently_added'] . "\n";
    echo "Favorites: " . $data['data']['favorites'] . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Python (Requests)

```python
import requests

url = "http://your-domain/api/contacts/state"
headers = {
    "Authorization": "Bearer your-auth-token",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
payload = {}

response = requests.post(url, json=payload, headers=headers)

if response.status_code == 200:
    data = response.json()
    print(f"Total Contacts: {data['data']['total_contacts']}")
    print(f"This Month: {data['data']['this_month']}")
    print(f"Recently Added: {data['data']['recently_added']}")
    print(f"Favorites: {data['data']['favorites']}")
else:
    print(f"Error: {response.status_code}")
    print(response.json())
```

---

## Important Notes

### 1. Month Calculation
- The `this_month` count uses the calendar month (1st to last day of current month)
- Uses `startOfMonth()` from Carbon (Laravel's date library)
- Example: If today is November 6, 2025, it counts all contacts created from November 1, 2025 to November 30, 2025

### 2. Today Calculation
- The `recently_added` count uses the server's current date/time
- Compares contact `created_at` field with today's date range (start of day to end of day)
- Example: If today is November 6, 2025, it counts all contacts created between 00:00:00 and 23:59:59 on November 6, 2025

### 3. Organization Scope
- Statistics include contacts from the authenticated user's **first organization** only
- If user belongs to multiple organizations, only the first organization is used
- If user has no organizations, returns zeros for all statistics

### 4. No Parameters Required
- This endpoint does not accept any request parameters
- Simply send an empty JSON object `{}`
- All calculations are based on the current date/time on the server

### 5. Favorites (User-Specific)
- The `favorites` count is **user-specific**
- Each user sees only their own favorite contacts count
- Requires a record in the `contact_favourites` table with:
  - `user_id` matching the authenticated user
  - `is_favourite` set to `true`
  - `organization_id` matching the user's organization
- To mark/unmark a contact as favorite, use the `/api/contacts/favourite` endpoint

### 6. Date Format
- Contact creation dates are stored in `created_at` timestamp format
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

5. **Empty States:** Handle cases where all statistics are zero (no contacts)

6. **Auto-refresh:** Consider auto-refreshing statistics when:
   - User creates a new contact
   - User marks/unmarks a contact as favorite
   - User deletes a contact
   - Page becomes visible (using Page Visibility API)

7. **Timezone Considerations:** Be aware that statistics are calculated based on server timezone. If your application needs client timezone support, you may need to pass timezone information in future API versions

8. **Favorites Management:** Remember that favorites are user-specific. When a user marks a contact as favorite, it only affects their own favorite count, not other users' counts

---

## Integration Example: Complete Dashboard

```javascript
import React, { useState, useEffect } from 'react';

function ContactDashboard() {
  const [statistics, setStatistics] = useState({
    total_contacts: 0,
    this_month: 0,
    recently_added: 0,
    favorites: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchStatistics = async () => {
    try {
      setLoading(true);
      setError(null);

      const token = localStorage.getItem('auth_token');
      const response = await fetch('http://your-domain/api/contacts/state', {
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
      console.error('Error fetching contact statistics:', err);
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
      <h2>Contact Statistics</h2>
      <div style={{ display: 'flex', gap: '20px', flexWrap: 'wrap' }}>
        <StatCard
          title="Total Contacts"
          value={statistics.total_contacts}
          icon="👥"
          color="#2c3e50"
        />
        <StatCard
          title="This Month"
          value={statistics.this_month}
          icon="📅"
          color="#3498db"
        />
        <StatCard
          title="Recently Add"
          value={statistics.recently_added}
          icon="➕"
          color="#9b59b6"
        />
        <StatCard
          title="Favorites"
          value={statistics.favorites}
          icon="❤️"
          color="#e74c3c"
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

export default ContactDashboard;
```

---

## Related Endpoints

- **Contact Index:** `POST /api/contacts/index` - Get paginated list of contacts
- **Contact Show:** `POST /api/contacts/show` - Get single contact by ID
- **Contact Save:** `POST /api/contacts/save` - Create or update a contact
- **Contact Delete:** `POST /api/contacts/delete` - Delete a contact
- **Contact Dropdown:** `POST /api/contacts/dropdown` - Get contacts dropdown list
- **Contact Favourite:** `POST /api/contacts/favourite` - Mark/unmark a contact as favorite

---

## Support

For issues or questions regarding this API endpoint, please contact the development team or refer to the main API documentation.

---

**Last Updated:** November 2025

