# Organizations Show API Documentation

## Overview
This API endpoint allows you to retrieve a single organization by its ID. The organization must belong to the authenticated user (the user must be a member of the organization). This endpoint is useful for viewing detailed information about a specific organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/organizations/show`  
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

**Note:** The `id` must be an integer and must exist in the `organizations` table. Additionally, the authenticated user must be a member of the organization.

---

## Request Parameters

| Parameter | Type | Required | Description | Constraints |
|-----------|------|----------|-------------|-------------|
| `id` | integer | Yes | The ID of the organization to retrieve | Must exist in `organizations` table and user must be a member |

---

## Response Examples

### Success - Organization Retrieved (200)

```json
{
  "data": {
    "id": 1,
    "type": "business",
    "name": "Tech Solutions Inc.",
    "slug": "tech-solutions-inc-1",
    "description": "A leading technology solutions provider",
    "email": "contact@techsolutions.com",
    "phone": "+1 (555) 123-4567",
    "address": "123 Main Street, City, State 12345",
    "gst_status": "registered",
    "gst_in": "27ABCDE1234F1Z5",
    "place_of_supply": "Maharashtra",
    "shipping_address": "123 Main Street",
    "shipping_city": "Mumbai",
    "shipping_zip": "400001",
    "shipping_phone": "+1 (555) 123-4567",
    "billing_address": "123 Main Street",
    "billing_city": "Mumbai",
    "billing_zip": "400001",
    "billing_phone": "+1 (555) 123-4567",
    "status": "active",
    "created_at": "2025-11-07T10:30:00.000000Z",
    "updated_at": "2025-11-07T14:45:00.000000Z",
    "deleted_at": null
  },
  "message": "Organization retrieved successfully."
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
      "The id must be an integer.",
      "The selected id is invalid."
    ]
  }
}
```

### Error - Organization Not Found (404)

```json
{
  "message": "Organization not found or you do not have permission to view it."
}
```

**Note:** This error occurs when:
- The provided ID does not exist in the `organizations` table, OR
- The organization exists but the authenticated user is not a member of it

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
| `id` | integer | Unique identifier for the organization |
| `type` | string | Organization type: `business` or `individual` |
| `name` | string | Organization name |
| `slug` | string | URL-friendly identifier for the organization |
| `description` | string \| null | Organization description |
| `email` | string | Organization email address |
| `phone` | string \| null | Organization phone number |
| `address` | string \| null | Organization address |
| `gst_status` | string \| null | GST status: `registered` or `unregistered` |
| `gst_in` | string \| null | GST Identification Number (max 15 characters) |
| `place_of_supply` | string \| null | Place of supply location |
| `shipping_address` | string | Shipping address |
| `shipping_city` | string | Shipping city |
| `shipping_zip` | string | Shipping ZIP/postal code |
| `shipping_phone` | string | Shipping phone number |
| `billing_address` | string | Billing address |
| `billing_city` | string | Billing city |
| `billing_zip` | string | Billing ZIP/postal code |
| `billing_phone` | string | Billing phone number |
| `status` | string | Organization status (e.g., `active`, `inactive`) |
| `created_at` | string | ISO 8601 timestamp of when the organization was created |
| `updated_at` | string | ISO 8601 timestamp of when the organization was last updated |
| `deleted_at` | string \| null | ISO 8601 timestamp of when the organization was soft deleted (null if not deleted) |

---

## HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | Organization retrieved successfully |
| `401` | Unauthenticated - Missing or invalid authentication token |
| `404` | Organization not found or user is not a member |
| `422` | Validation error - Invalid or missing required fields |

---

## Business Logic

1. **Authentication Required**: You must be authenticated with a valid Bearer token to access this endpoint.

2. **Membership Check**: The organization must belong to the authenticated user. The user must be a member of the organization (linked through the `organization_users` pivot table).

3. **ID Validation**: 
   - The `id` must be provided in the request body
   - The `id` must be an integer
   - The `id` must exist in the `organizations` table
   - The authenticated user must be a member of the organization with this `id`

4. **Access Control**: This endpoint enforces membership-level access control. Users can only view organizations they are members of.

5. **Soft Deletes**: The endpoint respects soft deletes. If an organization has been soft deleted, it will still be returned if the user has access.

---

## Example cURL Requests

### Retrieve Organization

```bash
curl -X POST "https://your-domain.com/api/organizations/show" \
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
    "type": "business",
    "name": "Tech Solutions Inc.",
    "slug": "tech-solutions-inc-1",
    "description": "A leading technology solutions provider",
    "email": "contact@techsolutions.com",
    "phone": "+1 (555) 123-4567",
    "address": "123 Main Street, City, State 12345",
    "gst_status": "registered",
    "gst_in": "27ABCDE1234F1Z5",
    "place_of_supply": "Maharashtra",
    "shipping_address": "123 Main Street",
    "shipping_city": "Mumbai",
    "shipping_zip": "400001",
    "shipping_phone": "+1 (555) 123-4567",
    "billing_address": "123 Main Street",
    "billing_city": "Mumbai",
    "billing_zip": "400001",
    "billing_phone": "+1 (555) 123-4567",
    "status": "active",
    "created_at": "2025-11-07T10:30:00.000000Z",
    "updated_at": "2025-11-07T14:45:00.000000Z",
    "deleted_at": null
  },
  "message": "Organization retrieved successfully."
}
```

---

## JavaScript/Fetch Example

### Retrieve Organization

```javascript
const getOrganization = async (organizationId) => {
  try {
    const response = await fetch('https://your-domain.com/api/organizations/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: organizationId
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('Organization retrieved:', data.data);
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
const organization = await getOrganization(1);
console.log('Organization:', organization.data);
```

### With Error Handling

```javascript
const getOrganization = async (organizationId) => {
  try {
    const response = await fetch('https://your-domain.com/api/organizations/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ id: organizationId })
    });

    const data = await response.json();
    
    if (!response.ok) {
      // Handle different error types
      if (response.status === 404) {
        throw new Error('Organization not found or you do not have access to it.');
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
    console.error('Failed to retrieve organization:', error);
    throw error;
  }
};
```

---

## Axios Example

```javascript
import axios from 'axios';

const getOrganization = async (organizationId, authToken) => {
  try {
    const response = await axios.post(
      'https://your-domain.com/api/organizations/show',
      { id: organizationId },
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
      throw new Error(error.response.data.message || 'Failed to retrieve organization');
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
const organization = await getOrganization(1, yourAuthToken);
console.log('Organization:', organization.data);
```

---

## React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface Organization {
  id: number;
  type: string;
  name: string;
  slug: string;
  description: string | null;
  email: string;
  phone: string | null;
  address: string | null;
  gst_status: string | null;
  gst_in: string | null;
  place_of_supply: string | null;
  shipping_address: string;
  shipping_city: string;
  shipping_zip: string;
  shipping_phone: string;
  billing_address: string;
  billing_city: string;
  billing_zip: string;
  billing_phone: string;
  status: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

interface OrganizationResponse {
  data: Organization;
  message: string;
}

export function useOrganization(organizationId: number | null, authToken: string) {
  const [organization, setOrganization] = useState<Organization | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!organizationId) {
      return;
    }

    const fetchOrganization = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<OrganizationResponse>(
          'https://your-domain.com/api/organizations/show',
          { id: organizationId },
          {
            headers: {
              'Authorization': `Bearer ${authToken}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        setOrganization(response.data.data);
      } catch (err: any) {
        const errorMessage = err.response?.data?.message || 'Failed to fetch organization';
        setError(errorMessage);
        console.error('Error fetching organization:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchOrganization();
  }, [organizationId, authToken]);

  return { organization, loading, error };
}

// Usage in component
function OrganizationView({ organizationId }: { organizationId: number }) {
  const authToken = localStorage.getItem('auth_token') || '';
  const { organization, loading, error } = useOrganization(organizationId, authToken);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!organization) return <div>No organization found</div>;

  return (
    <div>
      <h1>{organization.name}</h1>
      <p>Type: {organization.type}</p>
      <p>Email: {organization.email}</p>
      <p>Phone: {organization.phone}</p>
      <p>Status: {organization.status}</p>
      {organization.description && <p>Description: {organization.description}</p>}
      {organization.gst_status && (
        <div>
          <p>GST Status: {organization.gst_status}</p>
          {organization.gst_in && <p>GST IN: {organization.gst_in}</p>}
        </div>
      )}
    </div>
  );
}
```

---

## PHP Example

```php
<?php

function getOrganization($organizationId, $authToken) {
    $url = 'https://your-domain.com/api/organizations/show';
    
    $data = [
        'id' => $organizationId
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
    $organizationId = 1;
    
    $result = getOrganization($organizationId, $authToken);
    echo "Organization retrieved successfully!\n";
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

def get_organization(organization_id, auth_token):
    url = 'https://your-domain.com/api/organizations/show'
    
    headers = {
        'Authorization': f'Bearer {auth_token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    data = {
        'id': organization_id
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
    organization_id = 1
    
    result = get_organization(organization_id, auth_token)
    print("Organization retrieved successfully!")
    print(json.dumps(result['data'], indent=2))
except Exception as e:
    print(f"Error: {e}")
```

---

## Notes

1. **Authentication**: You must be authenticated with a valid Bearer token to use this endpoint.

2. **Membership Required**: You can only retrieve organizations that you are a member of. Attempting to retrieve an organization you're not a member of will result in a 404 error.

3. **ID Format**: The `id` must be provided as an integer in the request body, not as a URL parameter.

4. **Error Handling**: Always check the HTTP status code and handle errors appropriately. Common errors include:
   - 401: Authentication required
   - 404: Organization not found or you're not a member
   - 422: Validation error (missing or invalid ID)

5. **Performance**: This endpoint performs a single database query to retrieve the organization. It's optimized for quick lookups.

6. **Data Privacy**: The endpoint enforces membership-level access control, ensuring users can only view organizations they belong to.

7. **Soft Deletes**: The endpoint respects soft deletes. If an organization has been soft deleted, it will still be returned if you have access (the `deleted_at` field will contain a timestamp).

---

## Related Endpoints

- `POST /api/organizations/index` - Get paginated list of organizations
- `POST /api/organizations/save` - Create or update an organization
- `POST /api/organizations/delete` - Delete an organization
- `GET /api/organizations` - Get all active organizations (public)
- `POST /api/organizations/search` - Search organization by name

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main API documentation.

