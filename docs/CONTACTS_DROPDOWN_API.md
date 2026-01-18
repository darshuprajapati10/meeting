# Contacts Dropdown API Documentation

## Overview
This API endpoint retrieves a simplified list of contacts for use in dropdown/select components. It returns only the `id` and `name` (full name) of contacts saved by the user in their organization. This is particularly useful for selecting referrers when creating/editing contacts.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/dropdown`  
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
    {
      "id": 1,
      "name": "John Doe"
    },
    {
      "id": 2,
      "name": "Jane Smith"
    },
    {
      "id": 3,
      "name": "Bob Johnson"
    }
  ],
  "message": "Contacts retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | array | Array of contact objects with `id` and `name` |
| `data[].id` | integer | Contact ID (EndID) - use this when saving referrer_id |
| `data[].name` | string | Full name (first_name + last_name) |
| `message` | string | Success message |

---

## Important Notes

### 1. Contact ID (EndID)
- The `id` field in the response is what you should use for `referrer_id` when saving/updating contacts via `/api/contacts/save`
- This is the "EndID" you mentioned - it's the contact's unique identifier

### 2. Organization Scope
- Only contacts from the authenticated user's organization are returned
- If the user has no organization, an empty array is returned

### 3. Sorted Results
- Contacts are sorted alphabetically by first name, then last name
- This makes them easy to find in dropdown lists

### 4. Name Format
- The `name` field combines `first_name` and `last_name`
- Example: "John" + "Doe" = "John Doe"

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get contacts for dropdown
async function getContactsForDropdown(token) {
  try {
    const response = await fetch('http://your-api-url/api/contacts/dropdown', {
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
      throw new Error(data.message || 'Failed to retrieve contacts');
    }

    return data.data; // Returns array of {id, name} objects
  } catch (error) {
    console.error('Error fetching contacts:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const contacts = await getContactsForDropdown(token);
console.log('Contacts:', contacts);
// Output: [{id: 1, name: "John Doe"}, {id: 2, name: "Jane Smith"}, ...]
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

// Get contacts for dropdown
async function getContactsForDropdown() {
  try {
    const response = await apiClient.post('/contacts/dropdown', {});
    return response.data.data; // Returns array of {id, name} objects
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const contacts = await getContactsForDropdown();
console.log('Contacts:', contacts);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface ContactDropdown {
  id: number;
  name: string;
}

interface ContactsDropdownResponse {
  data: ContactDropdown[];
  message: string;
}

export function useContactsDropdown(token: string) {
  const [contacts, setContacts] = useState<ContactDropdown[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchContacts = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<ContactsDropdownResponse>(
          '/api/contacts/dropdown',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setContacts(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching contacts:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      fetchContacts();
    }
  }, [token]);

  return { contacts, loading, error };
}

// Usage in component
function ReferrerSelect() {
  const token = localStorage.getItem('auth_token') || '';
  const { contacts, loading, error } = useContactsDropdown(token);
  const [selectedReferrerId, setSelectedReferrerId] = useState<number | null>(null);

  if (loading) return <div>Loading contacts...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <select 
      value={selectedReferrerId || ''} 
      onChange={(e) => setSelectedReferrerId(Number(e.target.value) || null)}
    >
      <option value="">Select a referrer (optional)</option>
      {contacts.map(contact => (
        <option key={contact.id} value={contact.id}>
          {contact.name}
        </option>
      ))}
    </select>
  );
}
```

### React Component Example (Complete Form)

```typescript
import React, { useState, useEffect } from 'react';

interface ContactDropdown {
  id: number;
  name: string;
}

function ContactForm({ token }: { token: string }) {
  const [referrerContacts, setReferrerContacts] = useState<ContactDropdown[]>([]);
  const [selectedReferrerId, setSelectedReferrerId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  // Fetch contacts for referrer dropdown
  useEffect(() => {
    async function fetchReferrerContacts() {
      try {
        const response = await fetch('/api/contacts/dropdown', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        const data = await response.json();
        setReferrerContacts(data.data);
      } catch (error) {
        console.error('Error fetching contacts:', error);
      }
    }

    fetchReferrerContacts();
  }, [token]);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);

    try {
      const formData = {
        first_name: e.currentTarget.firstName.value,
        last_name: e.currentTarget.lastName.value,
        email: e.currentTarget.email.value,
        referrer_id: selectedReferrerId || null, // Use the id from dropdown
        // ... other fields
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
        <label>Referrer (Optional):</label>
        <select 
          value={selectedReferrerId || ''} 
          onChange={(e) => setSelectedReferrerId(Number(e.target.value) || null)}
        >
          <option value="">None</option>
          {referrerContacts.map(contact => (
            <option key={contact.id} value={contact.id}>
              {contact.name}
            </option>
          ))}
        </select>
        <small>Select who referred this contact</small>
      </div>
      
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Contact'}
      </button>
    </form>
  );
}
```

