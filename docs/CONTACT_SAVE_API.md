# Contact Save API Documentation

## Overview
This API endpoint allows you to create or update a contact. The same endpoint handles both operations - if an `id` is provided, it updates the existing contact; otherwise, it creates a new one.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/save`  
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

### Creating a New Contact

When creating a contact, **do not** include the `id` field:

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "+1 (555) 123-4567",
  "company": "Acme Corporation",
  "job_title": "Software Engineer",
  "referrer_id": null,
  "groups": ["Clients", "Team"],
  "address": "123 Main St, City, State 12345",
  "notes": "Additional notes about this contact..."
}
```

### Updating an Existing Contact

When updating a contact, **include** the `id` field:

```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "+1 (555) 123-4567",
  "company": "Acme Corporation",
  "job_title": "Senior Software Engineer",
  "referrer_id": null,
  "groups": ["Clients", "Team", "Partners"],
  "address": "456 New St, City, State 12345",
  "notes": "Updated notes..."
}
```

---

## Field Specifications

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | Contact ID (only for updates) | Must exist in contacts table |
| `first_name` | string | **Yes** | Contact's first name | Max 255 characters |
| `last_name` | string | **Yes** | Contact's last name | Max 255 characters |
| `email` | string | No | Contact's email address | Valid email format, max 255 characters |
| `phone` | string | No | Contact's phone number | Max 30 characters |
| `company` | string | No | Company name | Max 255 characters |
| `job_title` | string | No | Job title/position | Max 255 characters |
| `referrer_id` | integer | No | ID of the contact who referred this contact | Must exist in contacts table |
| `groups` | array | No | Array of group names (e.g., ["Clients", "Team"]) | Each group name max 100 characters |
| `address` | string | No | Physical address | Max 500 characters |
| `notes` | string | No | Additional notes | No max length |

### Important Notes on `groups` Field

The `groups` field is flexible and will be normalized automatically:
- ✅ **Valid:** `["Clients", "Team"]` (array)
- ✅ **Valid:** `null` (will be converted to `[]`)
- ✅ **Valid:** `""` (empty string, will be converted to `[]`)
- ✅ **Valid:** Not provided (optional field)
- ✅ **Valid:** JSON string like `'["Clients","Team"]'` (will be decoded)

**Recommendation:** Always send `groups` as an array of strings: `["Clients", "Team"]`

---

## Response Examples

### Success - Contact Created (201)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1 (555) 123-4567",
    "company": "Acme Corporation",
    "job_title": "Software Engineer",
    "referrer_id": null,
    "groups": ["Clients", "Team"],
    "address": "123 Main St, City, State 12345",
    "notes": "Additional notes about this contact...",
    "created_by": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  },
  "message": "Contact created successfully."
}
```

### Success - Contact Updated (200)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1 (555) 123-4567",
    "company": "Acme Corporation",
    "job_title": "Senior Software Engineer",
    "referrer_id": null,
    "groups": ["Clients", "Team", "Partners"],
    "address": "456 New St, City, State 12345",
    "notes": "Updated notes...",
    "created_by": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T11:45:00.000000Z"
  },
  "message": "Contact updated successfully."
}
```

