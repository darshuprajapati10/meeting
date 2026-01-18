# Forgot Password API Documentation

## Overview
This API endpoint allows users to request a password reset email. When a user forgets their password, they can submit their email address, and the system will send them a password reset link via email. This is a public endpoint that does not require authentication.

**Security Note:** For security reasons, the API always returns the same success message regardless of whether the email exists in the system. This prevents email enumeration attacks.

---

## Endpoint

**URL:** `POST /api/auth/forgot-password`

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

| Parameter | Type   | Required | Description                    | Constraints                    |
|-----------|--------|----------|--------------------------------|--------------------------------|
| email     | string | Yes      | User's email address           | Valid email format, must exist in users table |

### Request Example
```json
{
  "email": "user@example.com"
}
```

---

## Response

### Success Response (200 OK)

When the email exists in the system, the endpoint returns a success response. **Note:** For security reasons, the response is the same whether the email exists or not (to prevent email enumeration attacks).

#### Response Structure
```json
{
  "success": true,
  "message": "If that email address exists in our system, we have sent a password reset link to it."
}
```

#### Response Fields

- `success` (boolean): Indicates the request was processed successfully
- `message` (string): Success message (same message regardless of email existence for security)

**Important:** The API always returns this success message, even if the email doesn't exist in the system. This prevents attackers from discovering which email addresses are registered.

---

### Validation Error Response (422 Unprocessable Entity)

When the request validation fails:

#### Missing Email Field
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

#### Invalid Email Format
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "email": [
      "The email must be a valid email address."
    ]
  }
}
```

---

### Rate Limiting Response (429 Too Many Requests)

When the user has exceeded the rate limit for password reset requests:

```json
{
  "success": false,
  "message": "Too many password reset attempts. Please try again later.",
  "retry_after": 3600
}
```

**Rate Limits:**
- Maximum **3 requests per email** per hour
- Maximum **10 requests per IP address** per hour

The `retry_after` field indicates the number of seconds until the user can make another request.

---

## Implementation Examples

### JavaScript (Fetch API)
```javascript
async function forgotPassword(email) {
  try {
    const response = await fetch('http://localhost:8000/api/auth/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email
      })
    });

    const data = await response.json();

    if (response.ok) {
      console.log('Success:', data.message);
      // Show success message to user
      alert(data.message);
      return data;
    } else if (response.status === 429) {
      // Handle rate limiting
      const retryAfter = data.retry_after;
      const minutes = Math.ceil(retryAfter / 60);
      alert(`Too many attempts. Please try again in ${minutes} minutes.`);
      throw new Error(data.message);
    } else {
      // Handle validation errors
      const errorMessages = Object.values(data.errors).flat().join(', ');
      alert(errorMessages);
      throw new Error(data.message || 'Request failed');
    }
  } catch (error) {
    console.error('Network error:', error);
    throw error;
  }
}

// Usage
forgotPassword('user@example.com')
  .then(data => {
    console.log('Password reset email sent');
  })
  .catch(error => {
    console.error('Error:', error.message);
  });
```

### React Example
```javascript
import { useState } from 'react';

function ForgotPassword() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess(false);

    try {
      const response = await fetch('http://localhost:8000/api/auth/forgot-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email })
      });

      const data = await response.json();

      if (response.ok) {
        setSuccess(true);
        setEmail(''); // Clear email field
      } else if (response.status === 429) {
        const minutes = Math.ceil(data.retry_after / 60);
        setError(`Too many attempts. Please try again in ${minutes} minutes.`);
      } else {
        const errorMessages = Object.values(data.errors || {}).flat().join(', ');
        setError(errorMessages || data.message);
      }
    } catch (err) {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Forgot Password</h2>
      
      {success && (
        <div className="success-message">
          If that email address exists in our system, we have sent a password reset link to it.
        </div>
      )}

      {error && <div className="error-message">{error}</div>}

      <input
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Enter your email address"
        required
        disabled={loading}
      />

      <button type="submit" disabled={loading || success}>
        {loading ? 'Sending...' : 'Send Reset Link'}
      </button>

      <p className="help-text">
        Enter your email address and we'll send you a link to reset your password.
      </p>
    </form>
  );
}

