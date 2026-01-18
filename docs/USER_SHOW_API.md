# User Show API Documentation

## Overview
This API endpoint retrieves the authenticated user's profile information without quick statistics. It returns user profile details including name, email, phone, job title, department, profile picture, and joining date. This is a lightweight endpoint for when you only need profile information without dashboard statistics.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/user/show`  
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

This endpoint does not require any request body parameters. You can send an empty JSON object:

```json
{}
```

---

## Response Format

### Success Response (200)

```json
{
  "data": {
    "id": 1,
    "avatar": {
      "initials": "JD",
      "profile_picture": "https://example.com/profile.jpg"
    },
    "name": "John Doe",
    "job_title": "Product Manager",
    "department": "Engineering",
    "email": "john.doe@example.com",
    "phone": "+1 (555) 123-4567",
    "joining_date": "January 2023",
    "joined_text": "Joined January 2023",
    "created_at": "2023-01-15T10:30:00.000000Z",
    "updated_at": "2025-11-07T14:45:00.000000Z"
  },
  "message": "User profile retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | User profile information |
| `data.id` | integer | User ID |
| `data.avatar` | object | Avatar information |
| `data.avatar.initials` | string | Auto-generated initials from name (e.g., "JD") |
| `data.avatar.profile_picture` | string\|null | Profile picture URL or path |
| `data.name` | string | User's full name |
| `data.job_title` | string\|null | User's job title |
| `data.department` | string\|null | User's department |
| `data.email` | string | User's email address |
| `data.phone` | string\|null | User's phone number |
| `data.joining_date` | string\|null | Formatted joining date (e.g., "January 2023") |
| `data.joined_text` | string\|null | Formatted text with "Joined " prefix |
| `data.created_at` | string | Account creation timestamp (ISO 8601) |
| `data.updated_at` | string | Last update timestamp (ISO 8601) |
| `message` | string | Success message |

---

## Error Responses

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

### Server Error (500)

```json
{
  "message": "Server error message"
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get user profile
async function getUserProfile(token) {
  try {
    const response = await fetch('http://your-api-url/api/user/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve profile');
    }

    return data;
  } catch (error) {
    console.error('Error fetching profile:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const result = await getUserProfile(token);
console.log('Profile:', result.data);
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

// Get user profile
async function getUserProfile() {
  try {
    const response = await apiClient.post('/user/show', {});
    return response.data;
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await getUserProfile();
console.log('Profile:', result.data);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface Avatar {
  initials: string;
  profile_picture: string | null;
}

interface UserProfile {
  id: number;
  avatar: Avatar;
  name: string;
  job_title: string | null;
  department: string | null;
  email: string;
  phone: string | null;
  joining_date: string | null;
  joined_text: string | null;
  created_at: string;
  updated_at: string;
}

interface ProfileResponse {
  data: UserProfile;
  message: string;
}

export function useUserProfile(token: string) {
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchProfile = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<ProfileResponse>(
          '/api/user/show',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setProfile(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching profile:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      fetchProfile();
    }
  }, [token]);

  return { profile, loading, error };
}

// Usage in component
function UserProfilePage() {
  const token = localStorage.getItem('auth_token') || '';
  const { profile, loading, error } = useUserProfile(token);

  if (loading) return <div>Loading profile...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!profile) return null;

  return (
    <div>
      <div>
        {profile.avatar.profile_picture ? (
          <img src={profile.avatar.profile_picture} alt={profile.name} />
        ) : (
          <div style={{
            width: '100px',
            height: '100px',
            borderRadius: '50%',
            backgroundColor: '#4A90E2',
            color: 'white',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '32px',
            fontWeight: 'bold'
          }}>
            {profile.avatar.initials}
          </div>
        )}
      </div>
      <h1>{profile.name}</h1>
      {profile.job_title && <p>{profile.job_title}</p>}
      {profile.department && <p>{profile.department}</p>}
      <p>{profile.email}</p>
      {profile.phone && <p>{profile.phone}</p>}
      {profile.joined_text && <p>{profile.joined_text}</p>}
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState, useEffect } from 'react';

function UserProfileCard({ token }) {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchProfile();
  }, []);

  const fetchProfile = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/user/show', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({})
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to retrieve profile');
      }

      setProfile(data.data);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching profile:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div>Loading profile...</div>;
  }

  if (error) {
    return <div style={{ color: 'red' }}>Error: {error}</div>;
  }

  if (!profile) {
    return null;
  }

  return (
    <div style={{ padding: '20px', border: '1px solid #ddd', borderRadius: '8px' }}>
      {/* Avatar */}
      <div style={{ marginBottom: '20px' }}>
        {profile.avatar.profile_picture ? (
          <img
            src={profile.avatar.profile_picture}
            alt={profile.name}
            style={{
              width: '100px',
              height: '100px',
              borderRadius: '50%',
              objectFit: 'cover'
            }}
          />
        ) : (
          <div
            style={{
              width: '100px',
              height: '100px',
              borderRadius: '50%',
              backgroundColor: '#4A90E2',
              color: 'white',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '32px',
              fontWeight: 'bold'
            }}
          >
            {profile.avatar.initials}
          </div>
        )}
      </div>

      {/* Name */}
      <h3>{profile.name}</h3>

      {/* Job Title */}
      {profile.job_title && (
        <p style={{ color: '#666', marginBottom: '10px' }}>{profile.job_title}</p>
      )}

      {/* Department */}
      {profile.department && (
        <span
          style={{
            display: 'inline-block',
            padding: '4px 12px',
            backgroundColor: '#f0f0f0',
            borderRadius: '12px',
            fontSize: '14px',
            marginBottom: '15px'
          }}
        >
          {profile.department}
        </span>
      )}

      {/* Contact Information */}
      <div style={{ marginTop: '20px' }}>
        <p>
          <strong>Email:</strong> {profile.email}
        </p>
        {profile.phone && (
          <p>
            <strong>Phone:</strong> {profile.phone}
          </p>
        )}
        {profile.joined_text && (
          <p>
            <strong>{profile.joined_text}</strong>
          </p>
        )}
      </div>
    </div>
  );
}

