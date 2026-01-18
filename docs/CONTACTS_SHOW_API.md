# Contacts Show API Documentation

## Overview
This API endpoint retrieves a single contact by its ID. It returns the complete contact data for viewing or editing. The contact must belong to the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/show`  
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

```json
{
  "id": 1
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | **Yes** | Contact ID to retrieve |

---

## Response Format

### Success Response (200)

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
  "message": "Contact retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Complete contact data (ContactResource) |
| `data.id` | integer | Contact ID |
| `data.organization_id` | integer | Organization ID |
| `data.first_name` | string | First name |
| `data.last_name` | string | Last name |
| `data.email` | string\|null | Email address |
| `data.phone` | string\|null | Phone number |
| `data.company` | string\|null | Company name |
| `data.job_title` | string\|null | Job title |
| `data.referrer_id` | integer\|null | ID of referring contact |
| `data.groups` | array | Array of group names |
| `data.address` | string\|null | Physical address |
| `data.notes` | string\|null | Additional notes |
| `data.created_by` | integer | User ID who created the contact |
| `data.created_at` | string | Creation timestamp (ISO 8601) |
| `data.updated_at` | string | Last update timestamp (ISO 8601) |
| `message` | string | Success message |

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get contact by ID
async function getContactById(contactId, token) {
  try {
    const response = await fetch('http://your-api-url/api/contacts/show', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: contactId
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve contact');
    }

    return data.data; // Returns contact object
  } catch (error) {
    console.error('Error fetching contact:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const contact = await getContactById(1, token);
console.log('Contact:', contact);
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

// Get contact by ID
async function getContactById(contactId) {
  try {
    const response = await apiClient.post('/contacts/show', {
      id: contactId
    });
    
    return response.data.data; // Returns contact object
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const contact = await getContactById(1);
console.log('Contact:', contact);
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';
import axios from 'axios';

interface Contact {
  id: number;
  organization_id: number;
  first_name: string;
  last_name: string;
  email: string | null;
  phone: string | null;
  company: string | null;
  job_title: string | null;
  referrer_id: number | null;
  groups: string[];
  address: string | null;
  notes: string | null;
  created_by: number;
  created_at: string;
  updated_at: string;
}

interface ContactShowResponse {
  data: Contact;
  message: string;
}

export function useContactShow(token: string, contactId: number | null) {
  const [contact, setContact] = useState<Contact | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchContact = async () => {
      if (!contactId) {
        setContact(null);
        return;
      }

      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<ContactShowResponse>(
          '/api/contacts/show',
          { id: contactId },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setContact(response.data.data);
      } catch (err: any) {
        const errorData = err.response?.data || err.message;
        setError(errorData);
        console.error('Error fetching contact:', errorData);
      } finally {
        setLoading(false);
      }
    };

    if (token && contactId) {
      fetchContact();
    }
  }, [token, contactId]);

  return { contact, loading, error };
}

// Usage in component
function ContactDetails({ contactId }: { contactId: number }) {
  const token = localStorage.getItem('auth_token') || '';
  const { contact, loading, error } = useContactShow(token, contactId);

  if (loading) return <div>Loading contact...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!contact) return <div>Contact not found</div>;

  return (
    <div>
      <h2>{contact.first_name} {contact.last_name}</h2>
      <p>Email: {contact.email || 'N/A'}</p>
      <p>Phone: {contact.phone || 'N/A'}</p>
      <p>Company: {contact.company || 'N/A'}</p>
      <p>Job Title: {contact.job_title || 'N/A'}</p>
      <p>Groups: {contact.groups.join(', ') || 'None'}</p>
      <p>Address: {contact.address || 'N/A'}</p>
      <p>Notes: {contact.notes || 'None'}</p>
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState, useEffect } from 'react';

function ContactView({ contactId, token }) {
  const [contact, setContact] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!contactId) return;

    const fetchContact = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch('/api/contacts/show', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ id: contactId })
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Failed to load contact');
        }

        setContact(data.data);
      } catch (err) {
        setError(err.message);
        console.error('Error:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchContact();
  }, [contactId, token]);

  if (loading) {
    return <div>Loading contact...</div>;
  }

  if (error) {
    return <div className="error">Error: {error}</div>;
  }

  if (!contact) {
    return <div>Contact not found</div>;
  }

  return (
    <div className="contact-view">
      <h1>{contact.first_name} {contact.last_name}</h1>
      
      <div className="contact-info">
        <div>
          <strong>Email:</strong> {contact.email || 'N/A'}
        </div>
        <div>
          <strong>Phone:</strong> {contact.phone || 'N/A'}
        </div>
        <div>
          <strong>Company:</strong> {contact.company || 'N/A'}
        </div>
        <div>
          <strong>Job Title:</strong> {contact.job_title || 'N/A'}
        </div>
        <div>
          <strong>Groups:</strong> {contact.groups.length > 0 ? contact.groups.join(', ') : 'None'}
        </div>
        <div>
          <strong>Address:</strong> {contact.address || 'N/A'}
        </div>
        {contact.notes && (
          <div>
            <strong>Notes:</strong>
            <p>{contact.notes}</p>
          </div>
        )}
      </div>

      <div className="contact-meta">
        <small>
          Created: {new Date(contact.created_at).toLocaleString()}
        </small>
        <small>
          Updated: {new Date(contact.updated_at).toLocaleString()}
        </small>
      </div>
    </div>
  );
}
```

---

## Integration with Contact Save API

After retrieving a contact, you can use the data to populate an edit form:

```javascript
// Step 1: Get contact by ID
async function loadContactForEdit(contactId, token) {
  const response = await fetch('/api/contacts/show', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ id: contactId })
  });

  const data = await response.json();
  return data.data; // Contact object
}

