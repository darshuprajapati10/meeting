# Contacts Index API Documentation

## Overview
This API endpoint retrieves a paginated list of all saved contacts from the user's organization. It supports pagination, search, and filtering by contact groups. Perfect for displaying contacts in a table or list view with pagination controls.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/index`  
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

### Basic Request (No Parameters)

```json
{}
```

### Request with Pagination

```json
{
  "page": 1,
  "per_page": 15
}
```

### Request with Search

```json
{
  "page": 1,
  "per_page": 15,
  "search": "John"
}
```

### Request with Group Filter

```json
{
  "page": 1,
  "per_page": 15,
  "group": "Clients"
}
```

### Request with All Parameters

```json
{
  "page": 2,
  "per_page": 20,
  "search": "john",
  "group": "Clients"
}
```

---

## Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number (starts from 1) |
| `per_page` | integer | No | 15 | Number of contacts per page (1-100) |
| `search` | string | No | - | Search term (searches in first_name, last_name, email, phone, company) |
| `group` | string | No | - | Filter by contact group (e.g., "Clients", "Team") |

---

## Response Format

### Success Response (200)

```json
{
  "data": [
    {
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
      "notes": "Additional notes...",
      "created_by": 1,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "organization_id": 1,
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane.smith@example.com",
      "phone": "+1 (555) 987-6543",
      "company": "Tech Corp",
      "job_title": "Product Manager",
      "referrer_id": 1,
      "groups": ["Partners"],
      "address": "456 Oak Ave",
      "notes": null,
      "created_by": 1,
      "created_at": "2025-01-14T09:15:00.000000Z",
      "updated_at": "2025-01-14T09:15:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 9,
    "per_page": 15,
    "to": 15,
    "total": 135
  },
  "message": "Contacts retrieved successfully."
}
```

### Response Structure

| Field | Type | Description |
|-------|------|-------------|
| `data` | array | Array of contact objects (ContactResource) |
| `meta` | object | Pagination metadata |
| `meta.current_page` | integer | Current page number |
| `meta.from` | integer | First item number on current page |
| `meta.last_page` | integer | Last page number (total pages) |
| `meta.per_page` | integer | Number of items per page |
| `meta.to` | integer | Last item number on current page |
| `meta.total` | integer | Total number of contacts |
| `message` | string | Success message |

---

## Pagination Example (9 Pages)

If you have 135 contacts with 15 per page, you'll have 9 pages:

**Page 1:** Contacts 1-15
```json
{
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 9,
    "per_page": 15,
    "to": 15,
    "total": 135
  }
}
```

**Page 2:** Contacts 16-30
```json
{
  "meta": {
    "current_page": 2,
    "from": 16,
    "last_page": 9,
    "per_page": 15,
    "to": 30,
    "total": 135
  }
}
```

