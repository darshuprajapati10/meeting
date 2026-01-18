# Contacts Bulk Import API Documentation

## Overview
This API endpoint allows you to bulk import contacts from a CSV file or by sending multiple contacts in a single request. The Flutter application parses the CSV file and sends the contacts data to this endpoint for processing. The endpoint validates, processes, and saves all contacts, returning a summary of successful and failed imports with detailed error information.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/contacts/import`  
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

The API supports two request formats:

### Format 1: Standard Format (Array of Contacts)

The request body should be a JSON object containing an array of contacts:

```json
{
  "contacts": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "company": "Acme Corp",
      "job_title": "Software Engineer",
      "groups": ["Clients", "VIP"]
    },
    {
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane.smith@example.com",
      "phone": "+0987654321",
      "company": "Tech Solutions",
      "job_title": "Product Manager",
      "groups": ["Partners"]
    }
  ]
}
```

### Format 2: Single Contact at Root Level

You can also send a single contact directly at the root level (useful for testing or single imports):

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "+1234567890",
  "company": "Acme Corp",
  "job_title": "Software Engineer",
  "groups": ["Clients", "VIP"]
}
```

**Note:** The API automatically detects if `contacts` array is missing and wraps the root-level fields into a contacts array.

---

## Field Specifications

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| contacts | array | Yes* | Array of contact objects | Must contain at least one contact (*optional if sending single contact at root) |
| contacts[].first_name | string | Yes | Contact's first name | Max 255 characters |
| contacts[].last_name | string | Yes | Contact's last name | Max 255 characters |
| contacts[].email | string | No | Contact's email address | Valid email format, unique per organization |
| contacts[].phone | string | No | Contact's phone number | Max 50 characters |
| contacts[].company | string | No | Contact's company name | Max 255 characters |
| contacts[].job_title | string | No | Contact's job title | Max 255 characters |
| contacts[].groups | array | No | Array of group names | Array of strings, max 50 characters per group |

---

## Response Format

### Success Response (200 - OK)

When all contacts are imported successfully:

```json
{
  "success": true,
  "message": "Contacts imported successfully",
  "data": {
    "total": 10,
    "successful": 10,
    "failed": 0,
    "errors": []
  }
}
```

### Partial Success Response (207 - Multi-Status)

If some contacts fail but others succeed:

```json
{
  "success": true,
  "message": "Some contacts imported successfully",
  "data": {
    "total": 10,
    "successful": 8,
    "failed": 2,
    "errors": [
      {
        "row": 3,
        "contact": {
          "first_name": "Invalid",
          "last_name": "Contact",
          "email": "invalid-email"
        },
        "message": "The email must be a valid email address."
      },
      {
        "row": 7,
        "contact": {
          "first_name": "Duplicate",
          "last_name": "User",
          "email": "john.doe@example.com"
        },
        "message": "A contact with this email already exists."
      }
    ]
  }
}
```

### Error Response (422 - All Contacts Failed)

If all contacts fail validation:

```json
{
  "success": false,
  "message": "All contacts failed validation",
  "data": {
    "total": 3,
    "successful": 0,
    "failed": 3,
    "errors": [
      {
        "row": 1,
        "contact": {
          "first_name": "",
          "last_name": "Doe",
          "email": "john.doe@example.com"
        },
        "message": "The first name field is required."
      },
      {
        "row": 2,
        "contact": {
          "first_name": "Jane",
          "last_name": "",
          "email": "jane@example.com"
        },
        "message": "The last name field is required."
      },
      {
        "row": 3,
        "contact": {
          "first_name": "Bob",
          "last_name": "Smith",
          "email": "not-an-email"
        },
        "message": "The email must be a valid email address."
      }
    ]
  }
}
```

### Error Response (400 - Bad Request)

If request format is invalid:

```json
{
  "success": false,
  "message": "Invalid request format",
  "errors": {
    "contacts": [
      "The contacts must be an array."
    ]
  }
}
```

### Error Response (404 - Organization Not Found)

If user doesn't have an organization:

```json
{
  "success": false,
  "message": "No organization found. Please create an organization first.",
  "errors": null
}
```

### Error Response (500 - Server Error)

```json
{
  "success": false,
  "message": "An error occurred while importing contacts",
  "errors": null
}
```

