# Contacts Favourite API Documentation

## Overview
This API endpoint allows you to mark a contact as a favourite or remove it from favourites. When `is_favourite` is set to `1`, the contact is marked as liked/favourite and a record is created in the database. When `is_favourite` is set to `0`, the contact is removed from favourites and the favourite record is deleted from the database. The contact must belong to the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/favourite`  
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

### Mark as Favourite (Like)
```json
{
  "contact_id": 1,
  "is_favourite": 1
}
```

### Remove from Favourites (Unlike)
```json
{
  "contact_id": 1,
  "is_favourite": 0
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `contact_id` | integer | **Yes** | Contact ID to favourite/unfavourite |
| `is_favourite` | boolean/integer | **Yes** | `1` or `true` to mark as favourite, `0` or `false` to remove from favourites |

**Note:** `is_favourite` accepts both boolean (`true`/`false`) and integer (`1`/`0`) values.

---

## Response Format

### Success Response - Mark as Favourite (200)

```json
{
  "data": {
    "contact_id": 1,
    "is_favourite": 1
  },
  "message": "Contact marked as favourite."
}
```

### Success Response - Remove from Favourites (200)

```json
{
  "data": {
    "contact_id": 1,
    "is_favourite": 0
  },
  "message": "Contact removed from favourites."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | object | Favourite status data |
| `data.contact_id` | integer | Contact ID |
| `data.is_favourite` | integer | `1` if favourite, `0` if not favourite |
| `message` | string | Success message |

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Toggle favourite status for a contact
async function toggleContactFavourite(contactId, isFavourite, token) {
  try {
    const response = await fetch('http://your-api-url/api/contacts/favourite', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        contact_id: contactId,
        is_favourite: isFavourite ? 1 : 0
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update favourite status');
    }

    return data; // Returns { data: { contact_id, is_favourite }, message }
  } catch (error) {
    console.error('Error updating favourite status:', error);
    throw error;
  }
}

// Usage - Mark as favourite
const token = localStorage.getItem('auth_token');
await toggleContactFavourite(1, true, token);
console.log('Contact marked as favourite');

// Usage - Remove from favourites
await toggleContactFavourite(1, false, token);
console.log('Contact removed from favourites');
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

// Toggle favourite status
async function toggleContactFavourite(contactId, isFavourite) {
  try {
    const response = await apiClient.post('/contacts/favourite', {
      contact_id: contactId,
      is_favourite: isFavourite ? 1 : 0
    });
    
    return response.data; // Returns { data: { contact_id, is_favourite }, message }
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await toggleContactFavourite(1, true);
console.log(result.message);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface FavouriteResponse {
  data: {
    contact_id: number;
    is_favourite: number;
  };
  message: string;
}

export function useToggleFavourite(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const toggleFavourite = async (contactId: number, isFavourite: boolean) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<FavouriteResponse>(
        '/api/contacts/favourite',
        {
          contact_id: contactId,
          is_favourite: isFavourite ? 1 : 0
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

  return { toggleFavourite, loading, error };
}

// Usage in component
function ContactCard({ contact, token }: { contact: any; token: string }) {
  const { toggleFavourite, loading, error } = useToggleFavourite(token);
  const [isFavourite, setIsFavourite] = useState(contact.is_favourite === 1);

  const handleToggleFavourite = async () => {
    try {
      const result = await toggleFavourite(contact.id, !isFavourite);
      setIsFavourite(result.data.is_favourite === 1);
      alert(result.message);
    } catch (error: any) {
      alert('Error: ' + error.message);
    }
  };

  return (
    <div>
      <button onClick={handleToggleFavourite} disabled={loading}>
        {loading ? 'Loading...' : isFavourite ? '❤️ Unlike' : '🤍 Like'}
      </button>
    </div>
  );
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function FavouriteButton({ contact, token, onToggle }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [isFavourite, setIsFavourite] = useState(contact.is_favourite === 1);

  const handleToggle = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/contacts/favourite', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          contact_id: contact.id,
          is_favourite: isFavourite ? 0 : 1
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to update favourite status');
      }

      setIsFavourite(data.data.is_favourite === 1);
      
      // Call success callback
      if (onToggle) {
        onToggle(contact.id, data.data.is_favourite === 1);
      }
    } catch (err) {
      setError(err.message);
      alert('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleToggle}
      disabled={loading}
      className="favourite-btn"
      style={{
        backgroundColor: isFavourite ? '#ff6b6b' : '#e0e0e0',
        color: isFavourite ? 'white' : '#333',
        border: 'none',
        padding: '8px 16px',
        borderRadius: '4px',
        cursor: loading ? 'not-allowed' : 'pointer',
        display: 'flex',
        alignItems: 'center',
        gap: '8px'
      }}
    >
      {loading ? (
        'Loading...'
      ) : (
        <>
          {isFavourite ? '❤️ Unlike' : '🤍 Like'}
        </>
      )}
    </button>
  );
}

// Usage in contact list
function ContactsTable({ contacts, token }) {
  const handleFavouriteToggle = (contactId, isFavourite) => {
    console.log(`Contact ${contactId} is now ${isFavourite ? 'favourite' : 'not favourite'}`);
    // Refresh list or update state
  };

  return (
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Favourite</th>
        </tr>
      </thead>
      <tbody>
        {contacts.map(contact => (
          <tr key={contact.id}>
            <td>{contact.first_name} {contact.last_name}</td>
            <td>{contact.email || '-'}</td>
            <td>
              <FavouriteButton
                contact={contact}
                token={token}
                onToggle={handleFavouriteToggle}
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

## Integration Example: Toggle with Feedback

```javascript
// Complete toggle favourite flow with user feedback
async function toggleContactFavouriteWithFeedback(contactId, currentStatus, token) {
  const newStatus = !currentStatus;

  try {
    const response = await fetch('/api/contacts/favourite', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        contact_id: contactId,
        is_favourite: newStatus ? 1 : 0
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update favourite status');
    }

    return {
      success: true,
      is_favourite: data.data.is_favourite === 1,
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
const result = await toggleContactFavouriteWithFeedback(1, false, token);
if (result.success) {
  console.log('Status:', result.is_favourite ? 'Favourite' : 'Not Favourite');
  console.log('Message:', result.message);
} else {
  console.error('Error:', result.error);
}
```

---

## cURL Example

### Mark as Favourite
```bash
curl -X POST "http://your-api-url/api/contacts/favourite" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "contact_id": 1,
    "is_favourite": 1
  }'
```

### Remove from Favourites
```bash
curl -X POST "http://your-api-url/api/contacts/favourite" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "contact_id": 1,
    "is_favourite": 0
  }'
```

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success - Favourite status updated successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Contact not found or doesn't belong to user's organization, or no organization found |
| `422` | Validation error (missing or invalid parameters) |
| `500` | Server error |

---

## Error Responses

### Validation Error (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "contact_id": [
      "The contact id field is required."
    ]
  }
}
```

### Contact Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Contact] 1"
}
```

### No Organization (404)

```json
{
  "message": "No organization found. Please create a contact first."
}
```

### Contact ID Doesn't Exist (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "contact_id": [
      "The selected contact id is invalid."
    ]
  }
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

### 1. Favourite Status Values
- `is_favourite: 1` or `is_favourite: true` → Marks contact as favourite (liked) and **creates/updates** record in database
- `is_favourite: 0` or `is_favourite: false` → Removes contact from favourites (unliked) and **deletes** record from database
- The API accepts both boolean and integer values

### 2. Organization Scope
- Only contacts from the authenticated user's organization can be favourited
- If the contact ID exists but belongs to a different organization, you'll get a 404 error
- Each user's favourite status is stored separately (user-specific favourites)

### 3. Database Behavior
- When marking as favourite (`is_favourite: 1`): The API uses `updateOrCreate`, so it will create a new favourite record if it doesn't exist, or update existing record
- When removing favourite (`is_favourite: 0`): The favourite record is **deleted** from the database entirely (not just updated to false)
- This ensures cleaner database with only active favourites stored

### 4. Contact Resource Integration
- When fetching contacts via `/api/contacts/index` or `/api/contacts/show`, the `is_favourite` field is automatically included
- The `is_favourite` value reflects the current user's favourite status for that contact
- Values are `1` (favourite) or `0` (not favourite)

### 5. Parameter Validation
- `contact_id` is **required** and must be an integer that exists in the contacts table
- `is_favourite` is **required** and accepts boolean or integer values

---

## Best Practices

1. **Toggle Pattern**: Instead of tracking current state, use a toggle function that determines the new state based on current state
2. **Loading States**: Show loading indicators while updating favourite status
3. **Optimistic Updates**: Update UI immediately, then sync with server (with error handling)
4. **User Feedback**: Show success/error messages to the user
5. **Icon Feedback**: Use visual indicators (heart icon, star, etc.) to show favourite status

---

## Example: Favourite Button with Optimistic Updates

```javascript
function FavouriteButton({ contact, token }) {
  const [isFavourite, setIsFavourite] = useState(contact.is_favourite === 1);
  const [loading, setLoading] = useState(false);

  const handleToggle = async () => {
    const newStatus = !isFavourite;
    
    // Optimistic update
    setIsFavourite(newStatus);

    try {
      const response = await fetch('/api/contacts/favourite', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          contact_id: contact.id,
          is_favourite: newStatus ? 1 : 0
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to update favourite status');
      }

      // Sync with server response
      setIsFavourite(data.data.is_favourite === 1);
    } catch (error) {
      // Revert on error
      setIsFavourite(!newStatus);
      alert('Error: ' + error.message);
    }
  };

  return (
    <button
      onClick={handleToggle}
      disabled={loading}
      className="favourite-btn"
      style={{
        fontSize: '20px',
        border: 'none',
        background: 'none',
        cursor: 'pointer',
        padding: '8px'
      }}
    >
      {isFavourite ? '❤️' : '🤍'}
    </button>
  );
}
```

---

## Example: Batch Toggle Favourites

```javascript
// Toggle favourite status for multiple contacts
async function toggleMultipleFavourites(contactIds, isFavourite, token) {
  const promises = contactIds.map(contactId =>
    fetch('/api/contacts/favourite', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        contact_id: contactId,
        is_favourite: isFavourite ? 1 : 0
      })
    })
  );

  try {
    const responses = await Promise.all(promises);
    const results = await Promise.all(
      responses.map(response => response.json())
    );

    return results;
  } catch (error) {
    console.error('Error updating favourites:', error);
    throw error;
  }
}

// Usage
const contactIds = [1, 2, 3];
await toggleMultipleFavourites(contactIds, true, token);
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/contacts/favourite` with Bearer token and request body
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Related Endpoints

- **GET Contacts List**: `/api/contacts/index` - Returns contacts with `is_favourite` status
- **GET Single Contact**: `/api/contacts/show` - Returns contact with `is_favourite` status

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

