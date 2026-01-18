# User Profiles Show API Documentation

## Overview
This API endpoint allows you to retrieve a single user profile by its ID from the `user_profiles` table. The profile must belong to the authenticated user's organization. This endpoint is useful for viewing detailed information about a specific user profile.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/user-profiles/show`  
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

### Required Fields

```json
{
  "id": 1
}
```

**Note:** The `id` must be an integer and must exist in the `user_profiles` table. Additionally, the profile must belong to the authenticated user's organization.

---

## Request Parameters

| Parameter | Type | Required | Description | Constraints |
|-----------|------|----------|-------------|-------------|
| `id` | integer | Yes | The ID of the user profile to retrieve | Must exist in `user_profiles` table and belong to user's organization |

---

## Response Examples

### Success - User Profile Retrieved (200)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "bio": "Software developer with 5 years of experience",
    "email_address": "john.doe@example.com",
    "address": "123 Main Street, City, State 12345",
    "company": "Tech Solutions Inc.",
    "phone": "+1 (555) 123-4567",
    "job_title": "Senior Developer",
    "department": "Engineering",
    "timezone": "America/New_York",
    "created_at": "2025-11-07T11:12:31.000000Z",
    "updated_at": "2025-11-07T11:12:31.000000Z"
  },
  "message": "User profile retrieved successfully."
}
```

### Error - Missing ID (422)

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

### Error - Invalid ID Format (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "id": [
      "The id must be an integer."
    ]
  }
}
```

### Error - User Profile Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\UserProfile] 999"
}
```

**Note:** This error occurs when:
- The provided ID does not exist in the `user_profiles` table, OR
- The profile exists but does not belong to the authenticated user's organization

### Error - No Organization Found (404)

```json
{
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

The response includes the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique identifier for the user profile |
| `organization_id` | integer | ID of the organization this profile belongs to |
| `user_id` | integer | ID of the user this profile belongs to |
| `first_name` | string | User's first name |
| `last_name` | string | User's last name |
| `bio` | string \| null | User's biography/description |
| `email_address` | string | User's email address (unique) |
| `address` | string | User's address |
| `company` | string | User's company name |
| `phone` | string \| null | User's phone number |
| `job_title` | string \| null | User's job title |
| `department` | string \| null | User's department |
| `timezone` | string \| null | User's timezone |
| `created_at` | string | ISO 8601 timestamp of when the profile was created |
| `updated_at` | string | ISO 8601 timestamp of when the profile was last updated |

---

## HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | User profile retrieved successfully |
| `401` | Unauthenticated - Missing or invalid authentication token |
| `404` | User profile not found, or no organization found |
| `422` | Validation error - Invalid or missing required fields |

---

## Business Logic

1. **Authentication Required**: You must be authenticated with a valid Bearer token to access this endpoint.

2. **Organization Context**: The user profile must belong to the authenticated user's organization. You cannot retrieve profiles from other organizations.

3. **ID Validation**: 
   - The `id` must be provided in the request body
   - The `id` must be an integer
   - The `id` must exist in the `user_profiles` table
   - The profile with this `id` must belong to your organization

4. **Organization Check**: If the authenticated user has no organization, the request will fail with a 404 error.

5. **Access Control**: This endpoint enforces organization-level access control. Users can only view profiles within their own organization.

---

## Example cURL Requests

### Retrieve User Profile

```bash
curl -X POST "https://your-domain.com/api/user-profiles/show" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1
  }'
```

### Expected Response

```bash
HTTP/1.1 200 OK
Content-Type: application/json

{
  "data": {
    "id": 1,
    "organization_id": 1,
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "bio": "Software developer with 5 years of experience",
    "email_address": "john.doe@example.com",
    "address": "123 Main Street, City, State 12345",
    "company": "Tech Solutions Inc.",
    "phone": "+1 (555) 123-4567",
    "job_title": "Senior Developer",
    "department": "Engineering",
    "timezone": "America/New_York",
    "created_at": "2025-11-07T11:12:31.000000Z",
    "updated_at": "2025-11-07T11:12:31.000000Z"
  },
  "message": "User profile retrieved successfully."
}
```

---

## JavaScript/Fetch Example

### Retrieve User Profile

```javascript
const getUserProfile = async (profileId) => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: profileId
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('User profile retrieved:', data.data);
      return data;
    } else {
      console.error('Error:', data.message);
      if (data.errors) {
        console.error('Validation errors:', data.errors);
      }
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Request failed:', error);
    throw error;
  }
};

// Usage
const profile = await getUserProfile(1);
console.log('Profile:', profile.data);
```

### With Error Handling

```javascript
const getUserProfile = async (profileId) => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ id: profileId })
    });

    const data = await response.json();
    
    if (!response.ok) {
      // Handle different error types
      if (response.status === 404) {
        throw new Error('User profile not found or you do not have access to it.');
      } else if (response.status === 422) {
        throw new Error(`Validation error: ${JSON.stringify(data.errors)}`);
      } else if (response.status === 401) {
        throw new Error('Authentication required. Please login again.');
      } else {
        throw new Error(data.message || 'An error occurred');
      }
    }
    
    return data;
  } catch (error) {
    console.error('Failed to retrieve user profile:', error);
    throw error;
  }
};
```

---

## Axios Example

```javascript
import axios from 'axios';

const getUserProfile = async (profileId, authToken) => {
  try {
    const response = await axios.post(
      'https://your-domain.com/api/user-profiles/show',
      { id: profileId },
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
      // Server responded with error status
      console.error('Error response:', error.response.data);
      throw new Error(error.response.data.message || 'Failed to retrieve user profile');
    } else if (error.request) {
      // Request made but no response received
      console.error('No response received:', error.request);
      throw new Error('No response from server');
    } else {
      // Error setting up request
      console.error('Error:', error.message);
      throw error;
    }
  }
};

// Usage
const profile = await getUserProfile(1, yourAuthToken);
console.log('Profile:', profile.data);
```

---

## React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface UserProfile {
  id: number;
  organization_id: number;
  user_id: number;
  first_name: string;
  last_name: string;
  bio: string | null;
  email_address: string;
  address: string;
  company: string;
  phone: string | null;
  job_title: string | null;
  department: string | null;
  timezone: string | null;
  created_at: string;
  updated_at: string;
}

