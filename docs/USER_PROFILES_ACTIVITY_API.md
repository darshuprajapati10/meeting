# User Profiles Activity API Documentation

## Overview
This API endpoint retrieves recent activities for the authenticated user's profile, including scheduled meetings, completed meetings, contact updates, and contact additions. Activities are sorted by timestamp (most recent first) and limited to the 20 most recent items from the last 30 days. This endpoint is perfect for displaying a "Recent Activity" feed on user profile pages or dashboards.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/user-profiles/activity`  
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

### Success - Activities Retrieved (200)

```json
{
  "data": [
    {
      "type": "meeting_scheduled",
      "description": "Scheduled meeting with Alice Johnson",
      "timestamp": "2025-11-07T10:30:00.000000Z",
      "human_time": "2 hours ago"
    },
    {
      "type": "contact_updated",
      "description": "Updated contact information",
      "timestamp": "2025-11-06T14:20:00.000000Z",
      "human_time": "1 day ago"
    },
    {
      "type": "meeting_completed",
      "description": "Completed Project Review meeting",
      "timestamp": "2025-11-05T16:45:00.000000Z",
      "human_time": "2 days ago"
    },
    {
      "type": "contacts_added",
      "description": "Added 5 new contacts",
      "timestamp": "2025-11-04T09:15:00.000000Z",
      "human_time": "3 days ago"
    }
  ],
  "message": "User profile activities retrieved successfully."
}
```

### Success - No Activities (200)

```json
{
  "data": [],
  "message": "User profile activities retrieved successfully."
}
```

### Success - No Organization (200)

```json
{
  "data": [],
  "message": "No organization found. Please create an organization first."
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

### Activity Object

Each activity in the `data` array contains the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `type` | string | Type of activity (see Activity Types below) |
| `description` | string | Human-readable description of the activity |
| `timestamp` | string | ISO 8601 timestamp of when the activity occurred |
| `human_time` | string | Human-readable time difference (e.g., "2 hours ago", "1 day ago") |

---

## Activity Types

### meeting_scheduled
- **Description Format:** "Scheduled meeting with [Contact Name]"
- **Trigger:** When a meeting with status "Scheduled" is created by the authenticated user
- **Timestamp Source:** Meeting `created_at`
- **Contact Name:** First attendee's name (first_name + last_name), or "contact" if no attendees

### meeting_completed
- **Description Format:** "Completed [Meeting Title] meeting"
- **Trigger:** When a meeting status changes to "Completed"
- **Timestamp Source:** Meeting `updated_at`
- **Meeting Title:** The `meeting_title` field from the meeting

### contact_updated
- **Description Format:** "Updated contact information"
- **Trigger:** When a contact is updated (updated_at != created_at) by the authenticated user
- **Timestamp Source:** Contact `updated_at`
- **Note:** Only includes contacts where the authenticated user is the creator

### contacts_added
- **Description Format:** "Added X new contact(s)"
- **Trigger:** When one or more contacts are created by the authenticated user on the same day
- **Timestamp Source:** Latest contact's `created_at` from that day
- **Grouping:** Contacts created on the same day are grouped together
- **Count:** Shows the total number of contacts added on that day

---

## Human Time Format

The `human_time` field provides human-readable time differences:

| Time Difference | Format Example |
|----------------|----------------|
| Less than 1 minute | "30 seconds ago" |
| Less than 1 hour | "15 minutes ago" |
| Less than 1 day | "2 hours ago" |
| Less than 1 month | "3 days ago" |
| 1 month or more | "2 months ago" |

---

## HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | Activities retrieved successfully |
| `401` | Unauthenticated - Missing or invalid authentication token |

---

## Business Logic

1. **Authentication Required**: You must be authenticated with a valid Bearer token to access this endpoint.

2. **Organization Context**: All activities are scoped to the authenticated user's organization. If the user has no organization, an empty array is returned.

3. **Time Range**: Only activities from the last 30 days are included.

4. **Activity Sources**:
   - **Meetings**: Only includes meetings created by the authenticated user (`created_by` = user ID)
   - **Contacts**: Only includes contacts created by the authenticated user (`created_by` = user ID)

5. **Sorting**: All activities are sorted by timestamp in descending order (most recent first).

6. **Limit**: Maximum of 20 activities are returned.

7. **Contact Grouping**: Multiple contacts created on the same day are grouped into a single "Added X new contacts" activity.

8. **Contact Updates**: Only contacts that have been updated (updated_at != created_at) are included in the activity feed.

9. **Meeting Attendees**: For scheduled meetings, the first attendee's name is used in the description. If no attendees exist, "contact" is used.

---

## Example cURL Requests

### Retrieve User Profile Activities

```bash
curl -X POST "https://your-domain.com/api/user-profiles/activity" \
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
  "data": [
    {
      "type": "meeting_scheduled",
      "description": "Scheduled meeting with Alice Johnson",
      "timestamp": "2025-11-07T10:30:00.000000Z",
      "human_time": "2 hours ago"
    },
    {
      "type": "contact_updated",
      "description": "Updated contact information",
      "timestamp": "2025-11-06T14:20:00.000000Z",
      "human_time": "1 day ago"
    },
    {
      "type": "meeting_completed",
      "description": "Completed Project Review meeting",
      "timestamp": "2025-11-05T16:45:00.000000Z",
      "human_time": "2 days ago"
    },
    {
      "type": "contacts_added",
      "description": "Added 5 new contacts",
      "timestamp": "2025-11-04T09:15:00.000000Z",
      "human_time": "3 days ago"
    }
  ],
  "message": "User profile activities retrieved successfully."
}
```

---

## JavaScript/Fetch Example

### Retrieve User Profile Activities

```javascript
const getUserProfileActivities = async () => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/activity', {
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
      console.log('User profile activities:', data.data);
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
const activities = await getUserProfileActivities();
activities.data.forEach(activity => {
  console.log(`${activity.description} - ${activity.human_time}`);
});
```

### With Error Handling

```javascript
const getUserProfileActivities = async () => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/activity', {
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
    console.error('Failed to retrieve user profile activities:', error);
    throw error;
  }
};
```

---

## Axios Example

```javascript
import axios from 'axios';

