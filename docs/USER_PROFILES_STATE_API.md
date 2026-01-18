# User Profiles State API Documentation

## Overview
This API endpoint retrieves quick statistics for the authenticated user's profile, including meetings this month, total contacts, hours scheduled, and meeting rating. This endpoint is perfect for displaying dashboard statistics cards showing user profile overview metrics. All statistics are scoped to the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/user-profiles/state`  
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

## Response Examples

### Success - Statistics Retrieved (200)

```json
{
  "data": {
    "meetings_this_month": 12,
    "total_contacts": 45,
    "hours_scheduled": 24.5,
    "meeting_rating": 4.8
  },
  "message": "User profile statistics retrieved successfully."
}
```

### Success - No Organization (200)

```json
{
  "data": {
    "meetings_this_month": 0,
    "total_contacts": 0,
    "hours_scheduled": 0,
    "meeting_rating": 4.8
  },
  "message": "User profile statistics retrieved successfully."
}
```

### Error - Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

---

## Response Data Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Statistics data object |
| `data.meetings_this_month` | integer | Count of meetings scheduled in the current month (excluding cancelled meetings) |
| `data.total_contacts` | integer | Total count of all contacts in user's organization |
| `data.hours_scheduled` | float | Total hours scheduled across all meetings (calculated from meeting durations in minutes) |
| `data.meeting_rating` | float | Average meeting rating (default: 4.8) |
| `message` | string | Success message |

---

## Field Specifications

### meetings_this_month
- **Type:** `integer`
- **Description:** Count of meetings scheduled in the current month for the authenticated user's organization
- **Calculation:** 
  - Includes meetings with dates between the first and last day of the current month
  - Excludes meetings with status "Cancelled"
  - Scoped to the authenticated user's organization
- **Example:** `12`, `25`, `0`
- **Note:** Returns `0` if user has no organization or no meetings in the current month

### total_contacts
- **Type:** `integer`
- **Description:** Total count of all contacts in the authenticated user's organization
- **Calculation:** 
  - Counts all contacts regardless of status, group, or other filters
  - Scoped to the authenticated user's organization
- **Example:** `45`, `100`, `250`
- **Note:** Returns `0` if user has no organization or no contacts

### hours_scheduled
- **Type:** `float`
- **Description:** Total hours scheduled across all meetings in the user's organization
- **Calculation:** 
  - Sums all meeting durations (stored in minutes)
  - Converts total minutes to hours (divides by 60)
  - Rounds to 1 decimal place
  - Excludes cancelled meetings
  - Scoped to the authenticated user's organization
- **Example:** `24.5`, `120.0`, `8.3`
- **Note:** Returns `0` if user has no organization or no meetings

### meeting_rating
- **Type:** `float`
- **Description:** Average meeting rating
- **Default Value:** `4.8`
- **Example:** `4.8`
- **Note:** Currently returns a default value. Can be enhanced later with an actual rating system based on meeting feedback or surveys.

---

## HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | Statistics retrieved successfully |
| `401` | Unauthenticated - Missing or invalid authentication token |

---

## Business Logic

1. **Authentication Required**: You must be authenticated with a valid Bearer token to access this endpoint.

2. **Organization Context**: All statistics are calculated based on the authenticated user's first organization. If the user has no organization, all counts will return `0` (except `meeting_rating` which has a default value).

3. **Meetings This Month**:
   - Calculated from the first day to the last day of the current month
   - Only includes meetings with status other than "Cancelled"
   - Based on the meeting `date` field

4. **Total Contacts**:
   - Counts all contacts in the user's organization
   - No filters applied (includes all contacts regardless of status)

5. **Hours Scheduled**:
   - Calculated from the sum of all meeting `duration` fields (in minutes)
   - Converted to hours and rounded to 1 decimal place
   - Excludes cancelled meetings

6. **Meeting Rating**:
   - Currently returns a default value of `4.8`
   - Can be enhanced in the future to calculate from actual meeting feedback or survey responses