interface UserProfileResponse {
  data: UserProfile;
  message: string;
}

export function useUserProfile(profileId: number | null, authToken: string) {
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!profileId) {
      return;
    }

    const fetchProfile = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<UserProfileResponse>(
          'https://your-domain.com/api/user-profiles/show',
          { id: profileId },
          {
            headers: {
              'Authorization': `Bearer ${authToken}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        setProfile(response.data.data);
      } catch (err: any) {
        const errorMessage = err.response?.data?.message || 'Failed to fetch user profile';
        setError(errorMessage);
        console.error('Error fetching profile:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchProfile();
  }, [profileId, authToken]);

  return { profile, loading, error };
}

// Usage in component
function ProfileView({ profileId }: { profileId: number }) {
  const authToken = localStorage.getItem('auth_token') || '';
  const { profile, loading, error } = useUserProfile(profileId, authToken);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!profile) return <div>No profile found</div>;

  return (
    <div>
      <h1>{profile.first_name} {profile.last_name}</h1>
      <p>Email: {profile.email_address}</p>
      <p>Company: {profile.company}</p>
      <p>Job Title: {profile.job_title}</p>
      {profile.bio && <p>Bio: {profile.bio}</p>}
    </div>
  );
}
```

---

## PHP Example

```php
<?php

function getUserProfile($profileId, $authToken) {
    $url = 'https://your-domain.com/api/user-profiles/show';
    
    $data = [
        'id' => $profileId
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
    $profileId = 1;
    
    $result = getUserProfile($profileId, $authToken);
    echo "Profile retrieved successfully!\n";
    print_r($result['data']);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Python Example

```python
import requests
import json

def get_user_profile(profile_id, auth_token):
    url = 'https://your-domain.com/api/user-profiles/show'
    
    headers = {
        'Authorization': f'Bearer {auth_token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    data = {
        'id': profile_id
    }
    
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
    profile_id = 1
    
    result = get_user_profile(profile_id, auth_token)
    print("Profile retrieved successfully!")
    print(json.dumps(result['data'], indent=2))
except Exception as e:
    print(f"Error: {e}")
```

---

## Notes

1. **Authentication**: You must be authenticated with a valid Bearer token to use this endpoint.

2. **Organization Context**: You can only retrieve user profiles that belong to your organization. Attempting to retrieve a profile from another organization will result in a 404 error.

3. **ID Format**: The `id` must be provided as an integer in the request body, not as a URL parameter.

4. **Error Handling**: Always check the HTTP status code and handle errors appropriately. Common errors include:
   - 401: Authentication required
   - 404: Profile not found or doesn't belong to your organization
   - 422: Validation error (missing or invalid ID)

5. **Performance**: This endpoint performs a single database query to retrieve the profile. It's optimized for quick lookups.

6. **Data Privacy**: The endpoint enforces organization-level access control, ensuring users can only view profiles within their own organization.

---

## Related Endpoints

- `POST /api/user-profiles/index` - Get paginated list of user profiles
- `POST /api/user-profiles/save` - Create or update a user profile
- `POST /api/user-profiles/delete` - Delete a user profile

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main API documentation.

