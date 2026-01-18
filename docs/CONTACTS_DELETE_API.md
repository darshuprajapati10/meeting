# Contacts Delete API Documentation

## Overview
This API endpoint allows you to delete a contact by its ID. The contact must belong to the authenticated user's organization. Once deleted, the contact is permanently removed from the database.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/delete`  
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
| `id` | integer | **Yes** | Contact ID to delete |

---

## Response Format

### Success Response (200)

```json
{
  "message": "Contact deleted successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `message` | string | Success message |

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Delete contact by ID
async function deleteContact(contactId, token) {
  try {
    const response = await fetch('http://your-api-url/api/contacts/delete', {
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
      throw new Error(data.message || 'Failed to delete contact');
    }

    return data; // Returns { message: "Contact deleted successfully." }
  } catch (error) {
    console.error('Error deleting contact:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
await deleteContact(1, token);
console.log('Contact deleted successfully');
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

// Delete contact by ID
async function deleteContact(contactId) {
  try {
    const response = await apiClient.post('/contacts/delete', {
      id: contactId
    });
    
    return response.data; // Returns { message: "Contact deleted successfully." }
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await deleteContact(1);
console.log(result.message);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface DeleteContactResponse {
  message: string;
}

export function useDeleteContact(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const deleteContact = async (contactId: number) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<DeleteContactResponse>(
        '/api/contacts/delete',
        { id: contactId },
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

  return { deleteContact, loading, error };
}

// Usage in component
function ContactList({ token }: { token: string }) {
  const { deleteContact, loading, error } = useDeleteContact(token);

  const handleDelete = async (contactId: number) => {
    if (window.confirm('Are you sure you want to delete this contact?')) {
      try {
        const result = await deleteContact(contactId);
        alert(result.message);
        // Refresh the contact list
        window.location.reload();
      } catch (error) {
        alert('Error deleting contact: ' + error.message);
      }
    }
  };

  return (
    <div>
      {/* Your contact list */}
      <button onClick={() => handleDelete(1)} disabled={loading}>
        {loading ? 'Deleting...' : 'Delete Contact'}
      </button>
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function ContactDeleteButton({ contactId, contactName, token, onDeleteSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleDelete = async () => {
    if (!window.confirm(`Are you sure you want to delete "${contactName}"?`)) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/contacts/delete', {
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
        throw new Error(data.message || 'Failed to delete contact');
      }

      // Call success callback to refresh list
      if (onDeleteSuccess) {
        onDeleteSuccess();
      }

      alert(data.message || 'Contact deleted successfully');
    } catch (err) {
      setError(err.message);
      alert('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleDelete}
      disabled={loading}
      className="delete-btn"
      style={{
        backgroundColor: '#dc3545',
        color: 'white',
        border: 'none',
        padding: '8px 16px',
        borderRadius: '4px',
        cursor: loading ? 'not-allowed' : 'pointer'
      }}
    >
      {loading ? 'Deleting...' : 'Delete'}
    </button>
  );
}

// Usage in contact list
function ContactsTable({ contacts, token, onContactDeleted }) {
  return (
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {contacts.map(contact => (
          <tr key={contact.id}>
            <td>{contact.first_name} {contact.last_name}</td>
            <td>{contact.email || '-'}</td>
            <td>
              <ContactDeleteButton
                contactId={contact.id}
                contactName={`${contact.first_name} ${contact.last_name}`}
                token={token}
                onDeleteSuccess={onContactDeleted}
              />
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
```

---

## Integration Example: Delete with Confirmation

```javascript
// Complete delete flow with confirmation
async function deleteContactWithConfirmation(contactId, contactName, token) {
  // Show confirmation dialog
  const confirmed = window.confirm(
    `Are you sure you want to delete "${contactName}"?\n\nThis action cannot be undone.`
  );

  if (!confirmed) {
    return { cancelled: true };
  }

  try {
    const response = await fetch('/api/contacts/delete', {
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
      throw new Error(data.message || 'Failed to delete contact');
    }

    return {
      success: true,
      message: data.message
    };
  } catch (error) {
    return {
      success: false,
      error: error.message
    };
  }
}

// Usage
const result = await deleteContactWithConfirmation(1, 'John Doe', token);
if (result.success) {
  console.log('Deleted:', result.message);
} else if (!result.cancelled) {
  console.error('Error:', result.error);
}
```

---

## cURL Example

```bash
curl -X POST "http://your-api-url/api/contacts/delete" \
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
| `200` | Success - Contact deleted successfully |
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
  "message": "Contact not found or you do not have permission to delete it."
}
```

### Contact ID Doesn't Exist (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "id": [
      "The selected id is invalid."
    ]
  }
}
```

### No Organization (404)

```json
{
  "message": "No organization found."
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

### 1. Permanent Deletion
- ⚠️ **Warning**: Deleting a contact is **permanent** and cannot be undone
- The contact will be permanently removed from the database
- Consider implementing a "soft delete" or confirmation dialog in your frontend

### 2. Organization Scope
- Only contacts from the authenticated user's organization can be deleted
- If the contact ID exists but belongs to a different organization, you'll get a 404 error

### 3. Referrer Relationships
- If other contacts have this contact as a referrer (`referrer_id`), those relationships will be set to `null` automatically (due to database foreign key constraints)

### 4. ID Validation
- The `id` parameter is **required**
- Must be an integer
- Must exist in the contacts table

### 5. Security
- Contact deletion is restricted to the user's organization
- Users cannot delete contacts from other organizations

---

## Best Practices

1. **Confirmation Dialog**: Always show a confirmation dialog before deleting
2. **Error Handling**: Handle 404 and 422 errors gracefully
3. **Loading States**: Show loading indicators while deleting
4. **Refresh List**: Refresh the contact list after successful deletion
5. **User Feedback**: Show success/error messages to the user

---

## Example: Delete Button with Confirmation

```javascript
function DeleteContactButton({ contact, token, onDeleted }) {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    // Show confirmation
    const confirmed = window.confirm(
      `Are you sure you want to delete "${contact.first_name} ${contact.last_name}"?\n\nThis action cannot be undone.`
    );

    if (!confirmed) return;

    setLoading(true);

    try {
      const response = await fetch('/api/contacts/delete', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id: contact.id })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete contact');
      }

      // Show success message
      alert('Contact deleted successfully');
      
      // Call callback to refresh list
      if (onDeleted) {
        onDeleted();
      }
    } catch (error) {
      alert('Error: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleDelete}
      disabled={loading}
      className="btn btn-danger"
    >
      {loading ? 'Deleting...' : 'Delete'}
    </button>
  );
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/contacts/delete` with Bearer token and `{"id": 1}`
- **cURL**: Use the example command above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