**Page 9:** Contacts 121-135
```json
{
  "meta": {
    "current_page": 9,
    "from": 121,
    "last_page": 9,
    "per_page": 15,
    "to": 135,
    "total": 135
  }
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Get paginated contacts
async function getContacts(page = 1, perPage = 15, search = '', group = '') {
  try {
    const requestBody = {
      page: page,
      per_page: perPage
    };
    
    if (search) {
      requestBody.search = search;
    }
    
    if (group) {
      requestBody.group = group;
    }

    const response = await fetch('http://your-api-url/api/contacts/index', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(requestBody)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve contacts');
    }

    return {
      contacts: data.data,
      pagination: data.meta
    };
  } catch (error) {
    console.error('Error fetching contacts:', error);
    throw error;
  }
}

// Usage
const token = localStorage.getItem('auth_token');
const result = await getContacts(1, 15, '', '');
console.log('Contacts:', result.contacts);
console.log('Pagination:', result.pagination);
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

// Get paginated contacts
async function getContacts(page = 1, perPage = 15, search = '', group = '') {
  try {
    const response = await apiClient.post('/contacts/index', {
      page,
      per_page: perPage,
      ...(search && { search }),
      ...(group && { group })
    });
    
    return {
      contacts: response.data.data,
      pagination: response.data.meta
    };
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
const result = await getContacts(1, 15, 'john', 'Clients');
console.log('Contacts:', result.contacts);
console.log('Total:', result.pagination.total);
console.log('Pages:', result.pagination.last_page);
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

interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
}

interface ContactsResponse {
  data: Contact[];
  meta: PaginationMeta;
  message: string;
}

export function useContactsList(
  token: string,
  page: number = 1,
  perPage: number = 15,
  search: string = '',
  group: string = ''
) {
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [pagination, setPagination] = useState<PaginationMeta | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  useEffect(() => {
    const fetchContacts = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await axios.post<ContactsResponse>(
          '/api/contacts/index',
          {
            page,
            per_page: perPage,
            ...(search && { search }),
            ...(group && { group })
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
          }
        );

        setContacts(response.data.data);
        setPagination(response.data.meta);
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
  }, [token, page, perPage, search, group]);

  return { contacts, pagination, loading, error };
}

// Usage in component
function ContactsList() {
  const token = localStorage.getItem('auth_token') || '';
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const { contacts, pagination, loading, error } = useContactsList(token, page, 15, search);

  if (loading) return <div>Loading contacts...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!pagination) return null;

  return (
    <div>
      <input
        type="text"
        placeholder="Search contacts..."
        value={search}
        onChange={(e) => {
          setSearch(e.target.value);
          setPage(1); // Reset to page 1 when searching
        }}
      />

      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Company</th>
            <th>Groups</th>
          </tr>
        </thead>
        <tbody>
          {contacts.map(contact => (
            <tr key={contact.id}>
              <td>{contact.first_name} {contact.last_name}</td>
              <td>{contact.email || '-'}</td>
              <td>{contact.phone || '-'}</td>
              <td>{contact.company || '-'}</td>
              <td>{contact.groups.join(', ') || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Pagination Controls */}
      <div>
        <button
          onClick={() => setPage(page - 1)}
          disabled={page === 1}
        >
          Previous
        </button>
        
        <span>
          Page {pagination.current_page} of {pagination.last_page}
          ({pagination.total} total contacts)
        </span>
        
        <button
          onClick={() => setPage(page + 1)}
          disabled={page === pagination.last_page}
        >
          Next
        </button>
      </div>
    </div>
  );
}
```

### Complete React Component with Pagination

```javascript
import React, { useState, useEffect } from 'react';

function ContactsTable({ token }) {
  const [contacts, setContacts] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  const [search, setSearch] = useState('');
  const [group, setGroup] = useState('');

  useEffect(() => {
    fetchContacts();
  }, [page, perPage, search, group]);

  const fetchContacts = async () => {
    setLoading(true);
    
    try {
      const response = await fetch('/api/contacts/index', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          page,
          per_page: perPage,
          ...(search && { search }),
          ...(group && { group })
        })
      });

      const data = await response.json();
      
      if (response.ok) {
        setContacts(data.data);
        setPagination(data.meta);
      } else {
        console.error('Error:', data.message);
      }
    } catch (error) {
      console.error('Network error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= pagination.last_page) {
      setPage(newPage);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  const handleSearchChange = (value) => {
    setSearch(value);
    setPage(1); // Reset to first page when searching
  };

  if (loading && contacts.length === 0) {
    return <div>Loading contacts...</div>;
  }

  return (
    <div>
      {/* Search and Filter */}
      <div style={{ marginBottom: '20px' }}>
        <input
          type="text"
          placeholder="Search contacts..."
          value={search}
          onChange={(e) => handleSearchChange(e.target.value)}
          style={{ padding: '8px', marginRight: '10px' }}
        />
        
        <select
          value={group}
          onChange={(e) => {
            setGroup(e.target.value);
            setPage(1);
          }}
          style={{ padding: '8px' }}
        >
          <option value="">All Groups</option>
          <option value="Clients">Clients</option>
          <option value="Partners">Partners</option>
          <option value="Team">Team</option>
          {/* Add more groups */}
        </select>
      </div>

      {/* Contacts Table */}
      {loading ? (
        <div>Loading...</div>
      ) : (
        <>
          <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Company</th>
                <th>Groups</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              {contacts.map(contact => (
                <tr key={contact.id}>
                  <td>{contact.first_name} {contact.last_name}</td>
                  <td>{contact.email || '-'}</td>
                  <td>{contact.phone || '-'}</td>
                  <td>{contact.company || '-'}</td>
                  <td>{contact.groups.join(', ') || '-'}</td>
                  <td>{new Date(contact.created_at).toLocaleDateString()}</td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Pagination */}
          {pagination && (
            <div style={{ marginTop: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                Showing {pagination.from} to {pagination.to} of {pagination.total} contacts
              </div>
              
              <div>
                <button
                  onClick={() => handlePageChange(1)}
                  disabled={page === 1}
                >
                  First
                </button>
                
                <button
                  onClick={() => handlePageChange(page - 1)}
                  disabled={page === 1}
                >
                  Previous
                </button>
                
                <span style={{ margin: '0 10px' }}>
                  Page {pagination.current_page} of {pagination.last_page}
                </span>
                
                <button
                  onClick={() => handlePageChange(page + 1)}
                  disabled={page === pagination.last_page}
                >
                  Next
                </button>
                
                <button
                  onClick={() => handlePageChange(pagination.last_page)}
                  disabled={page === pagination.last_page}
                >
                  Last
                </button>
              </div>

              <div>
                <select
                  value={perPage}
                  onChange={(e) => {
                    setPerPage(Number(e.target.value));
                    setPage(1);
                  }}
                >
                  <option value="10">10 per page</option>
                  <option value="15">15 per page</option>
                  <option value="20">20 per page</option>
                  <option value="50">50 per page</option>
                  <option value="100">100 per page</option>
                </select>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
}
```

