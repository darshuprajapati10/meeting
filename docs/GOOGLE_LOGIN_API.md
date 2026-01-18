# Google Login API Documentation

## Overview
This API endpoint allows users to authenticate using their Google account. The application handles the Google OAuth flow on the client side and sends the Google authentication token to this endpoint. The backend verifies the token with Google, checks if the user exists, and either logs them in or creates a new account automatically.

---

## Endpoint

**URL:** `POST /api/auth/google`

**Base URL:** `http://localhost:8000` (Development)  
**Production URL:** `https://your-domain.com`

**Authentication:** Not Required (Public Endpoint)

---

## Request

### Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

### Body Parameters

| Parameter | Type   | Required | Description                          |
|-----------|--------|----------|--------------------------------------|
| id_token | string | Yes      | Google ID token (JWT) from OAuth     |
| email    | string | Yes      | User's Google email address          |
| name     | string | Yes      | User's full name from Google         |
| access_token | string | No    | Google OAuth access token (optional) |
| google_id    | string | No    | Google user ID (optional)            |

### Request Example
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjEyMzQ1NiIsInR5cCI6IkpXVCJ9...",
  "access_token": "ya29.a0AfH6SMBx...",
  "email": "john.doe@gmail.com",
  "name": "John Doe",
  "google_id": "123456789012345678901"
}
```

### Minimum Required Request
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIs...",
  "email": "john.doe@gmail.com",
  "name": "John Doe"
}
```

---

## Response

### Success Response - Existing User (200 OK)

When the user already exists in the system:

**Status Code:** `200 OK`

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@gmail.com",
      "email_verified_at": "2024-12-19T10:30:00.000000Z",
      "created_at": "2024-12-01T10:30:00.000000Z",
      "updated_at": "2024-12-19T10:30:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer"
  }
}
```

### Success Response - New User Created (201 Created)

When a new user account is created:

**Status Code:** `201 Created`

```json
{
  "success": true,
  "message": "Account created and logged in successfully",
  "data": {
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane.smith@gmail.com",
      "email_verified_at": "2024-12-19T10:30:00.000000Z",
      "created_at": "2024-12-19T10:30:00.000000Z",
      "updated_at": "2024-12-19T10:30:00.000000Z"
    },
    "token": "2|xyzabcdefghijklmnopqrstuvwxyz123456",
    "token_type": "Bearer"
  }
}
```

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

When required fields are missing or invalid:

```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "id_token": [
      "The id token field is required."
    ],
    "email": [
      "The email field is required."
    ],
    "name": [
      "The name field is required."
    ]
  }
}
```

### Google Client ID Not Configured (500 Internal Server Error)

When `GOOGLE_CLIENT_ID` is not set in `.env` file:

```json
{
  "success": false,
  "message": "Google Client ID is not configured. Please set GOOGLE_CLIENT_ID in your .env file."
}
```

### Invalid Google Token (401 Unauthorized)

When the Google token cannot be verified:

```json
{
  "success": false,
  "message": "Invalid Google token. Please try again."
}
```

### Client ID Mismatch (401 Unauthorized)

When the token's client ID doesn't match the configured client ID:

```json
{
  "success": false,
  "message": "Invalid Google token. Client ID mismatch."
}
```

### Email Mismatch (422 Unprocessable Entity)

When the email in the request doesn't match the email in the Google token:

```json
{
  "success": false,
  "message": "Email mismatch. The provided email does not match the Google account."
}
```

### Email Not Authorized (403 Forbidden)

When `GOOGLE_LOGIN_ALLOWED_EMAIL` is configured and the email doesn't match:

```json
{
  "success": false,
  "message": "This email is not authorized to login via Google."
}
```

### Server Error (500 Internal Server Error)

When an unexpected error occurs:

```json
{
  "success": false,
  "message": "An error occurred while processing your request. Please try again later."
}
```

---

## Implementation Details

### Token Verification

The API verifies the Google ID token by:
1. Calling Google's `tokeninfo` endpoint: `https://oauth2.googleapis.com/tokeninfo`
2. Verifying the token's audience (client ID) matches the configured `GOOGLE_CLIENT_ID`
3. Verifying the email in the token matches the email in the request

