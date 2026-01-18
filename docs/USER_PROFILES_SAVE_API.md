# User Profiles Save API Documentation

## Overview
This API endpoint allows you to create or update user profiles in the `user_profiles` table. When an `id` is provided, it updates an existing user profile. When `id` is not provided, it creates a new user profile. The `organization_id` is automatically set from the authenticated user's organization.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/user-profiles/save`  
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

### Create New User Profile

```json
{
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
  "timezone": "America/New_York"
}
```

### Update Existing User Profile

```json
{
  "id": 1,
  "user_id": 1,
  "first_name": "John",
  "last_name": "Doe Updated",
  "bio": "Updated bio information",
  "email_address": "john.doe.updated@example.com",
  "address": "456 New Street, City, State 12345",
  "company": "New Tech Solutions Inc.",
  "phone": "+1 (555) 987-6543",
  "job_title": "Lead Developer",
  "department": "Engineering",
  "timezone": "America/Los_Angeles"
}
```

### Create with Minimal Required Fields

```json
{
  "user_id": 1,
  "first_name": "Jane",
  "last_name": "Smith",
  "email_address": "jane.smith@example.com",
  "address": "789 Oak Avenue",
  "company": "ABC Corp"
}
```

---

## Field Specifications

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | User profile ID (for updates only) | Must exist in `user_profiles` table if provided |
| `user_id` | integer | Yes | ID of the user this profile belongs to | Must exist in `users` table |
| `first_name` | string | Yes | User's first name | Max 255 characters |
| `last_name` | string | Yes | User's last name | Max 255 characters |
| `bio` | string | No | User's biography/description | No max length specified |
| `email_address` | string | Yes | User's email address | Must be valid email format, must be unique in `user_profiles` table |
| `address` | string | Yes | User's address | Max 500 characters |
| `company` | string | Yes | User's company name | Max 255 characters |
| `phone` | string | No | User's phone number | Max 20 characters |
| `job_title` | string | No | User's job title | Max 255 characters |
| `department` | string | No | User's department | Max 255 characters |
| `timezone` | string | No | User's timezone | Max 100 characters |

**Note:** 
- `organization_id` is automatically set from the authenticated user's organization and cannot be manually specified.
- When updating, the `email_address` must be unique, except for the current record being updated.
- All required fields must be provided when creating a new profile.
- When updating, you can provide any combination of fields to update.

---

## Response Examples

### Success - User Profile Created (201)

```json
{
  "data": {
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
  "message": "User profile created successfully."
}
```

### Success - User Profile Updated (200)

```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe Updated",
    "bio": "Updated bio information",
    "email_address": "john.doe.updated@example.com",
    "address": "456 New Street, City, State 12345",
    "company": "New Tech Solutions Inc.",
    "phone": "+1 (555) 987-6543",
    "job_title": "Lead Developer",
    "department": "Engineering",
    "timezone": "America/Los_Angeles",
    "created_at": "2025-11-07T11:12:31.000000Z",
    "updated_at": "2025-11-07T12:30:45.000000Z"
  },
  "message": "User profile updated successfully."
}
```

### Error - Validation Failed (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "user_id": [
      "The user id field is required."
    ],
    "first_name": [
      "The first name field is required."
    ],
    "email_address": [
      "The email address has already been taken."
    ],
    "address": [
      "The address field is required."
    ]
  }
}
```

### Error - User Profile Not Found (404)

```json
{
  "message": "User profile not found or you do not have permission to update it."
}
```

### Error - No Organization Found (404)

```json
{
  "message": "No organization found. Please create an organization first."
}
```

### Error - Server Error (500)

```json
{
  "message": "An error occurred while saving the user profile.",
  "error": "SQLSTATE[23000]: Integrity constraint violation..."
}
```

---

## HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `201` | User profile created successfully |
| `200` | User profile updated successfully |
| `422` | Validation error - Invalid or missing required fields |
| `404` | User profile not found, or no organization found |
| `500` | Internal server error |

---

## Business Logic

1. **Organization Assignment**: The `organization_id` is automatically set from the authenticated user's first organization. If the user has no organization, the request will fail with a 404 error.

2. **Create vs Update**: 
   - If `id` is provided and exists in the database, the profile will be updated.
   - If `id` is not provided, a new profile will be created.
   - When updating, the profile must belong to the authenticated user's organization.

3. **Email Uniqueness**: The `email_address` must be unique across all user profiles. When updating, the current record's email is excluded from the uniqueness check.

4. **User Validation**: The `user_id` must reference an existing user in the `users` table.

5. **Transaction Safety**: All database operations are wrapped in a transaction. If any error occurs, all changes are rolled back.

---

## Example cURL Requests

### Create User Profile

```bash
curl -X POST "https://your-domain.com/api/user-profiles/save" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "bio": "Software developer",
    "email_address": "john.doe@example.com",
    "address": "123 Main Street",
    "company": "Tech Solutions Inc.",
    "phone": "+1 (555) 123-4567",
    "job_title": "Senior Developer",
    "department": "Engineering",
    "timezone": "America/New_York"
  }'