---

## Response Fields

### Main Response Object

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Whether the request was successful (true if at least one contact imported) |
| message | string | Human-readable message |
| data | object | Response data (only present on success/partial success) |
| errors | object/array | Error details (validation errors or error array) |

### Data Object (Success Response)

| Field | Type | Description |
|-------|------|-------------|
| total | integer | Total number of contacts processed |
| successful | integer | Number of contacts successfully imported |
| failed | integer | Number of contacts that failed to import |
| errors | array | Array of error objects (only for failed contacts) |

### Error Object (in errors array)

| Field | Type | Description |
|-------|------|-------------|
| row | integer | Row number in the original CSV/array (1-indexed) |
| contact | object | The contact data that failed (for reference) |
| message | string | Error message explaining why the import failed |

---

## Validation Rules

### Required Fields
- **first_name** - Must be present and non-empty (after trimming)
- **last_name** - Must be present and non-empty (after trimming)

### Optional Fields
- **email** - If provided, must be valid email format and unique within the organization (case-insensitive)
- **phone** - If provided, max 50 characters
- **company** - If provided, max 255 characters
- **job_title** - If provided, max 255 characters
- **groups** - If provided, must be an array of strings (max 50 characters per group)

### Business Rules

1. **Email Uniqueness**: 
   - Each email address must be unique within the organization
   - Email comparison is case-insensitive
   - Duplicates within the same import batch are detected and skipped
   - Existing contacts with the same email in the database are skipped (not updated)

2. **Organization Context**: 
   - All contacts are imported for the authenticated user's organization
   - If user doesn't have an organization, the request fails with 404

3. **Groups**: 
   - Groups are stored as a JSON array
   - Empty groups are filtered out
   - Group names are trimmed and validated (max 50 characters)
   - Groups are case-sensitive

4. **Duplicate Handling**: 
   - Duplicates are **skipped** (not updated)
   - Error is returned in the errors array with message: "A contact with this email already exists."

5. **Processing**: 
   - Contacts are processed sequentially
   - If one contact fails, processing continues with the next contact
   - All errors are collected and returned in the response

---

## Processing Logic

### Flow

1. **Validate Request Structure**
   - Check if contacts array exists OR contact fields at root level
   - If root level fields detected, wrap in contacts array
   - Validate that contacts is an array with at least one item

2. **Process Each Contact**
   - Validate required fields (first_name, last_name)
   - Validate email format if provided
   - Check for duplicate emails within the batch
   - Check for existing contacts with same email in database
   - Normalize and validate groups
   - Save contact to database

3. **Track Results**
   - Count successful imports
   - Track failed imports with row number and error message
   - Continue processing even if some contacts fail

4. **Return Response**
   - Include total count
   - Include successful count
   - Include failed count
   - Include detailed error information for failed contacts
   - Return appropriate HTTP status code (200, 207, or 422)

---

## Example Use Cases

### Example 1: Successful Import (All Contacts)

**Request:**
```json
{
  "contacts": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "company": "Acme Corp",
      "job_title": "Software Engineer",
      "groups": ["Clients"]
    },
    {
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane.smith@example.com",
      "company": "Tech Solutions",
      "groups": ["Partners", "VIP"]
    }
  ]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Contacts imported successfully",
  "data": {
    "total": 2,
    "successful": 2,
    "failed": 0,
    "errors": []
  }
}
```

### Example 2: Partial Success (Some Failures)

**Request:**
```json
{
  "contacts": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "phone": "+1234567890"
    },
    {
      "first_name": "Invalid",
      "last_name": "Contact",
      "email": "not-an-email"
    },
    {
      "first_name": "Duplicate",
      "last_name": "User",
      "email": "john.doe@example.com"
    }
  ]
}
```

**Response (207):**
```json
{
  "success": true,
  "message": "Some contacts imported successfully",
  "data": {
    "total": 3,
    "successful": 1,
    "failed": 2,
    "errors": [
      {
        "row": 2,
        "contact": {
          "first_name": "Invalid",
          "last_name": "Contact",
          "email": "not-an-email"
        },
        "message": "The email must be a valid email address."
      },
      {
        "row": 3,
        "contact": {
          "first_name": "Duplicate",
          "last_name": "User",
          "email": "john.doe@example.com"
        },
        "message": "A contact with this email already exists."
      }
    ]
  }
}
```