export default ForgotPassword;
```

### Vue.js Example
```vue
<template>
  <form @submit.prevent="handleSubmit">
    <h2>Forgot Password</h2>
    
    <div v-if="success" class="success-message">
      If that email address exists in our system, we have sent a password reset link to it.
    </div>

    <div v-if="error" class="error-message">{{ error }}</div>

    <input
      v-model="email"
      type="email"
      placeholder="Enter your email address"
      required
      :disabled="loading || success"
    />

    <button type="submit" :disabled="loading || success">
      {{ loading ? 'Sending...' : 'Send Reset Link' }}
    </button>

    <p class="help-text">
      Enter your email address and we'll send you a link to reset your password.
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue';

const email = ref('');
const loading = ref(false);
const error = ref('');
const success = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  success.value = false;

  try {
    const response = await fetch('http://localhost:8000/api/auth/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email: email.value })
    });

    const data = await response.json();

    if (response.ok) {
      success.value = true;
      email.value = '';
    } else if (response.status === 429) {
      const minutes = Math.ceil(data.retry_after / 60);
      error.value = `Too many attempts. Please try again in ${minutes} minutes.`;
    } else {
      const errorMessages = Object.values(data.errors || {}).flat().join(', ');
      error.value = errorMessages || data.message;
    }
  } catch (err) {
    error.value = 'Network error. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
```

### Flutter/Dart Example
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ForgotPasswordService {
  final String baseUrl = 'http://localhost:8000/api';

  Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/forgot-password'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'],
        };
      } else if (response.statusCode == 429) {
        final retryAfter = data['retry_after'] ?? 3600;
        final minutes = (retryAfter / 60).ceil();
        throw Exception('Too many attempts. Please try again in $minutes minutes.');
      } else {
        final errors = data['errors'] ?? {};
        final errorMessages = errors.values
            .expand((e) => e as List)
            .join(', ');
        throw Exception(errorMessages.isNotEmpty 
            ? errorMessages 
            : data['message'] ?? 'Request failed');
      }
    } catch (e) {
      if (e is Exception) {
        rethrow;
      }
      throw Exception('Network error. Please try again.');
    }
  }
}

// Usage in Flutter widget
class ForgotPasswordScreen extends StatefulWidget {
  @override
  _ForgotPasswordScreenState createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _emailController = TextEditingController();
  final _service = ForgotPasswordService();
  bool _loading = false;
  String? _error;
  bool _success = false;

  Future<void> _handleSubmit() async {
    setState(() {
      _loading = true;
      _error = null;
      _success = false;
    });

    try {
      await _service.forgotPassword(_emailController.text);
      setState(() {
        _success = true;
        _emailController.clear();
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
    } finally {
      setState(() {
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Forgot Password')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Column(
          children: [
            if (_success)
              Container(
                padding: EdgeInsets.all(12),
                color: Colors.green[100],
                child: Text(
                  'If that email address exists in our system, we have sent a password reset link to it.',
                  style: TextStyle(color: Colors.green[800]),
                ),
              ),
            if (_error != null)
              Container(
                padding: EdgeInsets.all(12),
                color: Colors.red[100],
                child: Text(
                  _error!,
                  style: TextStyle(color: Colors.red[800]),
                ),
              ),
            TextField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: InputDecoration(
                labelText: 'Email',
                hintText: 'Enter your email address',
              ),
            ),
            SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loading || _success ? null : _handleSubmit,
              child: _loading 
                  ? CircularProgressIndicator() 
                  : Text('Send Reset Link'),
            ),
          ],
        ),
      ),
    );
  }
}
```

### Axios Example
```javascript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Forgot password function
async function forgotPassword(email) {
  try {
    const response = await apiClient.post('/auth/forgot-password', {
      email
    });

    return {
      success: true,
      message: response.data.message
    };
  } catch (error) {
    if (error.response) {
      // Server responded with error
      if (error.response.status === 429) {
        const retryAfter = error.response.data.retry_after;
        const minutes = Math.ceil(retryAfter / 60);
        throw new Error(`Too many attempts. Please try again in ${minutes} minutes.`);
      } else {
        const errors = error.response.data.errors || {};
        const errorMessages = Object.values(errors).flat().join(', ');
        throw new Error(errorMessages || error.response.data.message);
      }
    } else {
      // Network error
      throw new Error('Network error. Please try again.');
    }
  }
}

// Usage
forgotPassword('user@example.com')
  .then(data => {
    console.log('Success:', data.message);
    alert(data.message);
  })
  .catch(error => {
    console.error('Error:', error.message);
    alert(error.message);
  });
```

### cURL Example
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

### Postman Example

1. **Method:** POST
2. **URL:** `http://localhost:8000/api/auth/forgot-password`
3. **Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`
4. **Body (raw JSON):**
```json
{
  "email": "user@example.com"
}
```

---

## Security Considerations

### 1. Email Enumeration Prevention
The API always returns the same success message regardless of whether the email exists in the system. This prevents attackers from discovering which email addresses are registered.

### 2. Rate Limiting
- **Per Email:** Maximum 3 requests per email address per hour
- **Per IP:** Maximum 10 requests per IP address per hour
- When rate limit is exceeded, the API returns a 429 status with `retry_after` seconds

### 3. Token Security
- Password reset tokens are cryptographically secure
- Tokens expire after 60 minutes (configurable in `config/auth.php`)
- Tokens are stored securely in the `password_reset_tokens` table

### 4. Logging
Password reset requests are logged (without sensitive data) for security monitoring:
- Email address (for legitimate users)
- IP address
- Request status

---

## Database Requirements

The API uses Laravel's built-in password reset functionality, which requires the `password_reset_tokens` table:

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

This table is automatically created by Laravel's default migration.

---

## Email Configuration

To send password reset emails, configure your email settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Email Service Providers
- **Mailtrap** (Development/Testing)
- **SendGrid** (Production)
- **Mailgun** (Production)
- **Amazon SES** (Production)
- **SMTP** (Any SMTP server)

---

## Password Reset Email

When a user requests a password reset, they will receive an email containing:
- A secure password reset link with token
- Clear instructions for resetting the password
- Token expiration time (60 minutes)
- Security warning if the user didn't request the reset

The reset link format:
```
https://yourapp.com/reset-password?token=xxx&email=user@example.com
```

**Note:** The actual password reset (using the token) is handled by a separate endpoint (`/api/auth/reset-password` - to be implemented).

---

## Testing Checklist

- [ ] Valid email address returns success (200)
- [ ] Invalid email format returns validation error (422)
- [ ] Missing email field returns validation error (422)
- [ ] Non-existent email returns success (200) - for security
- [ ] Rate limiting works correctly (429 after limit exceeded)
- [ ] Password reset email is sent when email exists
- [ ] Password reset token is stored in database
- [ ] Token expires after 60 minutes
- [ ] Response format matches specification
- [ ] Email enumeration prevention works (same message for all emails)

---

## Error Handling Best Practices

### Frontend Implementation
1. **Always show the success message** - Even if email doesn't exist, show success to prevent information leakage
2. **Handle rate limiting** - Show user-friendly message with retry time
3. **Display validation errors** - Show specific field errors for invalid input
4. **Loading states** - Show loading indicator during request
5. **Network errors** - Handle connection issues gracefully

### Example Error Handling
```javascript
try {
  const response = await forgotPassword(email);
  // Always show success message
  showSuccessMessage(response.message);
} catch (error) {
  if (error.status === 429) {
    // Rate limit exceeded
    showRateLimitError(error.retry_after);
  } else if (error.status === 422) {
    // Validation errors
    showValidationErrors(error.errors);
  } else {
    // Network or other errors
    showGenericError('Something went wrong. Please try again.');
  }
}
```

---

## Related Endpoints

- **POST** `/api/auth/reset-password` - Reset password using token (to be implemented)
- **POST** `/api/login` - User login
- **POST** `/api/register` - User registration
- **POST** `/api/signup` - User signup with organization

---

## Important Notes

1. **Password Reset Link:** The actual password reset (using the token) is a separate endpoint that will be implemented later. This endpoint only handles the initial request for a password reset email.

2. **Email Service:** Ensure your email service (SMTP, Mailgun, SendGrid, etc.) is properly configured to send password reset emails.

3. **Token Format:** Uses Laravel's built-in `Password::sendResetLink()` which generates secure tokens automatically.

4. **Frontend URL:** Configure the frontend URL in your Laravel configuration or environment variables for the reset link to point to your frontend application.

5. **HTTPS:** Always use HTTPS in production for security.

6. **CORS:** Ensure proper CORS configuration for your frontend domain.

---

## Support

For questions or issues regarding this API endpoint, please contact the development team.

**Last Updated:** 2024-12-19