---

## cURL Examples

### Basic Request

```bash
curl -X POST "http://your-api-url/api/contacts/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

### Request with Pagination

```bash
curl -X POST "http://your-api-url/api/contacts/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 2,
    "per_page": 20
  }'
```

### Request with Search

```bash
curl -X POST "http://your-api-url/api/contacts/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "search": "john"
  }'
```

### Request with Group Filter

```bash
curl -X POST "http://your-api-url/api/contacts/index" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "page": 1,
    "per_page": 15,
    "group": "Clients"
  }'
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

### No Organization (200 with empty data)

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
  "message": "No organization found. Please create a contact first."
}
```

---

## Important Notes

### 1. Pagination
- Default: 15 contacts per page
- Minimum: 1 per page
- Maximum: 100 per page
- Page numbers start from 1 (not 0)

### 2. Search
- Searches in: `first_name`, `last_name`, `email`, `phone`, `company`
- Case-insensitive partial match
- Example: searching "john" will find "John Doe" and "johnson@example.com"

### 3. Group Filter
- Filter by exact group name (case-sensitive)
- Valid groups: "Clients", "Partners", "Team", "Family", "Prospects", "Vendors", "Friends", "Colleagues"
- Example: `{"group": "Clients"}` returns only contacts with "Clients" in their groups array

### 4. Sorting
- Contacts are sorted by `created_at` in descending order (newest first)
- Most recently created contacts appear first

### 5. Organization Scope
- Only contacts from the authenticated user's organization are returned
- Contacts from other organizations are not accessible

---

## Best Practices

1. **Page Size**: Use 15-20 per page for optimal performance and UX
2. **Search Debouncing**: Debounce search input to avoid too many API calls
3. **Caching**: Consider caching contacts for better performance
4. **Loading States**: Show loading indicators while fetching
5. **Error Handling**: Display user-friendly error messages
6. **Pagination UX**: Show page numbers, total pages, and navigation buttons

---

## Example: Building Pagination UI

```javascript
// Calculate page numbers to show
function getPageNumbers(currentPage, lastPage) {
  const pages = [];
  const maxVisible = 5;
  
  if (lastPage <= maxVisible) {
    // Show all pages if less than maxVisible
    for (let i = 1; i <= lastPage; i++) {
      pages.push(i);
    }
  } else {
    // Show smart pagination
    if (currentPage <= 3) {
      // Near beginning
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
    } else if (currentPage >= lastPage - 2) {
      // Near end
      for (let i = lastPage - 4; i <= lastPage; i++) {
        pages.push(i);
      }
    } else {
      // In middle
      for (let i = currentPage - 2; i <= currentPage + 2; i++) {
        pages.push(i);
      }
    }
  }
  
  return pages;
}

// Usage
const pageNumbers = getPageNumbers(pagination.current_page, pagination.last_page);
// Output: [1, 2, 3, 4, 5] or [3, 4, 5, 6, 7] or [5, 6, 7, 8, 9]
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/contacts/index` with Bearer token
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