const getUserProfileActivities = async (authToken) => {
  try {
    const response = await axios.post(
      'https://your-domain.com/api/user-profiles/activity',
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
      throw new Error(error.response.data.message || 'Failed to retrieve activities');
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
const activities = await getUserProfileActivities(yourAuthToken);
console.log('Activities:', activities.data);
```

---

## React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface Activity {
  type: string;
  description: string;
  timestamp: string;
  human_time: string;
}

interface UserProfileActivitiesResponse {
  data: Activity[];
  message: string;
}

export function useUserProfileActivities(authToken: string) {
  const [activities, setActivities] = useState<Activity[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchActivities = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<UserProfileActivitiesResponse>(
          'https://your-domain.com/api/user-profiles/activity',
          {},
          {
            headers: {
              'Authorization': `Bearer ${authToken}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        setActivities(response.data.data);
      } catch (err: any) {
        const errorMessage = err.response?.data?.message || 'Failed to fetch activities';
        setError(errorMessage);
        console.error('Error fetching activities:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchActivities();
  }, [authToken]);

  return { activities, loading, error };
}

// Usage in component
function RecentActivityFeed() {
  const authToken = localStorage.getItem('auth_token') || '';
  const { activities, loading, error } = useUserProfileActivities(authToken);

  if (loading) return <div>Loading activities...</div>;
  if (error) return <div>Error: {error}</div>;
  if (activities.length === 0) return <div>No recent activities</div>;

  return (
    <div className="activity-feed">
      <h2>Recent Activity</h2>
      <ul>
        {activities.map((activity, index) => (
          <li key={index} className="activity-item">
            <div className="activity-description">{activity.description}</div>
            <div className="activity-time">{activity.human_time}</div>
          </li>
        ))}
      </ul>
    </div>
  );
}
```

---

## PHP Example

```php
<?php

function getUserProfileActivities($authToken) {
    $url = 'https://your-domain.com/api/user-profiles/activity';
    
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
    
    $result = getUserProfileActivities($authToken);
    echo "Activities retrieved successfully!\n";
    
    foreach ($result['data'] as $activity) {
        echo "- " . $activity['description'] . " (" . $activity['human_time'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Python Example

```python
import requests
import json

def get_user_profile_activities(auth_token):
    url = 'https://your-domain.com/api/user-profiles/activity'
    
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
    
    result = get_user_profile_activities(auth_token)
    print("Activities retrieved successfully!")
    
    for activity in result['data']:
        print(f"- {activity['description']} ({activity['human_time']})")
except Exception as e:
    print(f"Error: {e}")
```

---

## Activity Type Details

### meeting_scheduled
- **When it appears:** When you create a meeting with status "Scheduled"
- **Description includes:** First attendee's name (if available)
- **Example:** "Scheduled meeting with Alice Johnson"

### meeting_completed
- **When it appears:** When a meeting's status changes to "Completed"
- **Description includes:** Meeting title
- **Example:** "Completed Project Review meeting"

### contact_updated
- **When it appears:** When you update a contact's information (any field changed)
- **Description:** Generic "Updated contact information"
- **Note:** Only shows contacts you created

### contacts_added
- **When it appears:** When you create one or more contacts
- **Description includes:** Count of contacts added on the same day
- **Grouping:** Multiple contacts created on the same day are grouped
- **Examples:** 
  - "Added 1 new contact" (single contact)
  - "Added 5 new contacts" (multiple contacts on same day)

---

## Use Cases

1. **Activity Feed**: Display recent user activities in a timeline or feed component
2. **Dashboard Widget**: Show recent activity summary on user dashboard
3. **Profile Page**: Display activity history on user profile pages
4. **Notifications**: Use activity data to generate notification summaries
5. **Analytics**: Track user engagement and activity patterns

---

## Notes

1. **Authentication**: You must be authenticated with a valid Bearer token to use this endpoint.

2. **Organization Dependency**: Activities are scoped to the authenticated user's organization. If the user has no organization, an empty array is returned.

3. **Time Range**: Only activities from the last 30 days are included. Older activities are not returned.

4. **Activity Limit**: Maximum of 20 activities are returned, sorted by most recent first.

5. **User-Specific**: Only activities created by the authenticated user are included. Activities from other users in the organization are not shown.

6. **Real-time Data**: Activities are calculated in real-time from the database, ensuring up-to-date information.

7. **Contact Grouping**: Multiple contacts created on the same day are automatically grouped into a single activity entry.

8. **Meeting Attendees**: For scheduled meetings, the description includes the first attendee's name. If a meeting has no attendees, "contact" is used as a placeholder.

9. **Update Detection**: Contact updates are detected by comparing `updated_at` with `created_at`. If they differ, it's considered an update.

10. **Performance**: The endpoint performs multiple database queries to gather activities from different sources. For high-traffic applications, consider caching the results.

---

## Related Endpoints

- `POST /api/user-profiles/index` - Get paginated list of user profiles
- `POST /api/user-profiles/show` - Get single user profile by ID
- `POST /api/user-profiles/save` - Create or update a user profile
- `POST /api/user-profiles/delete` - Delete a user profile
- `POST /api/user-profiles/state` - Get user profile quick statistics

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main API documentation.