```

### Update User Profile

```bash
curl -X POST "https://your-domain.com/api/user-profiles/save" \
  -H "Authorization: Bearer your-auth-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1,
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe Updated",
    "email_address": "john.doe.updated@example.com",
    "address": "456 New Street",
    "company": "New Tech Solutions Inc.",
    "job_title": "Lead Developer"
  }'
```

---

## JavaScript/Fetch Example

### Create User Profile

```javascript
const createUserProfile = async (profileData) => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: 1,
        first_name: "John",
        last_name: "Doe",
        bio: "Software developer",
        email_address: "john.doe@example.com",
        address: "123 Main Street",
        company: "Tech Solutions Inc.",
        phone: "+1 (555) 123-4567",
        job_title: "Senior Developer",
        department: "Engineering",
        timezone: "America/New_York"
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('User profile created:', data.data);
      return data;
    } else {
      console.error('Error:', data.message, data.errors);
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Request failed:', error);
    throw error;
  }
};
```

### Update User Profile

```javascript
const updateUserProfile = async (profileId, updateData) => {
  try {
    const response = await fetch('https://your-domain.com/api/user-profiles/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: profileId,
        ...updateData
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      console.log('User profile updated:', data.data);
      return data;
    } else {
      console.error('Error:', data.message, data.errors);
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Request failed:', error);
    throw error;
  }
};
```

---

## PHP Example

### Create User Profile

```php
<?php

$url = 'https://your-domain.com/api/user-profiles/save';
$token = 'your-auth-token';

$data = [
    'user_id' => 1,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'bio' => 'Software developer',
    'email_address' => 'john.doe@example.com',
    'address' => '123 Main Street',
    'company' => 'Tech Solutions Inc.',
    'phone' => '+1 (555) 123-4567',
    'job_title' => 'Senior Developer',
    'department' => 'Engineering',
    'timezone' => 'America/New_York'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 201) {
    echo "User profile created successfully!\n";
    print_r($result['data']);
} else {
    echo "Error: " . $result['message'] . "\n";
    if (isset($result['errors'])) {
        print_r($result['errors']);
    }
}
```

---

## Notes

1. **Authentication**: You must be authenticated with a valid Bearer token to use this endpoint.

2. **Organization Context**: The user profile is automatically associated with the authenticated user's organization. You cannot create profiles for other organizations.

3. **User ID Validation**: The `user_id` must reference an existing user. Make sure the user exists before creating a profile.

4. **Email Uniqueness**: Each email address can only be used once across all user profiles. When updating, the current record's email is excluded from the uniqueness check.

5. **Partial Updates**: When updating, you only need to provide the fields you want to change. However, all required fields must still be present.

6. **Transaction Safety**: All operations are wrapped in database transactions, ensuring data consistency.

7. **Error Handling**: Always check the HTTP status code and handle errors appropriately. Validation errors will include detailed field-specific error messages.

---

## Related Endpoints

- `POST /api/user-profiles/index` - Get paginated list of user profiles
- `POST /api/user-profiles/show` - Get single user profile by ID
- `POST /api/user-profiles/delete` - Delete a user profile

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main API documentation.