export default UserProfileCard;
```

---

## cURL Examples

### Basic Request

```bash
curl -X POST "http://your-api-url/api/user/show" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### With Pretty Print

```bash
curl -X POST "http://your-api-url/api/user/show" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}' | json_pp
```

---

## Important Notes

### 1. Avatar Initials
- Avatar initials are automatically generated from the user's name
- Format: First letter of first name + First letter of last name (e.g., "John Doe" → "JD")
- If only one word in name, first two letters are used
- Initials are always uppercase

### 2. Profile Picture
- Can be a URL or file path
- If `profile_picture` is `null`, you can use the initials to display an avatar
- Consider using a service like UI Avatars or generating a colored circle with initials

### 3. Joining Date Format
- `joining_date`: Formatted as "Month Year" (e.g., "January 2023")
- `joined_text`: Includes "Joined " prefix (e.g., "Joined January 2023")
- Based on user's `created_at` timestamp

### 4. Difference from `/user/index`
- `/user/index`: Returns profile details + quick stats (meetings, contacts, hours, rating)
- `/user/show`: Returns only profile details (lighter response, faster)

### 5. Authentication
- User can only view their own profile
- The authenticated user's profile is automatically identified from the Bearer token
- No need to provide user ID in the request

### 6. Empty Request Body
- Request body can be empty `{}` or omitted
- No parameters are required for this endpoint

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Profile retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `500` | Server error |

---

## Error Handling Best Practices

1. **Check Response Status**: Always check the HTTP status code before processing the response
2. **Handle Network Errors**: Handle timeout and network connectivity issues
3. **Token Expiration**: Handle 401 errors by redirecting to login
4. **Null Values**: Handle null values for optional fields (phone, job_title, department, profile_picture)

Example error handling:

