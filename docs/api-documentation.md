# API Documentation

## Base URL
```
http://your-domain.com/api
```

---

## 1. User Registration

Register a new user. The organization will be created automatically if it doesn't exist.

### Endpoint
```
POST /api/register
```

### Request Body
```json
{
    "name": "Dhavalkumar Mesavaniya",
    "email": "dhaval48@gmail.com",
    "password": "Tunafishm@48",
    "password_confirmation": "Tunafishm@48",
    "organization_name": "My Company"
}
```

### Required Fields
- `name` (string, required): User's full name
- `email` (string, required, unique): User's email address
- `password` (string, required, min:8): User's password
- `password_confirmation` (string, required): Confirm password
- `organization_name` (string, required): Organization name

### Response (Success - 201)
```json
{
    "data": {
        "id": 1,
        "first_name": null,
        "last_name": null,
        "organization_id": 1,
        "financial_year_id": null,
        "name": "Dhavalkumar Mesavaniya",
        "email": "dhaval48@gmail.com",
        "email_verified_at": null,
        "email_verified_code": null,
        "2fa_code": null,
        "is_platform_admin": 0,
        "created_at": "2025-10-28T00:00:00.000000Z",
        "updated_at": "2025-10-28T00:00:00.000000Z",
        "mobile": null
    },
    "meta": {
        "token": "1|xxxxxxxxxxxxxxxxxx"
    },
    "message": "Registration successful!"
}
```

### Notes
- If organization doesn't exist, it will be created automatically
- User will be assigned as admin of the organization
- Password must be at least 8 characters

---

## 2. User Login

Authenticate a user and receive an access token.

### Endpoint
```
POST /api/login
```

### Request Body
```json
{
    "email": "dhaval48@gmail.com",
    "password": "Tunafishm@48"
}
```

### Required Fields
- `email` (string, required): User's email address
- `password` (string, required): User's password

### Response (Success - 200)
```json
{
    "data": {
        "id": 1,
        "first_name": null,
        "last_name": null,
        "organization_id": null,
        "financial_year_id": null,
        "name": "Dhavalkumar Mesavaniya",
        "email": "dhaval48@gmail.com",
        "email_verified_at": null,
        "email_verified_code": null,
        "2fa_code": null,
        "is_platform_admin": 0,
        "created_at": "2025-10-28T00:00:00.000000Z",
        "updated_at": "2025-10-28T00:00:00.000000Z",
        "mobile": null
    },
    "meta": {
        "token": "2|xxxxxxxxxxxxxxxxxx"
    },
    "message": "Login successfully!"
}
```

### Response (Error - 401)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

## 3. Get All Organizations

Retrieve a list of all active organizations.

### Endpoint
```
GET /api/organizations
```

### Headers
None required (public endpoint)

### Response (Success - 200)
```json
{
    "data": [
        {
            "id": 1,
            "name": "My Organization",
            "slug": "my-organization",
            "description": "Default organization",
            "email": "org@example.com",
            "phone": "+1234567890",
            "address": "123 Main Street, City, Country",
            "status": "active",
            "created_at": "2025-10-28T00:00:00.000000Z",
            "updated_at": "2025-10-28T00:00:00.000000Z"
        }
    ],
    "message": "Organizations retrieved successfully"
}
```

---

## 4. Search Organization by Name

Search for an organization by name or slug.

### Endpoint
```
POST /api/organizations/search
```

### Request Body
```json
{
    "name": "My Organization"
}
```

### Required Fields
- `name` (string, required): Organization name or slug

### Response (Success - 200)
```json
{
    "data": {
        "id": 1,
        "name": "My Organization",
        "slug": "my-organization",
        "description": "Default organization",
        "email": "org@example.com",
        "phone": "+1234567890",
        "address": "123 Main Street, City, Country",
        "status": "active",
        "created_at": "2025-10-28T00:00:00.000000Z",
        "updated_at": "2025-10-28T00:00:00.000000Z"
    },
    "message": "Organization found"
}
```

### Response (Not Found - 404)
```json
{
    "message": "Organization not found"
}
```

---

## 5. Get Current User

Retrieve the authenticated user's information.

### Endpoint
```
GET /api/user
```

### Headers
```
Authorization: Bearer {token}
```

### Response (Success - 200)
```json
{
    "data": {
        "id": 1,
        "name": "Dhavalkumar Mesavaniya",
        "email": "dhaval48@gmail.com"
    }
}
```

### Response (Unauthorized - 401)
```json
{
    "message": "Unauthenticated."
}
```

---

## Authentication

Protected routes require a Bearer token for authentication.

### How to Use Bearer Token

Include the token in the request headers:

```
Authorization: Bearer {your_token_here}
```

### Example Request
```bash
curl -X GET http://your-domain.com/api/user \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json"
```

---

## Error Responses

### Validation Error (400)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
    "message": "Organization not found"
}
```

---

## Important Notes

1. **Organization Auto-Creation**: During registration, if the organization name provided doesn't exist, it will be created automatically
2. **User Role**: The registered user will be assigned as the admin of the organization
3. **Password Requirements**: Minimum 8 characters required
4. **Email Uniqueness**: Email addresses must be unique across all users
5. **Token Life**: Tokens don't expire by default
6. **Organization Slug**: Organizations are identified by their slug, which is generated from the name

---

## API Request Examples

### Registration
```bash
curl -X POST http://your-domain.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "organization_name": "John's Company"
  }'
```

### Login
```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Organizations
```bash
curl -X GET http://your-domain.com/api/organizations
```

### Search Organization
```bash
curl -X POST http://your-domain.com/api/organizations/search \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John'\''s Company"
  }'
```

### Get User (Protected)
```bash
curl -X GET http://your-domain.com/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Support

For any issues or questions, please contact the development team.
