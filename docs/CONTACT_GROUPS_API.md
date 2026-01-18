# Contact Groups API Documentation

## Overview
This API endpoint retrieves the list of available contact groups that can be assigned to contacts. These are predefined groups that are used to categorize contacts in the system.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contact-groups`  
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

This endpoint accepts an empty request body (optional). You can send an empty JSON object or no body at all:

```json
{}
```

Or simply send no body (empty POST request).

---

## Response Format

### Success Response (200)

```json
{
  "data": [
    "Clients",
    "Partners",
    "Team",
    "Family",
    "Prospects",
    "Vendors",
    "Friends",
    "Colleagues"
  ],
  "message": "Contact groups retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | array | Array of contact group names (strings) |
| `message` | string | Success message |

---

## Available Contact Groups

The API returns the following predefined contact groups:

1. **Clients** - Business clients or customers
2. **Partners** - Business partners
3. **Team** - Team members or colleagues
4. **Family** - Family members
5. **Prospects** - Potential clients or leads
6. **Vendors** - Suppliers or vendors
7. **Friends** - Personal friends
8. **Colleagues** - Work colleagues

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get contact groups
async function getContactGroups(token) {
  try {
    const response = await fetch('http://your-api-url/api/contact-groups', {
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
      throw new Error(data.message || 'Failed to retrieve contact groups');
    }

    return data.data; // Returns array of group names
  } catch (error) {
    console.error('Error fetching contact groups:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const groups = await getContactGroups(token);
console.log('Available groups:', groups);
// Output: ['Clients', 'Partners', 'Team', 'Family', 'Prospects', 'Vendors', 'Friends', 'Colleagues']
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

// Get contact groups
async function getContactGroups() {
  try {
    const response = await apiClient.post('/contact-groups', {});
    return response.data.data; // Returns array of group names
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const groups = await getContactGroups();
console.log('Available groups:', groups);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface ContactGroupsResponse {
  data: string[];
  message: string;
}

export function useContactGroups(token: string) {
  const [groups, setGroups] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchGroups = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<ContactGroupsResponse>(
          '/api/contact-groups',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setGroups(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching contact groups:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      fetchGroups();
    }
  }, [token]);

  return { groups, loading, error };
}

// Usage in component
function ContactForm() {
  const token = localStorage.getItem('auth_token') || '';
  const { groups, loading, error } = useContactGroups(token);

  if (loading) return <div>Loading groups...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div>
      <label>Select Groups:</label>
      {groups.map(group => (
        <label key={group}>
          <input type="checkbox" value={group} />
          {group}
        </label>
      ))}
    </div>
  );
}
```

### React Query Example

```typescript
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

const fetchContactGroups = async (token: string) => {
  const response = await axios.post<{ data: string[]; message: string }>(
    '/api/contact-groups',
    {},
    {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    }
  );
  return response.data.data;
};

export function useContactGroupsQuery(token: string) {
  return useQuery({
    queryKey: ['contactGroups'],
    queryFn: () => fetchContactGroups(token),
    enabled: !!token,
    staleTime: 1000 * 60 * 60, // Cache for 1 hour (groups don't change often)
  });
}

// Usage
function ContactForm() {
  const token = localStorage.getItem('auth_token') || '';
  const { data: groups = [], isLoading, error } = useContactGroupsQuery(token);

  if (isLoading) return <div>Loading groups...</div>;
  if (error) return <div>Error loading groups</div>;

  return (
    <div>
      {groups.map(group => (
        <label key={group}>
          <input type="checkbox" value={group} />
          {group}
        </label>
      ))}
    </div>
  );
}
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/contact-groups" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

---

## Integration with Contact Save API

The contact groups returned by this endpoint are used in the Contact Save API. Here's how to use them together:

```javascript
// Step 1: Fetch available groups
async function setupContactForm(token) {
  // Get available groups
  const groupsResponse = await fetch('/api/contact-groups', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({})
  });
  
  const groupsData = await groupsResponse.json();
  const availableGroups = groupsData.data; // ['Clients', 'Partners', ...]
  
  // Use these groups to populate checkboxes in your form
  return availableGroups;
}

// Step 2: When saving a contact, send selected groups
async function saveContact(contactData, selectedGroups, token) {
  const response = await fetch('/api/contacts/save', {
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
      // ... other fields
      groups: selectedGroups // ['Clients', 'Team'] - selected from the groups API
    })
  });
  
  return await response.json();
}
```

---

## Complete Example: Contact Form with Groups

```javascript
import React, { useState, useEffect } from 'react';

function ContactForm({ token }) {
  const [availableGroups, setAvailableGroups] = useState([]);
  const [selectedGroups, setSelectedGroups] = useState([]);
  const [loading, setLoading] = useState(false);

  // Fetch available groups on component mount
  useEffect(() => {
    async function fetchGroups() {
      try {
        const response = await fetch('/api/contact-groups', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });
        
        const data = await response.json();
        setAvailableGroups(data.data);
      } catch (error) {
        console.error('Error fetching groups:', error);
      }
    }
    
    fetchGroups();
  }, [token]);

  // Handle group selection
  const handleGroupToggle = (group) => {
    setSelectedGroups(prev => {
      if (prev.includes(group)) {
        return prev.filter(g => g !== group);
      } else {
        return [...prev, group];
      }
    });
  };

  // Submit form
  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      const formData = {
        first_name: e.target.firstName.value,
        last_name: e.target.lastName.value,
        email: e.target.email.value,
        groups: selectedGroups // Send selected groups
      };
      
      const response = await fetch('/api/contacts/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });
      
      const result = await response.json();
      console.log('Contact saved:', result);
    } catch (error) {
      console.error('Error saving contact:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input name="firstName" placeholder="First Name" required />
      <input name="lastName" placeholder="Last Name" required />
      <input name="email" type="email" placeholder="Email" />
      
      <div>
        <label>Select Groups:</label>
        {availableGroups.map(group => (
          <label key={group}>
            <input
              type="checkbox"
              checked={selectedGroups.includes(group)}
              onChange={() => handleGroupToggle(group)}
            />
            {group}
          </label>
        ))}
        <p>Selected: {selectedGroups.length} group(s)</p>
      </div>
      
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Contact'}
      </button>
    </form>
  );
}
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Contact groups retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `500` | Server error |

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
  "message": "Server Error"
}
```

---

## Important Notes

### 1. Groups are Predefined
- The contact groups are **predefined** and returned by the API
- Currently, there are 8 fixed groups: Clients, Partners, Team, Family, Prospects, Vendors, Friends, Colleagues
- Groups cannot be created, updated, or deleted through this API

### 2. Multiple Selection
- A contact can belong to **multiple groups** at the same time
- When saving a contact, send an array of selected group names: `["Clients", "Team"]`

### 3. Group Names are Case-Sensitive
- Group names should match exactly as returned by the API
- For example: use `"Clients"` not `"clients"` or `"CLIENTS"`

### 4. Caching Recommendations
- Contact groups don't change frequently
- Consider caching the response for better performance
- Cache for at least 1 hour or until the user logs out

### 5. Empty Body
- The endpoint accepts an empty request body (`{}`)
- You can also send no body at all - both will work

### 6. POST Method
- Note that this endpoint uses `POST` instead of `GET`
- This is by design for consistency with other endpoints

---

## Best Practices

1. **Fetch Once**: Load groups when the form/modal opens, not on every render
2. **Cache Results**: Store groups in component state or global state management
3. **Validate Selection**: Ensure selected groups exist in the available groups list before submitting
4. **Handle Errors**: Display user-friendly error messages if the API call fails
5. **Loading States**: Show loading indicators while fetching groups

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/contact-groups` with Bearer token
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