7. **Real-time Data**: All statistics are calculated in real-time from the database, ensuring up-to-date information.

---

## Example cURL Requests

### Basic Request

```bash
curl -X POST "https://your-domain.com/api/user-profiles/state" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### Expected Response

```bash
HTTP/1.1 200 OK
Content-Type: application/json

{
  "data": {
    "meetings_this_month": 12,
    "total_contacts": 45,
    "hours_scheduled": 24.5,
    "meeting_rating": 4.8
  },
  "message": "User profile statistics retrieved successfully."
}
```

---

## JavaScript/Fetch Example

### Retrieve User Profile Statistics

```javascript
const getUserProfileStats = async () => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/state', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('User profile statistics:', data.data);
      return data;
    } else {
      console.error('Error:', data.message);
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Request failed:', error);
    throw error;
  }
};

// Usage
const stats = await getUserProfileStats();
console.log(`Meetings this month: ${stats.data.meetings_this_month}`);
console.log(`Total contacts: ${stats.data.total_contacts}`);
console.log(`Hours scheduled: ${stats.data.hours_scheduled}`);
console.log(`Meeting rating: ${stats.data.meeting_rating}`);
```

### With Error Handling

```javascript
const getUserProfileStats = async () => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/state', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const data = await response.json();
    
    if (!response.ok) {
      if (response.status === 401) {
        throw new Error('Authentication required. Please login again.');
      } else {
        throw new Error(data.message || 'An error occurred');
      }
    }
    
    return data;
  } catch (error) {
    console.error('Failed to retrieve user profile statistics:', error);
    throw error;
  }
};
```

---

## Axios Example

```javascript
import axios from 'axios';

const getUserProfileStats = async (authToken) => {
  try {
    const response = await axios.post(
      'https://your-domain.com/api/user-profiles/state',
      {},
      {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );
    
    return response.data;
  } catch (error) {
    if (error.response) {
      console.error('Error response:', error.response.data);
      throw new Error(error.response.data.message || 'Failed to retrieve statistics');
    } else if (error.request) {
      console.error('No response received:', error.request);
      throw new Error('No response from server');
    } else {
      console.error('Error:', error.message);
      throw error;
    }
  }
};

// Usage
const stats = await getUserProfileStats(yourAuthToken);
console.log('Statistics:', stats.data);
```

---

## React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface UserProfileStats {
  meetings_this_month: number;
  total_contacts: number;
  hours_scheduled: number;
  meeting_rating: number;
}

interface UserProfileStatsResponse {
  data: UserProfileStats;
  message: string;
}

export function useUserProfileStats(authToken: string) {
  const [stats, setStats] = useState<UserProfileStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchStats = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<UserProfileStatsResponse>(
          'https://your-domain.com/api/user-profiles/state',
          {},
          {
            headers: {
              'Authorization': `Bearer ${authToken}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        setStats(response.data.data);
      } catch (err: any) {
        const errorMessage = err.response?.data?.message || 'Failed to fetch statistics';
        setError(errorMessage);
        console.error('Error fetching statistics:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [authToken]);

  return { stats, loading, error };
}

// Usage in component
function QuickStatsCard() {
  const authToken = localStorage.getItem('auth_token') || '';
  const { stats, loading, error } = useUserProfileStats(authToken);

  if (loading) return <div>Loading statistics...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!stats) return <div>No statistics available</div>;

  return (
    <div className="quick-stats-card">
      <h2>Quick Stats</h2>
      <div className="stats-grid">
        <div className="stat-item">
          <div className="stat-icon">📅</div>
          <div className="stat-label">Meetings This Month</div>
          <div className="stat-value">{stats.meetings_this_month}</div>
        </div>
        <div className="stat-item">
          <div className="stat-icon">👥</div>
          <div className="stat-label">Total Contacts</div>
          <div className="stat-value">{stats.total_contacts}</div>
        </div>
        <div className="stat-item">
          <div className="stat-icon">⏰</div>
          <div className="stat-label">Hours Scheduled</div>
          <div className="stat-value">{stats.hours_scheduled}</div>
        </div>
        <div className="stat-item">
          <div className="stat-icon">⭐</div>
          <div className="stat-label">Meeting Rating</div>
          <div className="stat-value">{stats.meeting_rating}</div>
        </div>
      </div>
    </div>
  );
}
```

---

## PHP Example

```php
<?php