### User Account Handling

- **Existing User**: If user exists by email or `google_id`, they are logged in and a new token is generated
- **New User**: If user doesn't exist, a new account is created with:
  - Email marked as verified (`email_verified_at` set to current timestamp)
  - Random secure password generated (user won't need it for Google login)
  - `google_id` stored if provided

### Email Restriction

If `GOOGLE_LOGIN_ALLOWED_EMAIL` is set in `.env`, only that specific email can login via Google. This is useful for testing or restricting access.

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_LOGIN_ALLOWED_EMAIL=allowed-email@gmail.com  # Optional - for single email restriction
```

### How to Get Google Client ID

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create or select a project
3. Navigate to "APIs & Services" > "Credentials"
4. Click "Create Credentials" > "OAuth client ID"
5. Configure OAuth consent screen if not done
6. Select application type (Web application, iOS, Android, etc.)
7. Copy the Client ID and add to `.env` file

---

## Usage Examples

### cURL Example

```bash
curl -X POST "http://localhost:8000/api/auth/google" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id_token": "eyJhbGciOiJSUzI1NiIs...",
    "email": "john.doe@gmail.com",
    "name": "John Doe",
    "google_id": "123456789012345678901"
  }'
```

### JavaScript/Fetch Example

```javascript
async function googleLogin(idToken, email, name, googleId) {
  try {
    const response = await fetch('http://localhost:8000/api/auth/google', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id_token: idToken,
        email: email,
        name: name,
        google_id: googleId
      })
    });

    const data = await response.json();

    if (response.ok) {
      // Save token and user data
      localStorage.setItem('auth_token', data.data.token);
      localStorage.setItem('user_data', JSON.stringify(data.data.user));
      
      return {
        success: true,
        user: data.data.user,
        token: data.data.token
      };
    } else {
      throw new Error(data.message || 'Google login failed');
    }
  } catch (error) {
    console.error('Google login error:', error);
    throw error;
  }
}
```

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class GoogleLoginService {
  final String baseUrl = 'http://localhost:8000/api';

  Future<Map<String, dynamic>> googleLogin({
    required String idToken,
    required String email,
    required String name,
    String? googleId,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/google'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'id_token': idToken,
          'email': email,
          'name': name,
          if (googleId != null) 'google_id': googleId,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 || response.statusCode == 201) {
        return {
          'success': true,
          'user': data['data']['user'],
          'token': data['data']['token'],
        };
      } else {
        throw Exception(data['message'] ?? 'Google login failed');
      }
    } catch (e) {
      throw Exception('Google login error: ${e.toString()}');
    }
  }
}
```

---

## Postman Setup

1. **Method:** POST
2. **URL:** `http://localhost:8000/api/auth/google`
3. **Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`
4. **Body (raw JSON):**
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIs...",
  "email": "user@gmail.com",
  "name": "John Doe",
  "google_id": "123456789012345678901"
}
```

---

## Security Considerations

1. **Token Verification**: The API always verifies the Google token before processing
2. **Email Verification**: Ensures the email in the request matches the email in the verified token
3. **Client ID Verification**: Verifies the token was issued for the correct application
4. **Email Restriction**: Optional single email restriction for testing/security

---

## Status Codes

| Code | Description |
|------|-------------|
| 200  | Existing user logged in successfully |
| 201  | New user created and logged in successfully |
| 401  | Invalid Google token or client ID mismatch |
| 403  | Email not authorized (if restriction is enabled) |
| 422  | Validation error or email mismatch |
| 500  | Server error or configuration issue |

---

## Related Endpoints

- `POST /api/login` - Regular email/password login
- `POST /api/register` - Regular user registration
- `POST /api/signup` - Signup with organization
- `POST /api/auth/forgot-password` - Password reset request

---

## Notes

- The `id_token` must be obtained from Google OAuth flow (cannot be manually created)
- Tokens expire after a certain period (usually 1 hour)
- The token's client ID must match the `GOOGLE_CLIENT_ID` in `.env`
- New users are automatically created with verified email
- Users can login with either email or `google_id` if both exist

---

**Last Updated:** 2024-12-19