### Example 3: Single Contact at Root Level

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "+1234567890",
  "company": "Acme Corp",
  "job_title": "Software Engineer",
  "groups": ["Clients", "VIP"]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Contacts imported successfully",
  "data": {
    "total": 1,
    "successful": 1,
    "failed": 0,
    "errors": []
  }
}
```

### Example 4: All Contacts Fail

**Request:**
```json
{
  "contacts": [
    {
      "first_name": "",
      "last_name": "Doe",
      "email": "john.doe@example.com"
    }
  ]
}
```

**Response (422):**
```json
{
  "success": false,
  "message": "All contacts failed validation",
  "data": {
    "total": 1,
    "successful": 0,
    "failed": 1,
    "errors": [
      {
        "row": 1,
        "contact": {
          "first_name": "",
          "last_name": "Doe",
          "email": "john.doe@example.com"
        },
        "message": "The first name field is required."
      }
    ]
  }
}
```

---

## Error Handling

### Common Error Scenarios

1. **Missing Required Fields**
   - Error message: "The first name field is required." or "The last name field is required."
   - Contact is skipped, processing continues

2. **Invalid Email Format**
   - Error message: "The email must be a valid email address."
   - Contact is skipped, processing continues

3. **Duplicate Email (in batch)**
   - Error message: "A contact with this email already exists in the import batch."
   - Contact is skipped, processing continues

4. **Duplicate Email (in database)**
   - Error message: "A contact with this email already exists."
   - Contact is skipped (not updated), processing continues

5. **Database Errors**
   - Error message: "An error occurred while saving contact: [error details]"
   - Contact is skipped, processing continues
   - Error is logged for debugging

6. **Invalid Groups Format**
   - Groups are normalized automatically
   - Empty or invalid groups are filtered out
   - Processing continues

---

## Testing with Postman

### 1. Import Single Contact (Root Level Format)

**Method:** POST  
**URL:** `http://localhost:8000/api/contacts/import`  
**Headers:**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "first_name": "Test",
  "last_name": "User",
  "email": "test@example.com",
  "phone": "+1234567890",
  "company": "Test Company",
  "job_title": "Tester",
  "groups": ["Test Group"]
}
```

### 2. Import Multiple Contacts (Standard Format)

**Body:**
```json
{
  "contacts": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "company": "Acme Corp",
      "job_title": "Engineer",
      "groups": ["Clients"]
    },
    {
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane@example.com",
      "company": "Tech Inc",
      "groups": ["Partners"]
    },
    {
      "first_name": "Bob",
      "last_name": "Johnson",
      "email": "bob@example.com",
      "phone": "+0987654321"
    }
  ]
}
```

### 3. Test Validation Errors

**Body (with invalid data):**
```json
{
  "contacts": [
    {
      "first_name": "",
      "last_name": "Doe",
      "email": "invalid-email"
    },
    {
      "first_name": "Jane",
      "last_name": "",
      "email": "jane@example.com"
    },
    {
      "first_name": "Bob",
      "last_name": "Johnson",
      "email": "bob@example.com"
    }
  ]
}
```

### 4. Test Duplicate Emails

**Body:**
```json
{
  "contacts": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com"
    },
    {
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "john@example.com"
    }
  ]
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
async function importContacts(token, contacts) {
  try {
    const response = await fetch('http://localhost:8000/api/contacts/import', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        contacts: contacts
      }),
    });

    const data = await response.json();

    if (response.ok || response.status === 207) {
      console.log(`Imported ${data.data.successful} out of ${data.data.total} contacts`);
      
      if (data.data.errors.length > 0) {
        console.log('Errors:', data.data.errors);
      }
      
      return data;
    } else {
      throw new Error(data.message || 'Import failed');
    }
  } catch (error) {
    console.error('Error importing contacts:', error);
    throw error;
  }
}

// Usage
const contacts = [
  {
    first_name: 'John',
    last_name: 'Doe',
    email: 'john@example.com',
    phone: '+1234567890',
    company: 'Acme Corp',
    job_title: 'Engineer',
    groups: ['Clients']
  },
  {
    first_name: 'Jane',
    last_name: 'Smith',
    email: 'jane@example.com',
    groups: ['Partners']
  }
];

