# User Profiles Index API Documentation

## Overview
This API endpoint allows you to retrieve a paginated list of user profiles from the `user_profiles` table. The profiles are filtered by the authenticated user's organization, ensuring users can only view profiles within their own organization. This endpoint supports pagination with customizable page size and page number.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/user-profiles/index`  
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

### Basic Request (Default Pagination)

```json
{}
```

This will return the first page with 15 items per page (default).

### With Pagination Parameters

```json
{
  "page": 1,
  "per_page": 20
}
```

### Request with Custom Page

```json
{
  "page": 2,
  "per_page": 10
}
```

---

## Request Parameters

| Parameter | Type | Required | Description | Constraints |
|-----------|------|----------|-------------|-------------|
| `page` | integer | No | Page number to retrieve | Default: 1, Minimum: 1 |
| `per_page` | integer | No | Number of items per page | Default: 15, Minimum: 1, Maximum: 100 |

**Note:** 
- If `per_page` exceeds 100, it will be automatically capped at 100.
- If `per_page` is less than 1, it will be set to 1.
- If `page` is less than 1, it will be set to 1.
- If the requested page exceeds the last page, the API will automatically return the last available page.

---

## Response Examples

### Success - User Profiles Retrieved (200)

```json
{
  "data": [
    {
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
    {
      "id": 2,
      "organization_id": 1,
      "user_id": 2,
      "first_name": "Jane",
      "last_name": "Smith",
      "bio": "Product manager with expertise in agile methodologies",
      "email_address": "jane.smith@example.com",
      "address": "456 Oak Avenue, City, State 12345",
      "company": "Tech Solutions Inc.",
      "phone": "+1 (555) 987-6543",
      "job_title": "Product Manager",
      "department": "Product",
      "timezone": "America/Los_Angeles",
      "created_at": "2025-11-07T10:30:15.000000Z",
      "updated_at": "2025-11-07T10:30:15.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 42
  },
  "message": "User profiles retrieved successfully."
}
```

### Success - Empty Results (200)

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
  "message": "User profiles retrieved successfully."
}
```

### Success - No Organization Found (200)

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

### Data Array

The `data` array contains user profile objects with the following structure:

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

### Meta Object

The `meta` object contains pagination information:

| Field | Type | Description |
|-------|------|-------------|
| `current_page` | integer | Current page number |
| `from` | integer \| null | Starting record number for current page (null if no records) |
| `last_page` | integer | Total number of pages available |
| `per_page` | integer | Number of items per page |
| `to` | integer \| null | Ending record number for current page (null if no records) |
| `total` | integer | Total number of user profiles in the organization |

---

## HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | User profiles retrieved successfully |
| `401` | Unauthenticated - Missing or invalid authentication token |

---

## Business Logic

1. **Authentication Required**: You must be authenticated with a valid Bearer token to access this endpoint.

2. **Organization Context**: Only user profiles belonging to the authenticated user's organization are returned. You cannot view profiles from other organizations.

3. **Pagination**:
   - Default page size is 15 items per page
   - Maximum page size is 100 items per page
   - Minimum page size is 1 item per page
   - Page numbers start at 1
   - If you request a page beyond the last page, the API automatically returns the last available page

4. **Ordering**: User profiles are ordered by `created_at` in descending order (newest first).

5. **Empty Results**: If there are no user profiles in your organization, the API returns an empty array with appropriate pagination metadata.

6. **Organization Check**: If the authenticated user has no organization, the API returns an empty result set with a message indicating no organization was found.

---

## Example cURL Requests

### Basic Request (Default Pagination)

```bash
curl -X POST "https://your-domain.com/api/user-profiles/index" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### Request with Pagination

```bash
curl -X POST "https://your-domain.com/api/user-profiles/index" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 20
  }'
```

### Request for Second Page

```bash
curl -X POST "https://your-domain.com/api/user-profiles/index" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 2,
    "per_page": 10
  }'
```

---

## JavaScript/Fetch Example

### Basic Request

```javascript
const getUserProfiles = async (page = 1, perPage = 15) => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/index', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        page: page,
        per_page: perPage
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('User profiles retrieved:', data.data);
      console.log('Pagination info:', data.meta);
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
const result = await getUserProfiles(1, 20);
console.log(`Retrieved ${result.data.length} profiles out of ${result.meta.total} total`);
```

### With Pagination Helper

```javascript
const getUserProfilesPaginated = async (page = 1, perPage = 15) => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/index', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        page: Math.max(1, page),
        per_page: Math.min(100, Math.max(1, perPage))
      })
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve user profiles');
    }
    
    return {
      profiles: data.data,
      pagination: {
        currentPage: data.meta.current_page,
        lastPage: data.meta.last_page,
        perPage: data.meta.per_page,
        total: data.meta.total,
        from: data.meta.from,
        to: data.meta.to,
        hasMore: data.meta.current_page < data.meta.last_page
      }
    };
  } catch (error) {
    console.error('Failed to retrieve user profiles:', error);
    throw error;
  }
};

// Usage
const result = await getUserProfilesPaginated(1, 20);
console.log(`Page ${result.pagination.currentPage} of ${result.pagination.lastPage}`);
console.log(`Showing ${result.pagination.from}-${result.pagination.to} of ${result.pagination.total}`);
```

---

## Axios Example

