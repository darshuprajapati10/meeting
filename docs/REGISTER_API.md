# Register/Signup API Documentation

## Overview
This API endpoint allows new users to create an account, automatically creates or links to an organization, and returns an access token for immediate authenticated access.

---

## Endpoint

**URL:** `POST /api/register`

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

| Parameter            | Type   | Required | Description                           |
|----------------------|--------|----------|---------------------------------------|
| name                 | string | Yes      | User's full name                      |
| email                | string | Yes      | User's email address (must be unique) |
| password             | string | Yes      | Password (minimum 8 characters)       |
| password_confirmation| string | Yes      | Password confirmation (must match)    |
| organization_name    | string | Yes      | Organization/company name             |

### Request Example
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "organization_name": "Acme Corporation"
}
```

---

## Response

### Success Response (201 Created)

#### Response Structure
```json
{
  "data": {
    "id": 1,
    "first_name": null,
    "last_name": null,
    "organization_id": null,
    "financial_year_id": null,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": null,
    "email_verified_code": null,
    "2fa_code": null,
    "is_platform_admin": 0,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "mobile": null
  },
  "meta": {
    "token": "1|random_token_string_here_xxxxxxxxxxxx",
    "organization": {
      "id": 1,
      "name": "Acme Corporation",
      "slug": "acme-corporation",
      "description": "Organization created during registration",
      "email": null,
      "phone": null,
      "address": null,
      "status": "active",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z",
      "deleted_at": null
    }
  },
  "message": "Registration successful!"
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
- `email_verified_at` (datetime|null): Email verification timestamp (null for new users)
- `mobile` (string|null): User's mobile number
- `is_platform_admin` (number): Admin status (0 = regular user)
- `created_at` (datetime): Account creation time
- `updated_at` (datetime): Last update time

**meta** - Metadata object
- `token` (string): **Bearer token for authentication** ⚠️ **Save this token!**
- `organization` (object): Created or linked organization details

**message** (string): Success message

---

### Error Response (422 Unprocessable Entity)

#### Missing Fields
```json
{
  "message": "The name field is required. (and 4 more errors)",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email field is required."],
    "password": ["The password field is required."],
    "password_confirmation": ["The password confirmation field is required."],
    "organization_name": ["The organization name field is required."]
  }
}
```

#### Email Already Exists
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

#### Password Mismatch
```json
{
  "message": "The password field confirmation does not match.",
  "errors": {
    "password": ["The password field confirmation does not match."]
  }
}
```

#### Password Too Short
```json
{
  "message": "The password field must be at least 8 characters.",
  "errors": {
    "password": ["The password field must be at least 8 characters."]
  }
}
```

#### Invalid Email Format
```json
{
  "message": "The email field must be a valid email address.",
  "errors": {
    "email": ["The email field must be a valid email address."]
  }
}
```

---

## Implementation Examples

### JavaScript (Fetch API)
```javascript
async function register(userData) {
  try {
    const response = await fetch('http://localhost:8000/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: userData.name,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.passwordConfirmation,
        organization_name: userData.organizationName
      })
    });

    const data = await response.json();

    if (response.ok) {
      // Save token and user data
      localStorage.setItem('auth_token', data.meta.token);
      localStorage.setItem('user', JSON.stringify(data.data));
      localStorage.setItem('organization', JSON.stringify(data.meta.organization));
      
      console.log('Registration successful:', data.message);
      return data;
    } else {
      // Handle errors
      console.error('Registration failed:', data.message);
      if (data.errors) {
        console.error('Validation errors:', data.errors);
      }
      throw new Error(data.message || 'Registration failed');
    }
  } catch (error) {
    console.error('Network error:', error);
    throw error;
  }
}

// Usage
register({
  name: 'John Doe',
  email: 'john.doe@example.com',
  password: 'SecurePass123!',
  passwordConfirmation: 'SecurePass123!',
  organizationName: 'Acme Corporation'
})
  .then(data => {
    console.log('User:', data.data.name);
    console.log('Organization:', data.meta.organization.name);
    console.log('Token:', data.meta.token);
  })
  .catch(error => {
    console.error('Error:', error.message);
  });
```

### React Example
```javascript
import { useState } from 'react';

function Register() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    organization_name: ''
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [success, setSuccess] = useState('');

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setSuccess('');

    try {
      const response = await fetch('http://localhost:8000/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok) {
        // Save credentials
        localStorage.setItem('auth_token', data.meta.token);
        localStorage.setItem('user', JSON.stringify(data.data));
        localStorage.setItem('organization', JSON.stringify(data.meta.organization));
        
        setSuccess('Registration successful! Redirecting...');
        
        // Redirect to dashboard after 2 seconds
        setTimeout(() => {
          window.location.href = '/dashboard';
        }, 2000);
      } else {
        // Display validation errors
        setErrors(data.errors || {});
      }
    } catch (err) {
      setErrors({ general: 'Network error. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        name="name"
        value={formData.name}
        onChange={handleChange}
        placeholder="Full Name"
        required
      />
      {errors.name && <span className="error">{errors.name[0]}</span>}

      <input
        type="email"
        name="email"
        value={formData.email}
        onChange={handleChange}
        placeholder="Email"
        required
      />
      {errors.email && <span className="error">{errors.email[0]}</span>}

      <input
        type="password"
        name="password"
        value={formData.password}
        onChange={handleChange}
        placeholder="Password"
        required
      />
      {errors.password && <span className="error">{errors.password[0]}</span>}

      <input
        type="password"
        name="password_confirmation"
        value={formData.password_confirmation}
        onChange={handleChange}
        placeholder="Confirm Password"
        required
      />
      
      <input
        type="text"
        name="organization_name"
        value={formData.organization_name}
        onChange={handleChange}
        placeholder="Organization Name"
        required
      />
      {errors.organization_name && <span className="error">{errors.organization_name[0]}</span>}

      {success && <div className="success">{success}</div>}
      {errors.general && <div className="error">{errors.general}</div>}

      <button type="submit" disabled={loading}>
        {loading ? 'Registering...' : 'Register'}
      </button>
    </form>
  );
}
```

### Vue.js Example
```vue
<template>
  <form @submit.prevent="handleRegister">
    <input v-model="form.name" type="text" placeholder="Full Name" required />
    <span v-if="errors.name" class="error">{{ errors.name[0] }}</span>

    <input v-model="form.email" type="email" placeholder="Email" required />
    <span v-if="errors.email" class="error">{{ errors.email[0] }}</span>

    <input v-model="form.password" type="password" placeholder="Password" required />
    <span v-if="errors.password" class="error">{{ errors.password[0] }}</span>

    <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required />
    
    <input v-model="form.organization_name" type="text" placeholder="Organization Name" required />
    <span v-if="errors.organization_name" class="error">{{ errors.organization_name[0] }}</span>

    <div v-if="success" class="success">{{ success }}</div>
    <div v-if="errors.general" class="error">{{ errors.general }}</div>

    <button type="submit" :disabled="loading">
      {{ loading ? 'Registering...' : 'Register' }}
    </button>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';

const loading = ref(false);
const errors = ref({});
const success = ref('');
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  organization_name: ''
});

const handleRegister = async () => {
  loading.value = true;
  errors.value = {};
  success.value = '';

  try {
    const response = await fetch('http://localhost:8000/api/register', {
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
      localStorage.setItem('organization', JSON.stringify(data.meta.organization));
      
      success.value = 'Registration successful! Redirecting...';
      window.location.href = '/dashboard';
    } else {
      errors.value = data.errors || {};
    }
  } catch (err) {
    errors.value.general = 'Network error. Please try again.';
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

// Register function
async function register(name, email, password, passwordConfirmation, organizationName) {
  try {
    const response = await apiClient.post('/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
      organization_name: organizationName
    });

    // Save token
    const token = response.data.meta.token;
    localStorage.setItem('auth_token', token);
    localStorage.setItem('user', JSON.stringify(response.data.data));
    localStorage.setItem('organization', JSON.stringify(response.data.meta.organization));
    
    // Set default authorization header for future requests
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    return response.data;
  } catch (error) {
    if (error.response) {
      // Server responded with error
      console.error('Error:', error.response.data.message);
      console.error('Validation errors:', error.response.data.errors);
      throw new Error(error.response.data.message);
    } else {
      // Network error
      console.error('Network error:', error.message);
      throw error;
    }
  }
}

// Usage
register('John Doe', 'john@example.com', 'SecurePass123!', 'SecurePass123!', 'Acme Corp')
  .then(data => {
    console.log('Success:', data.message);
    console.log('Organization:', data.meta.organization.name);
  })
  .catch(error => console.error('Failed:', error.message));
```

### cURL Example
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "organization_name": "Acme Corporation"
  }'