### Error - Validation Failed (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "first_name": [
      "The first name field is required."
    ],
    "last_name": [
      "The last name field is required."
    ],
    "email": [
      "The email must be a valid email address."
    ],
    "groups": [
      "The groups field must be an array."
    ]
  }
}
```

### Error - Contact Not Found (404)

When trying to update a contact that doesn't exist or doesn't belong to the user's organization:

```json
{
  "message": "No query results for model [App\\Models\\Contact] {id}"
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Create a new contact
async function createContact(contactData, token) {
  try {
    const response = await fetch('http://your-api-url/api/contacts/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        first_name: contactData.firstName,
        last_name: contactData.lastName,
        email: contactData.email,
        phone: contactData.phone,
        company: contactData.company,
        job_title: contactData.jobTitle,
        referrer_id: contactData.referrerId || null,
        groups: contactData.groups || [],
        address: contactData.address,
        notes: contactData.notes
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to save contact');
    }

    return data;
  } catch (error) {
    console.error('Error saving contact:', error);
    throw error;
  }
}

// Update an existing contact
async function updateContact(contactId, contactData, token) {
  try {
    const response = await fetch('http://your-api-url/api/contacts/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: contactId,
        first_name: contactData.firstName,
        last_name: contactData.lastName,
        email: contactData.email,
        phone: contactData.phone,
        company: contactData.company,
        job_title: contactData.jobTitle,
        referrer_id: contactData.referrerId || null,
        groups: contactData.groups || [],
        address: contactData.address,
        notes: contactData.notes
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update contact');
    }

    return data;
  } catch (error) {
    console.error('Error updating contact:', error);
    throw error;
  }
}
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

// Create or update contact
async function saveContact(contactData) {
  try {
    const response = await response.post('/contacts/save', {
      // Include id only for updates
      ...(contactData.id && { id: contactData.id }),
      first_name: contactData.firstName,
      last_name: contactData.lastName,
      email: contactData.email,
      phone: contactData.phone,
      company: contactData.company,
      job_title: contactData.jobTitle,
      referrer_id: contactData.referrerId || null,
      groups: contactData.groups || [],
      address: contactData.address,
      notes: contactData.notes
    });

    return response.data;
  } catch (error) {
    if (error.response) {
      // Handle validation errors
      console.error('Validation errors:', error.response.data.errors);
      throw error.response.data;
    }
    throw error;
  }
}
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface ContactFormData {
  id?: number;
  firstName: string;
  lastName: string;
  email?: string;
  phone?: string;
  company?: string;
  jobTitle?: string;
  referrerId?: number | null;
  groups?: string[];
  address?: string;
  notes?: string;
}

export function useSaveContact() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const saveContact = async (formData: ContactFormData, token: string) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post(
        '/api/contacts/save',
        {
          ...(formData.id && { id: formData.id }),
          first_name: formData.firstName,
          last_name: formData.lastName,
          email: formData.email || null,
          phone: formData.phone || null,
          company: formData.company || null,
          job_title: formData.jobTitle || null,
          referrer_id: formData.referrerId || null,
          groups: formData.groups || [],
          address: formData.address || null,
          notes: formData.notes || null,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
        }
      );

      return response.data;
    } catch (err: any) {
      const errorData = err.response?.data || err.message;
      setError(errorData);
      throw errorData;
    } finally {
      setLoading(false);
    }
  };

  return { saveContact, loading, error };
}
```

---

## cURL Examples

### Create Contact

```bash
curl -X POST "http://your-api-url/api/contacts/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1 (555) 123-4567",
    "company": "Acme Corporation",
    "job_title": "Software Engineer",
    "groups": ["Clients", "Team"],
    "address": "123 Main St, City, State 12345",
    "notes": "Additional notes..."
  }'
```

### Update Contact

```bash
curl -X POST "http://your-api-url/api/contacts/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1 (555) 123-4567",
    "company": "Acme Corporation",
    "job_title": "Senior Software Engineer"
  }'
```

---

## Important Notes

### 1. Organization Auto-Creation
- If a user doesn't have an organization associated with their account, the system will **automatically create** a personal organization for them
- The organization will be named: `"{User Name}'s Organization"`
- The user will be automatically assigned the "admin" role in this organization
- This happens transparently - no additional API calls needed

### 2. Groups Field Handling
- The `groups` field accepts multiple formats and will be normalized automatically
- **Best practice:** Always send `groups` as an array: `["Clients", "Team"]`
- If you send `null`, `""`, or omit the field, it will be treated as an empty array `[]`
- Each group name must be a string with max 100 characters

### 3. Referrer ID
- `referrer_id` must be the ID of an existing contact
- If the contact doesn't exist, validation will fail
- Set to `null` if there's no referrer

### 4. Update Behavior
- When updating (with `id`), only contacts within the user's organization can be updated
- Attempting to update a contact from a different organization will result in a 404 error

### 5. Authentication
- All requests must include a valid Bearer token in the Authorization header
- Tokens are obtained through the login/signup endpoints

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Contact updated successfully |
| `201` | Contact created successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Contact not found (for updates) |
| `422` | Validation error |
| `500` | Server error |

---

## Error Handling Best Practices

1. **Check Response Status**: Always check the HTTP status code before processing the response
2. **Handle Validation Errors**: Display field-specific errors from `errors` object
3. **Network Errors**: Handle timeout and network connectivity issues
4. **Token Expiration**: Handle 401 errors by redirecting to login

Example error handling:

```javascript
try {
  const response = await fetch('/api/contacts/save', {...});
  const data = await response.json();
  
  if (!response.ok) {
    if (response.status === 422) {
      // Handle validation errors
      Object.keys(data.errors).forEach(field => {
        console.error(`${field}: ${data.errors[field].join(', ')}`);
      });
    } else if (response.status === 401) {
      // Handle authentication error
      // Redirect to login
    } else {
      // Handle other errors
      console.error(data.message);
    }
    return;
  }
  
  // Success
  console.log('Contact saved:', data.data);
} catch (error) {
  console.error('Network error:', error);
}
```

---

## Testing

You can test the API using:
- Postman (import the Postman collection if available)
- cURL (see examples above)
- Your frontend application
- API testing tools like Insomnia or HTTPie

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.