```javascript
try {
  const response = await fetch('/api/user/show', {...});
  const data = await response.json();
  
  if (!response.ok) {
    if (response.status === 401) {
      // Handle authentication error
      // Redirect to login
      window.location.href = '/login';
      return;
    } else {
      // Handle other errors
      console.error(data.message);
      alert(data.message);
      return;
    }
  }
  
  // Success
  const profile = data.data;
  
  // Handle null values
  const displayName = profile.name || 'Unknown User';
  const displayPhone = profile.phone || 'Not provided';
  const displayJobTitle = profile.job_title || 'No job title';
  
  console.log('Profile:', profile);
} catch (error) {
  console.error('Network error:', error);
  alert('Network error. Please try again.');
}
```

---

## Example Response Scenarios

### Scenario 1: Complete Profile

```json
{
  "data": {
    "id": 1,
    "avatar": {
      "initials": "JD",
      "profile_picture": "https://example.com/profile.jpg"
    },
    "name": "John Doe",
    "job_title": "Product Manager",
    "department": "Engineering",
    "email": "john.doe@example.com",
    "phone": "+1 (555) 123-4567",
    "joining_date": "January 2023",
    "joined_text": "Joined January 2023",
    "created_at": "2023-01-15T10:30:00.000000Z",
    "updated_at": "2025-11-07T14:45:00.000000Z"
  },
  "message": "User profile retrieved successfully."
}
```

### Scenario 2: Minimal Profile (No Optional Fields)

```json
{
  "data": {
    "id": 2,
    "avatar": {
      "initials": "JS",
      "profile_picture": null
    },
    "name": "Jane Smith",
    "job_title": null,
    "department": null,
    "email": "jane.smith@example.com",
    "phone": null,
    "joining_date": "March 2024",
    "joined_text": "Joined March 2024",
    "created_at": "2024-03-10T08:15:00.000000Z",
    "updated_at": "2024-03-10T08:15:00.000000Z"
  },
  "message": "User profile retrieved successfully."
}
```

### Scenario 3: User with Single Name

```json
{
  "data": {
    "id": 3,
    "avatar": {
      "initials": "MA",
      "profile_picture": null
    },
    "name": "Madonna",
    "job_title": "Artist",
    "department": "Entertainment",
    "email": "madonna@example.com",
    "phone": "+1 (555) 999-8888",
    "joining_date": "December 2022",
    "joined_text": "Joined December 2022",
    "created_at": "2022-12-01T12:00:00.000000Z",
    "updated_at": "2025-11-07T10:20:00.000000Z"
  },
  "message": "User profile retrieved successfully."
}
```

---

## UI Display Recommendations

### Avatar Display
- If `profile_picture` exists, display the image
- If `profile_picture` is null, display a colored circle with initials
- Use a consistent color scheme based on user ID or name hash
- Recommended size: 100px × 100px for profile pages

### Profile Information Layout
- Display name prominently (largest text)
- Show job title and department as secondary information
- Use tags/badges for department
- Format phone numbers consistently
- Display joining date with calendar icon

### Example UI Structure
```
┌─────────────────────────┐
│   [Avatar Circle]       │
│        JD               │
│                         │
│   John Doe              │
│   Product Manager       │
│   [Engineering]         │
│                         │
│   📧 john@example.com   │
│   📞 +1 (555) 123-4567  │
│   📅 Joined Jan 2023    │
└─────────────────────────┘
```

---

## Comparison with `/user/index`

| Feature | `/user/show` | `/user/index` |
|---------|--------------|---------------|
| **Response Size** | Smaller (profile only) | Larger (profile + stats) |
| **Response Time** | Faster | Slower (calculates stats) |
| **Use Case** | Simple profile display | Dashboard with statistics |
| **Quick Stats** | ❌ Not included | ✅ Included |
| **Profile Details** | ✅ Included | ✅ Included |

**When to use `/user/show`:**
- Simple profile page
- Edit profile form (pre-fill data)
- Header/navigation user info
- Settings page

**When to use `/user/index`:**
- Dashboard page
- Profile page with statistics
- Analytics view

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/user/show` with Bearer token and empty body `{}`
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