// Step 2: Use the data to populate edit form
async function populateEditForm(contactId, token) {
  const contact = await loadContactForEdit(contactId, token);
  
  // Populate form fields
  document.getElementById('firstName').value = contact.first_name;
  document.getElementById('lastName').value = contact.last_name;
  document.getElementById('email').value = contact.email || '';
  document.getElementById('phone').value = contact.phone || '';
  document.getElementById('company').value = contact.company || '';
  document.getElementById('jobTitle').value = contact.job_title || '';
  document.getElementById('address').value = contact.address || '';
  document.getElementById('notes').value = contact.notes || '';
  
  // Set referrer if exists
  if (contact.referrer_id) {
    document.getElementById('referrerId').value = contact.referrer_id;
  }
  
  // Set groups (if using checkboxes)
  contact.groups.forEach(group => {
    const checkbox = document.querySelector(`input[value="${group}"]`);
    if (checkbox) checkbox.checked = true;
  });
  
  // Store contact ID for update
  document.getElementById('contactId').value = contact.id;
}

// Step 3: Submit updated contact via /api/contacts/save
async function updateContact(contactId, formData, token) {
  const response = await fetch('/api/contacts/save', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      id: contactId, // Include ID for update
      first_name: formData.firstName,
      last_name: formData.lastName,
      // ... other fields
    })
  });

  return await response.json();
}
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/contacts/show" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Contact retrieved successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Contact not found or doesn't belong to user's organization |
| `422` | Validation error (missing or invalid ID) |
| `500` | Server error |

---

## Error Responses

### Validation Error (422)

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

### Contact Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Contact] {id}"
}
```

### Contact from Different Organization (404)

If you try to access a contact from a different organization:

```json
{
  "message": "No query results for model [App\\Models\\Contact] {id}"
}
```

### No Organization (404)

```json
{
  "message": "No organization found. Please create a contact first."
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

---

## Important Notes

### 1. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the contacts table

### 2. Organization Scope
- Only contacts from the authenticated user's organization can be accessed
- If the contact ID exists but belongs to a different organization, you'll get a 404 error

### 3. Complete Data
- Returns full contact data including all fields
- Use this endpoint to load contact details for viewing or editing

### 4. Use Case
- **Viewing**: Load contact details to display in a detail view
- **Editing**: Load contact data to populate an edit form
- **Referencing**: Get contact details when referenced by other contacts

### 5. Security
- Contact access is restricted to the user's organization
- Users cannot access contacts from other organizations

---

## Best Practices

1. **Error Handling**: Always handle 404 errors gracefully (contact not found)
2. **Loading States**: Show loading indicators while fetching
3. **Caching**: Consider caching contact data if it won't change frequently
4. **Refresh**: Refresh contact data after updates
5. **Validation**: Validate ID before making the request

---

## Example: Complete Contact View/Edit Flow

```javascript
import React, { useState, useEffect } from 'react';

function ContactEdit({ contactId, token, onSave }) {
  const [contact, setContact] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({});

  // Load contact data
  useEffect(() => {
    const fetchContact = async () => {
      try {
        const response = await fetch('/api/contacts/show', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ id: contactId })
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Failed to load contact');
        }

        setContact(data.data);
        setFormData({
          firstName: data.data.first_name,
          lastName: data.data.last_name,
          email: data.data.email || '',
          phone: data.data.phone || '',
          company: data.data.company || '',
          jobTitle: data.data.job_title || '',
          address: data.data.address || '',
          notes: data.data.notes || '',
          referrerId: data.data.referrer_id || null,
          groups: data.data.groups || []
        });
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchContact();
  }, [contactId, token]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('/api/contacts/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          id: contactId,
          first_name: formData.firstName,
          last_name: formData.lastName,
          email: formData.email || null,
          phone: formData.phone || null,
          company: formData.company || null,
          job_title: formData.jobTitle || null,
          address: formData.address || null,
          notes: formData.notes || null,
          referrer_id: formData.referrerId || null,
          groups: formData.groups || []
        })
      });

      const data = await response.json();

      if (response.ok) {
        onSave && onSave(data.data);
      } else {
        alert('Error: ' + data.message);
      }
    } catch (error) {
      alert('Error saving contact: ' + error.message);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!contact) return <div>Contact not found</div>;

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={formData.firstName}
        onChange={(e) => setFormData({...formData, firstName: e.target.value})}
        placeholder="First Name"
        required
      />
      <input
        value={formData.lastName}
        onChange={(e) => setFormData({...formData, lastName: e.target.value})}
        placeholder="Last Name"
        required
      />
      {/* Add other fields */}
      <button type="submit">Save Contact</button>
    </form>
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/contacts/show` with Bearer token and `{"id": 1}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