function getUserProfileStats($authToken) {
    $url = 'https://your-domain.com/api/user-profiles/state';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $authToken,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL Error: " . $error);
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200) {
        return $result;
    } else {
        throw new Exception("API Error: " . ($result['message'] ?? 'Unknown error'));
    }
}

// Usage
try {
    $authToken = 'your-auth-token';
    
    $result = getUserProfileStats($authToken);
    echo "Statistics retrieved successfully!\n";
    echo "Meetings this month: " . $result['data']['meetings_this_month'] . "\n";
    echo "Total contacts: " . $result['data']['total_contacts'] . "\n";
    echo "Hours scheduled: " . $result['data']['hours_scheduled'] . "\n";
    echo "Meeting rating: " . $result['data']['meeting_rating'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Python Example

```python
import requests
import json

def get_user_profile_stats(auth_token):
    url = 'https://your-domain.com/api/user-profiles/state'
    
    headers = {
        'Authorization': f'Bearer {auth_token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    data = {}
    
    try:
        response = requests.post(url, headers=headers, json=data)
        response.raise_for_status()
        
        result = response.json()
        return result
    except requests.exceptions.HTTPError as e:
        error_data = e.response.json() if e.response else {}
        raise Exception(f"API Error: {error_data.get('message', 'Unknown error')}")
    except requests.exceptions.RequestException as e:
        raise Exception(f"Request failed: {str(e)}")

# Usage
try:
    auth_token = 'your-auth-token'
    
    result = get_user_profile_stats(auth_token)
    print("Statistics retrieved successfully!")
    print(f"Meetings this month: {result['data']['meetings_this_month']}")
    print(f"Total contacts: {result['data']['total_contacts']}")
    print(f"Hours scheduled: {result['data']['hours_scheduled']}")
    print(f"Meeting rating: {result['data']['meeting_rating']}")
except Exception as e:
    print(f"Error: {e}")
```

---

## Use Cases

1. **Dashboard Widgets**: Display quick statistics cards on the user dashboard
2. **Profile Overview**: Show user activity summary on profile pages
3. **Performance Metrics**: Track user engagement and activity levels
4. **Reporting**: Use statistics for generating user activity reports
5. **Analytics**: Monitor trends in meetings, contacts, and scheduling

---

## Notes

1. **Authentication**: You must be authenticated with a valid Bearer token to use this endpoint.

2. **Organization Dependency**: Statistics are calculated based on the authenticated user's organization. If the user has no organization, most statistics will return `0`.

3. **Real-time Calculation**: All statistics are calculated in real-time from the database, ensuring accurate and up-to-date information.

4. **Performance**: The endpoint performs multiple database queries to calculate statistics. For high-traffic applications, consider caching the results.

5. **Meeting Rating**: Currently returns a default value of `4.8`. This can be enhanced in the future to calculate from actual meeting feedback, survey responses, or other rating mechanisms.

6. **Date Range**: "Meetings This Month" uses the current calendar month (first day to last day of the month).

7. **Cancelled Meetings**: Cancelled meetings are excluded from both "Meetings This Month" and "Hours Scheduled" calculations.

8. **Hours Calculation**: Hours are calculated by summing all meeting durations (in minutes) and converting to hours, rounded to 1 decimal place.

---

## Related Endpoints

- `POST /api/user-profiles/index` - Get paginated list of user profiles
- `POST /api/user-profiles/show` - Get single user profile by ID
- `POST /api/user-profiles/save` - Create or update a user profile
- `POST /api/user-profiles/delete` - Delete a user profile
- `POST /api/contacts/state` - Get contact statistics
- `POST /api/calendar/state` - Get calendar statistics

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main API documentation.

