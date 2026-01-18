# Login API Documentation

## Overview
This API endpoint authenticates users and returns an access token for subsequent authenticated requests.

---

## Endpoint

**URL:** `POST /api/login`

**Base URL:** `http://localhost:8000` (Development)  
**Production URL:** `https://your-domain.com`

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

| Parameter | Type   | Required | Description           |
|-----------|--------|----------|-----------------------|
| email     | string | Yes      | User's email address  |
| password  | string | Yes      | User's password       |

### Request Example
```json
{
  "email": "john.doe@example.com",
  "password": "your_password"
}
```

---

## Response

### Success Response (200 OK)

#### Response Structure
```json
{
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "organization_id": 5,
    "financial_year_id": null,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": "2024-01-15T10:30:00.000000Z",
    "email_verified_code": null,
    "2fa_code": null,
    "is_platform_admin": 0,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "mobile": "+1234567890"
  },
  "meta": {
    "token": "1|random_token_string_here_xxxxxxxxxxxx"
  },
  "message": "Login successfully!"
}
```

#### Response Fields

**data** - User information object
- `id` (number): User's unique identifier
- `name` (string): User's full name
- `email` (string): User's email address
- `first_name` (string|null): User's first name
- `last_name` (string|null): User's last name
- `organization_id` (number|null): Associated organization ID
- `financial_year_id` (number|null): Financial year ID
- `email_verified_at` (datetime|null): Email verification timestamp
- `mobile` (string|null): User's mobile number
- `is_platform_admin` (number): Admin status (0 or 1)
- `created_at` (datetime): Account creation time
- `updated_at` (datetime): Last update time

**meta** - Metadata object
- `token` (string): **Bearer token for authentication** ⚠️ **Save this token!**

**message** (string): Success message

---

### Error Response (422 Unprocessable Entity)

#### Missing Fields
```json
{
  "message": "The email field is required. (and 1 more error)",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

#### Invalid Credentials
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

## Implementation Examples

### JavaScript (Fetch API)
```javascript
async function login(email, password) {
  try {
    const response = await fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email,
        password: password
      })
    });

    const data = await response.json();

    if (response.ok) {
      // Save token for future requests
      localStorage.setItem('auth_token', data.meta.token);
      localStorage.setItem('user', JSON.stringify(data.data));
      
      console.log('Login successful:', data.message);
      return data;
    } else {
      // Handle errors
      console.error('Login failed:', data.message);
      throw new Error(data.message || 'Login failed');
    }
  } catch (error) {
    console.error('Network error:', error);
    throw error;
  }
}

// Usage
login('john.doe@example.com', 'password123')
  .then(data => {
    console.log('User:', data.data.name);
    console.log('Token:', data.meta.token);
  })
  .catch(error => {
    console.error('Error:', error.message);
  });
```

### React Example
```javascript
import { useState } from 'react';

function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await fetch('http://localhost:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
      });

      const data = await response.json();

      if (response.ok) {
        // Save token and user data
        localStorage.setItem('auth_token', data.meta.token);
        localStorage.setItem('user', JSON.stringify(data.data));
        
        // Redirect to dashboard
        window.location.href = '/dashboard';
      } else {
        setError(data.message || 'Invalid credentials');
      }
    } catch (err) {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <input
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Email"
        required
      />
      <input
        type="password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        placeholder="Password"
        required
      />
      {error && <div className="error">{error}</div>}
      <button type="submit" disabled={loading}>
        {loading ? 'Logging in...' : 'Login'}
      </button>
    </form>
  );
}
```

### Vue.js Example
```javascript
<template>
  <form @submit.prevent="handleLogin">
    <input v-model="form.email" type="email" placeholder="Email" required />
    <input v-model="form.password" type="password" placeholder="Password" required />
    <div v-if="error" class="error">{{ error }}</div>
    <button type="submit" :disabled="loading">
      {{ loading ? 'Logging in...' : 'Login' }}
    </button>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';

const loading = ref(false);
const error = ref('');
const form = reactive({
  email: '',
  password: ''
});

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    });

    const data = await response.json();

    if (response.ok) {
      localStorage.setItem('auth_token', data.meta.token);
      localStorage.setItem('user', JSON.stringify(data.data));
      // Redirect to dashboard
      window.location.href = '/dashboard';
    } else {
      error.value = data.message || 'Invalid credentials';
    }
  } catch (err) {
    error.value = 'Network error. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
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

// Login function
async function login(email, password) {
  try {
    const response = await apiClient.post('/login', {
      email,
      password
    });

    // Save token
    const token = response.data.meta.token;
    localStorage.setItem('auth_token', token);
    
    // Set default authorization header for future requests
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    return response.data;
  } catch (error) {
    if (error.response) {
      // Server responded with error
      console.error('Error:', error.response.data.message);
      throw new Error(error.response.data.message);
    } else {
      // Network error
      console.error('Network error:', error.message);
      throw error;
    }
  }
}

// Usage
login('john.doe@example.com', 'password123')
  .then(data => console.log('Success:', data))
  .catch(error => console.error('Failed:', error.message));
```

### cURL Example
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "password123"
  }'
```

---

## Using the Token in Subsequent Requests

After successful login, you must include the token in the `Authorization` header for protected routes:

### Fetch API
```javascript
const token = localStorage.getItem('auth_token');

fetch('http://localhost:8000/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

### Axios
```javascript
axios.get('http://localhost:8000/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

### Axios Interceptor (Recommended)
```javascript
// Add token to all requests automatically
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

---

## Important Notes

1. **Token Storage**: Store the token securely (localStorage, sessionStorage, or secure cookie)
2. **Token Expiration**: This token persists until manually deleted. Implement logout functionality.
3. **HTTPS**: Always use HTTPS in production for security
4. **Error Handling**: Always handle both network errors and API errors
5. **Loading States**: Show loading indicators during API calls
6. **CORS**: Ensure proper CORS configuration for your frontend domain

---

## Logout Implementation

To logout, simply remove the token:

```javascript
function logout() {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  // Optional: Call API to revoke token on server
  window.location.href = '/login';
}
```

---

## Support

For issues or questions, contact: [your-email@example.com]

**Last Updated:** 2024-10-28