```

---

## Organization Behavior

### How Organizations Work
- If an organization with the same name already exists, the user will be linked to it
- If the organization doesn't exist, a new one will be created
- Organization names are converted to slugs (e.g., "Acme Corp" → "acme-corp")
- New users are automatically assigned the "admin" role in their organization

### Example Scenarios

#### Scenario 1: New Organization
```json
// Request
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "organization_name": "New Company"
}

// Response - Organization is created
{
  "meta": {
    "organization": {
      "id": 1,
      "name": "New Company",
      "slug": "new-company",
      // ... new organization created
    }
  }
}
```

#### Scenario 2: Existing Organization
```json
// Request (second user registering for same company)
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "organization_name": "New Company"  // Same name
}

// Response - User linked to existing organization
{
  "meta": {
    "organization": {
      "id": 1,  // Same ID as first user
      "name": "New Company",
      // ... existing organization
    }
  }
}
```

---

## Using the Token

After successful registration, you'll receive a token in the response. Use it for all authenticated requests:

```javascript
const token = localStorage.getItem('auth_token');

// Example: Get current user
fetch('http://localhost:8000/api/userากรไก, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Important Notes

1. **Password Requirements**: Minimum 8 characters
2. **Email Uniqueness**: Email must not already be registered
3. **Password Confirmation**: Must match the password exactly
4. **Auto-Login**: After registration, you're automatically logged in
5. **Organization Linking**: Organizations are created/found by name (case-insensitive)
6. **Token Storage**: Store the token securely (localStorage, sessionStorage, or secure cookie)
7. **HTTPS**: Always use HTTPS in production for security
8. **Error Handling**: Always handle both network errors and API errors
9. **Loading States**: Show loading indicators during API calls

---

## Security Considerations

1. **Password Strength**: Consider adding client-side validation for:
   - Minimum length (8+ characters)
   - Mix of uppercase and lowercase
   - Numbers and special characters

2. **Email Verification**: Current implementation doesn't require email verification. Consider adding this in production.

3. **Rate Limiting**: Be aware of rate limiting on registration endpoints.

---

## Testing

### Test with Postman
Import the provided Postman collection (`docs/postman_collection.json`) and use the "Register" request.

### Test Data
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "TestPass123!",
  "password_confirmation": "TestPass123!",
  "organization_name": "Test Organization"
}
```

---

## Support

For issues or questions, contact: [your-email@example.com]

**Last Updated:** 2024-10-28