```javascript
import axios from 'axios';

const getUserProfiles = async (page = 1, perPage = 15, authToken) => {
  try {
    const response = await axios.post(
      'https://your-domain.com/api/user-profiles/index',
      {
        page: page,
        per_page: perPage
      },
      {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );
    
    return {
      profiles: response.data.data,
      pagination: response.data.meta
    };
  } catch (error) {
    if (error.response) {
      console.error('Error response:', error.response.data);
      throw new Error(error.response.data.message || 'Failed to retrieve user profiles');
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
const { profiles, pagination } = await getUserProfiles(1, 20, yourAuthToken);
console.log(`Retrieved ${profiles.length} profiles`);
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

interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
}

interface UserProfilesResponse {
  data: UserProfile[];
  meta: PaginationMeta;
  message: string;
}

export function useUserProfiles(page: number = 1, perPage: number = 15, authToken: string) {
  const [profiles, setProfiles] = useState<UserProfile[]>([]);
  const [pagination, setPagination] = useState<PaginationMeta | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchProfiles = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<UserProfilesResponse>(
          'https://your-domain.com/api/user-profiles/index',
          {
            page: Math.max(1, page),
            per_page: Math.min(100, Math.max(1, perPage))
          },
          {
            headers: {
              'Authorization': `Bearer ${authToken}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        setProfiles(response.data.data);
        setPagination(response.data.meta);
      } catch (err: any) {
        const errorMessage = err.response?.data?.message || 'Failed to fetch user profiles';
        setError(errorMessage);
        console.error('Error fetching profiles:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchProfiles();
  }, [page, perPage, authToken]);

  return { profiles, pagination, loading, error };
}

// Usage in component
function UserProfilesList() {
  const [currentPage, setCurrentPage] = useState(1);
  const authToken = localStorage.getItem('auth_token') || '';
  const { profiles, pagination, loading, error } = useUserProfiles(currentPage, 15, authToken);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <h1>User Profiles ({pagination?.total || 0})</h1>
      
      {profiles.length === 0 ? (
        <p>No user profiles found.</p>
      ) : (
        <>
          <ul>
            {profiles.map(profile => (
              <li key={profile.id}>
                {profile.first_name} {profile.last_name} - {profile.email_address}
              </li>
            ))}
          </ul>
          
          {pagination && (
            <div>
              <button 
                onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                disabled={currentPage === 1}
              >
                Previous
              </button>
              <span>Page {pagination.current_page} of {pagination.last_page}</span>
              <button 
                onClick={() => setCurrentPage(p => Math.min(pagination.last_page, p + 1))}
                disabled={currentPage === pagination.last_page}
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
```

---

## PHP Example

```php
<?php

function getUserProfiles($page = 1, $perPage = 15, $authToken) {
    $url = 'https://your-domain.com/api/user-profiles/index';
    
    $data = [
        'page' => max(1, (int)$page),
        'per_page' => min(100, max(1, (int)$perPage))
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
    $page = 1;
    $perPage = 20;
    
    $result = getUserProfiles($page, $perPage, $authToken);
    
    echo "Retrieved " . count($result['data']) . " profiles\n";
    echo "Total: " . $result['meta']['total'] . "\n";
    echo "Page " . $result['meta']['current_page'] . " of " . $result['meta']['last_page'] . "\n";
    
    foreach ($result['data'] as $profile) {
        echo "- " . $profile['first_name'] . " " . $profile['last_name'] . "\n";
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

def get_user_profiles(page=1, per_page=15, auth_token=None):
    url = 'https://your-domain.com/api/user-profiles/index'
    
    headers = {
        'Authorization': f'Bearer {auth_token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    data = {
        'page': max(1, int(page)),
        'per_page': min(100, max(1, int(per_page)))
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
    page = 1
    per_page = 20
    
    result = get_user_profiles(page, per_page, auth_token)
    
    print(f"Retrieved {len(result['data'])} profiles")
    print(f"Total: {result['meta']['total']}")
    print(f"Page {result['meta']['current_page']} of {result['meta']['last_page']}")
    
    for profile in result['data']:
        print(f"- {profile['first_name']} {profile['last_name']}")
except Exception as e:
    print(f"Error: {e}")
```

---

## Pagination Best Practices

1. **Page Size**: Choose an appropriate page size based on your UI needs. Common values are 10, 15, 20, 25, or 50 items per page.

2. **Page Navigation**: Always check `meta.last_page` to determine if there are more pages available.

3. **Empty States**: Check if `data` array is empty and `meta.total` is 0 to show appropriate empty state messages.

4. **Loading States**: Show loading indicators while fetching data, especially when changing pages.

5. **Error Handling**: Handle cases where the API returns errors, especially 401 (unauthorized) errors.

6. **Caching**: Consider caching results for better performance, especially if data doesn't change frequently.

---

## Notes

1. **Authentication**: You must be authenticated with a valid Bearer token to use this endpoint.

2. **Organization Context**: Only user profiles belonging to your organization are returned. You cannot view profiles from other organizations.

3. **Pagination Limits**: 
   - Maximum `per_page` is 100
   - Minimum `per_page` is 1
   - Page numbers start at 1

4. **Automatic Page Correction**: If you request a page beyond the last page, the API automatically returns the last available page.

5. **Ordering**: Results are ordered by `created_at` in descending order (newest first).

6. **Performance**: The endpoint is optimized for pagination and performs efficient database queries.

7. **Empty Results**: If there are no profiles in your organization, you'll receive an empty array with appropriate pagination metadata.

---

## Related Endpoints

- `POST /api/user-profiles/show` - Get single user profile by ID
- `POST /api/user-profiles/save` - Create or update a user profile
- `POST /api/user-profiles/delete` - Delete a user profile

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main API documentation.