---

## Integration with Contact Save API

This endpoint is typically used to populate the `referrer_id` field when saving contacts:

```javascript
// Step 1: Fetch contacts for dropdown
async function setupReferrerDropdown(token) {
  const response = await fetch('/api/contacts/dropdown', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({})
  });
  
  const data = await response.json();
  return data.data; // [{id: 1, name: "John Doe"}, ...]
}

// Step 2: When saving a contact, use the selected contact's id as referrer_id
async function saveContact(contactData, selectedReferrerId, token) {
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
      referrer_id: selectedReferrerId // Use the id from dropdown (EndID)
    })
  });
  
  return await response.json();
}

// Usage
const contacts = await setupReferrerDropdown(token);
// User selects a contact from dropdown, get the id
const selectedContactId = 1; // This is the EndID
await saveContact(contactData, selectedContactId, token);
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/contacts/dropdown" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Contacts retrieved successfully |
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

### No Organization (200 with empty array)

```json
{
  "data": [],
  "message": "No organization found. Please create a contact first."
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

### 1. EndID Usage
- The `id` field in the response is the **EndID** you need
- Use this `id` value for the `referrer_id` field when saving contacts
- Example: If dropdown returns `{id: 5, name: "John Doe"}`, use `referrer_id: 5` when saving

### 2. Empty Results
- If no contacts exist yet, the `data` array will be empty `[]`
- This is normal - users need to create contacts first before they can be used as referrers

### 3. Organization Scope
- Only contacts from the user's organization are returned
- Contacts from other organizations won't appear

### 4. Name Display
- The `name` field is formatted as "First Last"
- Use this directly in dropdown options for user-friendly display

### 5. POST Method
- This endpoint uses `POST` instead of `GET` for consistency with other endpoints

---

## Best Practices

1. **Cache Results**: Since contacts don't change frequently, consider caching the results
2. **Show Loading State**: Display a loading indicator while fetching contacts
3. **Handle Empty State**: Show a message when no contacts are available
4. **Update on Save**: After saving a new contact, refresh the dropdown to include it
5. **Optional Field**: Always make referrer_id optional - users may not have a referrer

---

## Complete Example: Contact Form with Referrer Selection

```javascript
import React, { useState, useEffect } from 'react';

function ContactForm({ token }) {
  const [referrers, setReferrers] = useState([]);
  const [selectedReferrerId, setSelectedReferrerId] = useState(null);
  const [loading, setLoading] = useState(false);

  // Fetch referrers on mount
  useEffect(() => {
    async function fetchReferrers() {
      try {
        const response = await fetch('/api/contacts/dropdown', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        const data = await response.json();
        setReferrers(data.data);
      } catch (error) {
        console.error('Error fetching referrers:', error);
      }
    }

    fetchReferrers();
  }, [token]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const formData = {
        first_name: e.target.firstName.value,
        last_name: e.target.lastName.value,
        email: e.target.email.value,
        referrer_id: selectedReferrerId || null, // EndID from dropdown
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
      
      if (response.ok) {
        // Refresh referrers list to include the new contact
        const refreshResponse = await fetch('/api/contacts/dropdown', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });
        const refreshData = await refreshResponse.json();
        setReferrers(refreshData.data);
        
        // Reset form
        e.target.reset();
        setSelectedReferrerId(null);
      }
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
        <label>Referred By (Optional):</label>
        <select 
          value={selectedReferrerId || ''} 
          onChange={(e) => setSelectedReferrerId(Number(e.target.value) || null)}
        >
          <option value="">-- Select Referrer --</option>
          {referrers.map(contact => (
            <option key={contact.id} value={contact.id}>
              {contact.name}
            </option>
          ))}
        </select>
        {referrers.length === 0 && (
          <small>No contacts available. Create a contact first to use as a referrer.</small>
        )}
      </div>
      
      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Contact'}
      </button>
    </form>
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/contacts/dropdown` with Bearer token
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