importContacts('YOUR_TOKEN', contacts);
```

### Axios Example

```javascript
import axios from 'axios';

async function importContacts(token, contacts) {
  try {
    const response = await axios.post(
      'http://localhost:8000/api/contacts/import',
      { contacts },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      }
    );

    const { data } = response;
    
    console.log(`Imported ${data.data.successful} out of ${data.data.total} contacts`);
    
    if (data.data.errors.length > 0) {
      console.log('Errors:', data.data.errors);
    }
    
    return data;
  } catch (error) {
    if (error.response) {
      console.error('Import failed:', error.response.data);
      throw error.response.data;
    }
    throw error;
  }
}

// Usage
importContacts('YOUR_TOKEN', contacts);
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface Contact {
  first_name: string;
  last_name: string;
  email?: string;
  phone?: string;
  company?: string;
  job_title?: string;
  groups?: string[];
}

interface ImportError {
  row: number;
  contact: Contact;
  message: string;
}

interface ImportResponse {
  success: boolean;
  message: string;
  data: {
    total: number;
    successful: number;
    failed: number;
    errors: ImportError[];
  };
}

export function useImportContacts(token: string) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const importContacts = async (contacts: Contact[]): Promise<ImportResponse> => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post<ImportResponse>(
        '/api/contacts/import',
        { contacts },
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

  return { importContacts, loading, error };
}

// Usage in component
function ContactImportForm({ token }: { token: string }) {
  const { importContacts, loading, error } = useImportContacts(token);
  const [result, setResult] = useState<ImportResponse | null>(null);

  const handleImport = async (contacts: Contact[]) => {
    try {
      const response = await importContacts(contacts);
      setResult(response);
      
      if (response.data.errors.length > 0) {
        console.log('Some contacts failed:', response.data.errors);
      }
    } catch (err) {
      console.error('Import error:', err);
    }
  };

  return (
    <div>
      {/* Your form UI */}
      {result && (
        <div>
          <p>Total: {result.data.total}</p>
          <p>Successful: {result.data.successful}</p>
          <p>Failed: {result.data.failed}</p>
          {result.data.errors.map((err, idx) => (
            <div key={idx}>
              Row {err.row}: {err.message}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

---

## Important Notes

1. **Row Numbering**: The `row` field in error objects is 1-indexed (first contact is row 1, second is row 2, etc.) to match CSV row numbers.

2. **Email Uniqueness**: 
   - Email comparison is case-insensitive
   - Duplicates are skipped, not updated
   - Check happens both within the batch and against existing database records

3. **Groups**: 
   - Groups are stored as JSON array
   - Empty groups are automatically filtered out
   - Group names are trimmed and validated (max 50 characters)
   - Groups are case-sensitive

4. **Processing**: 
   - Contacts are processed sequentially
   - If one contact fails, processing continues with the next
   - All errors are collected and returned together

5. **Performance**: 
   - For large imports (100+ contacts), consider processing in batches
   - Each contact is validated and saved individually
   - Database queries are optimized for duplicate checking

6. **Response Status Codes**:
   - `200`: All contacts imported successfully
   - `207`: Some contacts imported, some failed (Multi-Status)
   - `422`: All contacts failed validation
   - `400`: Invalid request format
   - `404`: Organization not found
   - `500`: Server error

---

## Related APIs

- `POST /api/contacts/save` - Save a single contact
- `POST /api/contacts/index` - List all contacts
- `POST /api/contacts/show` - Get single contact details
- `POST /api/contacts/delete` - Delete a contact
- `POST /api/contact-groups` - Get available contact groups

---

## Flutter Integration Notes

The Flutter application:
1. Parses the CSV file client-side
2. Validates CSV format (headers must match expected format)
3. Converts CSV rows to contact objects
4. Sends all contacts in a single API request (or in batches for large files)
5. Displays preview before import
6. Shows summary with success/failure counts
7. Displays expandable error details for failed imports
8. Auto-refreshes contact list after successful import

The Flutter app expects:
- Response status code 200 or 207 for success
- `data.successful` and `data.failed` counts
- `data.errors` array with row numbers and error messages
- Error objects with `row`, `contact`, and `message` fields

---

## Support

For questions or issues regarding this API endpoint, please contact the development team.

