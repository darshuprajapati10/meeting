# Organization Save API Documentation

## Overview
This API endpoint allows you to create or update an organization with complete business details including GST information, shipping, and billing addresses. The same endpoint handles both operations - if an `id` is provided, it updates the existing organization; otherwise, it creates a new one.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/organizations/save`  
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

### Creating a New Organization

When creating an organization, **do not** include the `id` field:

```json
{
  "type": "business",
  "name": "Acme Corporation",
  "email": "contact@acmecorp.com",
  "gst_status": "registered",
  "gst_in": "27AABCU9603R1ZM",
  "place_of_supply": "Maharashtra",
  "shipping_address": "123 Business Park, Sector 5",
  "shipping_city": "Mumbai",
  "shipping_zip": "400001",
  "shipping_phone": "9876543210",
  "same_as_shipping": true
}
```

**Note:** If `same_as_shipping` is `true`, you can omit the billing fields - they will be automatically copied from shipping fields.

### Updating an Existing Organization

When updating an organization, **include** the `id` field:

```json
{
  "id": 1,
  "type": "business",
  "name": "Acme Corporation Updated",
  "email": "contact@acmecorp.com",
  "gst_status": "registered",
  "gst_in": "27AABCU9603R1ZM",
  "place_of_supply": "Maharashtra",
  "shipping_address": "456 New Business Park, Sector 10",
  "shipping_city": "Mumbai",
  "shipping_zip": "400002",
  "shipping_phone": "9876543210",
  "same_as_shipping": false,
  "billing_address": "789 Corporate Tower, Sector 15",
  "billing_city": "Mumbai",
  "billing_zip": "400003",
  "billing_phone": "9876543211"
}
```

---

## Field Specifications

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | Organization ID (only for updates) | Must exist in organizations table |
| `type` | string | **Yes** | Organization type | Must be: `business` or `individual` |
| `name` | string | **Yes** | Organization name | Max 255 characters |
| `email` | string | **Yes** | Organization email | Valid email format, max 255 characters |
| `gst_status` | string | No | GST registration status | Must be: `registered` or `unregistered` |
| `gst_in` | string | No | GST Identification Number | Max 15 characters |
| `place_of_supply` | string | No | Place of supply (State) | Max 255 characters |
| `shipping_address` | string | **Yes** | Shipping address | Max 500 characters |
| `shipping_city` | string | **Yes** | Shipping city | Max 255 characters |
| `shipping_zip` | string | **Yes** | Shipping ZIP/postal code | Max 20 characters |
| `shipping_phone` | string | **Yes** | Shipping phone number | Max 20 characters |
| `same_as_shipping` | boolean | No | Copy shipping to billing | Default: `false` |
| `billing_address` | string | **Yes** | Billing address | Max 500 characters (required if `same_as_shipping` is false) |
| `billing_city` | string | **Yes** | Billing city | Max 255 characters (required if `same_as_shipping` is false) |
| `billing_zip` | string | **Yes** | Billing ZIP/postal code | Max 20 characters (required if `same_as_shipping` is false) |
| `billing_phone` | string | **Yes** | Billing phone number | Max 20 characters (required if `same_as_shipping` is false) |

### Important Notes on `same_as_shipping` Field

- If `same_as_shipping` is set to `true`, the billing fields will be **automatically copied** from shipping fields
- You can omit billing fields when `same_as_shipping` is `true`
- If `same_as_shipping` is `false` or not provided, all billing fields are **required**

---

## Response Examples

### Success - Organization Created (201)

```json
{
  "data": {
    "id": 1,
    "type": "business",
    "name": "Acme Corporation",
    "slug": "acme-corporation-1733567890",
    "description": null,
    "email": "contact@acmecorp.com",
    "phone": null,
    "address": null,
    "gst_status": "registered",
    "gst_in": "27AABCU9603R1ZM",
    "place_of_supply": "Maharashtra",
    "shipping_address": "123 Business Park, Sector 5",
    "shipping_city": "Mumbai",
    "shipping_zip": "400001",
    "shipping_phone": "9876543210",
    "billing_address": "123 Business Park, Sector 5",
    "billing_city": "Mumbai",
    "billing_zip": "400001",
    "billing_phone": "9876543210",
    "status": "active",
    "created_at": "2025-11-06T10:30:00.000000Z",
    "updated_at": "2025-11-06T10:30:00.000000Z",
    "deleted_at": null
  },
  "message": "Organization created successfully."
}
```

### Success - Organization Updated (200)

```json
{
  "data": {
    "id": 1,
    "type": "business",
    "name": "Acme Corporation Updated",
    "slug": "acme-corporation-1733567890",
    "description": null,
    "email": "contact@acmecorp.com",
    "phone": null,
    "address": null,
    "gst_status": "registered",
    "gst_in": "27AABCU9603R1ZM",
    "place_of_supply": "Maharashtra",
    "shipping_address": "456 New Business Park, Sector 10",
    "shipping_city": "Mumbai",
    "shipping_zip": "400002",
    "shipping_phone": "9876543210",
    "billing_address": "789 Corporate Tower, Sector 15",
    "billing_city": "Mumbai",
    "billing_zip": "400003",
    "billing_phone": "9876543211",
    "status": "active",
    "created_at": "2025-11-06T10:30:00.000000Z",
    "updated_at": "2025-11-06T11:45:00.000000Z",
    "deleted_at": null
  },
  "message": "Organization updated successfully."
}
```

### Error - Validation Failed (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "type": [
      "The type field is required."
    ],
    "name": [
      "The name field is required."
    ],
    "email": [
      "The email must be a valid email address.",
      "The email field is required."
    ],
    "shipping_address": [
      "The shipping address field is required."
    ],
    "billing_address": [
      "The billing address field is required."
    ]
  }
}
```

### Error - Organization Not Found (404)

When trying to update an organization that doesn't exist or doesn't belong to the user:

```json
{
  "message": "Organization not found or you do not have permission to update it."
}
```

---

## JavaScript/TypeScript Examples

### Fetch API Example

```javascript
// Create a new organization
async function createOrganization(orgData, token) {
  try {
    const response = await fetch('http://your-api-url/api/organizations/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        type: orgData.type,
        name: orgData.name,
        email: orgData.email,
        gst_status: orgData.gstStatus || null,
        gst_in: orgData.gstIn || null,
        place_of_supply: orgData.placeOfSupply || null,
        shipping_address: orgData.shippingAddress,
        shipping_city: orgData.shippingCity,
        shipping_zip: orgData.shippingZip,
        shipping_phone: orgData.shippingPhone,
        same_as_shipping: orgData.sameAsShipping || false,
        billing_address: orgData.billingAddress,
        billing_city: orgData.billingCity,
        billing_zip: orgData.billingZip,
        billing_phone: orgData.billingPhone
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to save organization');
    }

    return data;
  } catch (error) {
    console.error('Error saving organization:', error);
    throw error;
  }
}

// Update an existing organization
async function updateOrganization(orgId, orgData, token) {
  try {
    const response = await fetch('http://your-api-url/api/organizations/save', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: orgId,
        type: orgData.type,
        name: orgData.name,
        email: orgData.email,
        gst_status: orgData.gstStatus || null,
        gst_in: orgData.gstIn || null,
        place_of_supply: orgData.placeOfSupply || null,
        shipping_address: orgData.shippingAddress,
        shipping_city: orgData.shippingCity,
        shipping_zip: orgData.shippingZip,
        shipping_phone: orgData.shippingPhone,
        same_as_shipping: orgData.sameAsShipping || false,
        billing_address: orgData.billingAddress,
        billing_city: orgData.billingCity,
        billing_zip: orgData.billingZip,
        billing_phone: orgData.billingPhone
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update organization');
    }

    return data;
  } catch (error) {
    console.error('Error updating organization:', error);
    throw error;
  }
}
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

// Create or update organization
async function saveOrganization(orgData) {
  try {
    const response = await apiClient.post('/organizations/save', {
      // Include id only for updates
      ...(orgData.id && { id: orgData.id }),
      type: orgData.type,
      name: orgData.name,
      email: orgData.email,
      gst_status: orgData.gstStatus || null,
      gst_in: orgData.gstIn || null,
      place_of_supply: orgData.placeOfSupply || null,
      shipping_address: orgData.shippingAddress,
      shipping_city: orgData.shippingCity,
      shipping_zip: orgData.shippingZip,
      shipping_phone: orgData.shippingPhone,
      same_as_shipping: orgData.sameAsShipping || false,
      billing_address: orgData.billingAddress,
      billing_city: orgData.billingCity,
      billing_zip: orgData.billingZip,
      billing_phone: orgData.billingPhone
    });

    return response.data;
  } catch (error) {
    if (error.response) {
      // Handle validation errors
      console.error('Validation errors:', error.response.data.errors);
      throw error.response.data;
    }
    throw error;
  }
}
```

### React Hook Example

```typescript
import { useState } from 'react';
import axios from 'axios';

interface OrganizationFormData {
  id?: number;
  type: 'business' | 'individual';
  name: string;
  email: string;
  gstStatus?: 'registered' | 'unregistered' | null;
  gstIn?: string | null;
  placeOfSupply?: string | null;
  shippingAddress: string;
  shippingCity: string;
  shippingZip: string;
  shippingPhone: string;
  sameAsShipping: boolean;
  billingAddress?: string;
  billingCity?: string;
  billingZip?: string;
  billingPhone?: string;
}

export function useSaveOrganization() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<any>(null);

  const saveOrganization = async (formData: OrganizationFormData, token: string) => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post(
        '/api/organizations/save',
        {
          ...(formData.id && { id: formData.id }),
          type: formData.type,
          name: formData.name,
          email: formData.email,
          gst_status: formData.gstStatus || null,
          gst_in: formData.gstIn || null,
          place_of_supply: formData.placeOfSupply || null,
          shipping_address: formData.shippingAddress,
          shipping_city: formData.shippingCity,
          shipping_zip: formData.shippingZip,
          shipping_phone: formData.shippingPhone,
          same_as_shipping: formData.sameAsShipping,
          billing_address: formData.billingAddress || null,
          billing_city: formData.billingCity || null,
          billing_zip: formData.billingZip || null,
          billing_phone: formData.billingPhone || null,
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

  return { saveOrganization, loading, error };
}
```

### React Component Example (Complete)

```javascript
import React, { useState } from 'react';

function OrganizationForm({ token, organization, onSaveSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({
    type: organization?.type || 'business',
    name: organization?.name || '',
    email: organization?.email || '',
    gstStatus: organization?.gst_status || '',
    gstIn: organization?.gst_in || '',
    placeOfSupply: organization?.place_of_supply || '',
    shippingAddress: organization?.shipping_address || '',
    shippingCity: organization?.shipping_city || '',
    shippingZip: organization?.shipping_zip || '',
    shippingPhone: organization?.shipping_phone || '',
    sameAsShipping: organization ? false : true,
    billingAddress: organization?.billing_address || '',
    billingCity: organization?.billing_city || '',
    billingZip: organization?.billing_zip || '',
    billingPhone: organization?.billing_phone || '',
  });

  const handleSameAsShippingChange = (checked) => {
    setFormData(prev => ({
      ...prev,
      sameAsShipping: checked,
      billingAddress: checked ? prev.shippingAddress : prev.billingAddress,
      billingCity: checked ? prev.shippingCity : prev.billingCity,
      billingZip: checked ? prev.shippingZip : prev.billingZip,
      billingPhone: checked ? prev.shippingPhone : prev.billingPhone,
    }));
  };

  const handleShippingChange = (field, value) => {
    setFormData(prev => {
      const updated = { ...prev, [field]: value };
      // If same as shipping is checked, update billing too
      if (prev.sameAsShipping && field.startsWith('shipping')) {
        const billingField = field.replace('shipping', 'billing');
        updated[billingField] = value;
      }
      return updated;
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/organizations/save', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          ...(organization?.id && { id: organization.id }),
          type: formData.type,
          name: formData.name,
          email: formData.email,
          gst_status: formData.gstStatus || null,
          gst_in: formData.gstIn || null,
          place_of_supply: formData.placeOfSupply || null,
          shipping_address: formData.shippingAddress,
          shipping_city: formData.shippingCity,
          shipping_zip: formData.shippingZip,
          shipping_phone: formData.shippingPhone,
          same_as_shipping: formData.sameAsShipping,
          billing_address: formData.billingAddress,
          billing_city: formData.billingCity,
          billing_zip: formData.billingZip,
          billing_phone: formData.billingPhone
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to save organization');
      }

      if (onSaveSuccess) {
        onSaveSuccess(data.data);
      }

      alert(data.message || 'Organization saved successfully');
    } catch (err) {
      setError(err.message);
      alert('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Type Selection */}
      <div>
        <label>Type *</label>
        <div>
          <label>
            <input
              type="radio"
              value="business"
              checked={formData.type === 'business'}
              onChange={(e) => setFormData(prev => ({ ...prev, type: e.target.value }))}
            />
            Business
          </label>
          <label>
            <input
              type="radio"
              value="individual"
              checked={formData.type === 'individual'}
              onChange={(e) => setFormData(prev => ({ ...prev, type: e.target.value }))}
            />
            Individual
          </label>
        </div>
      </div>

      {/* Name */}
      <div>
        <label>Name *</label>
        <input
          type="text"
          value={formData.name}
          onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
          required
        />
      </div>

      {/* Email */}
      <div>
        <label>Email *</label>
        <input
          type="email"
          value={formData.email}
          onChange={(e) => setFormData(prev => ({ ...prev, email: e.target.value }))}
          required
        />
      </div>

      {/* GST Information */}
      <div>
        <label>GST Status</label>
        <select
          value={formData.gstStatus}
          onChange={(e) => setFormData(prev => ({ ...prev, gstStatus: e.target.value }))}
        >
          <option value="">Select GST Status</option>
          <option value="registered">Registered</option>
          <option value="unregistered">Unregistered</option>
        </select>
      </div>

      <div>
        <label>GST IN</label>
        <input
          type="text"
          value={formData.gstIn}
          onChange={(e) => setFormData(prev => ({ ...prev, gstIn: e.target.value }))}
          maxLength={15}
        />
      </div>

      <div>
        <label>Place of Supply</label>
        <input
          type="text"
          value={formData.placeOfSupply}
          onChange={(e) => setFormData(prev => ({ ...prev, placeOfSupply: e.target.value }))}
        />
      </div>

      {/* Shipping Address */}
      <h3>Shipping Address</h3>
      <div>
        <label>Shipping Address *</label>
        <textarea
          value={formData.shippingAddress}
          onChange={(e) => handleShippingChange('shippingAddress', e.target.value)}
          required
        />
      </div>

      <div>
        <label>Shipping City *</label>
        <input
          type="text"
          value={formData.shippingCity}
          onChange={(e) => handleShippingChange('shippingCity', e.target.value)}
          required
        />
      </div>

      <div>
        <label>Shipping ZIP *</label>
        <input
          type="text"
          value={formData.shippingZip}
          onChange={(e) => handleShippingChange('shippingZip', e.target.value)}
          required
        />
      </div>

      <div>
        <label>Shipping Phone *</label>
        <input
          type="text"
          value={formData.shippingPhone}
          onChange={(e) => handleShippingChange('shippingPhone', e.target.value)}
          required
        />
      </div>

      {/* Same as Shipping Checkbox */}
      <div>
        <label>
          <input
            type="checkbox"
            checked={formData.sameAsShipping}
            onChange={(e) => handleSameAsShippingChange(e.target.checked)}
          />
          Same as shipping address
        </label>
      </div>

      {/* Billing Address */}
      <h3>Billing Address</h3>
      <div>
        <label>Billing Address *</label>
        <textarea
          value={formData.billingAddress}
          onChange={(e) => setFormData(prev => ({ ...prev, billingAddress: e.target.value }))}
          disabled={formData.sameAsShipping}
          required={!formData.sameAsShipping}
        />
      </div>

      <div>
        <label>Billing City *</label>
        <input
          type="text"
          value={formData.billingCity}
          onChange={(e) => setFormData(prev => ({ ...prev, billingCity: e.target.value }))}
          disabled={formData.sameAsShipping}
          required={!formData.sameAsShipping}
        />
      </div>

      <div>
        <label>Billing ZIP *</label>
        <input
          type="text"
          value={formData.billingZip}
          onChange={(e) => setFormData(prev => ({ ...prev, billingZip: e.target.value }))}
          disabled={formData.sameAsShipping}
          required={!formData.sameAsShipping}
        />
      </div>

      <div>
        <label>Billing Phone *</label>
        <input
          type="text"
          value={formData.billingPhone}
          onChange={(e) => setFormData(prev => ({ ...prev, billingPhone: e.target.value }))}
          disabled={formData.sameAsShipping}
          required={!formData.sameAsShipping}
        />
      </div>

      <button type="submit" disabled={loading}>
        {loading ? 'Saving...' : 'Save Organization'}
      </button>

      {error && <div style={{ color: 'red' }}>{error}</div>}
    </form>
  );
}
```

---

## cURL Examples

### Create Organization

```bash
curl -X POST "http://your-api-url/api/organizations/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "business",
    "name": "Acme Corporation",
    "email": "contact@acmecorp.com",
    "gst_status": "registered",
    "gst_in": "27AABCU9603R1ZM",
    "place_of_supply": "Maharashtra",
    "shipping_address": "123 Business Park, Sector 5",
    "shipping_city": "Mumbai",
    "shipping_zip": "400001",
    "shipping_phone": "9876543210",
    "same_as_shipping": true
  }'
```

### Update Organization

```bash
curl -X POST "http://your-api-url/api/organizations/save" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id": 1,
    "type": "business",
    "name": "Acme Corporation Updated",
    "email": "contact@acmecorp.com",
    "gst_status": "registered",
    "gst_in": "27AABCU9603R1ZM",
    "place_of_supply": "Maharashtra",
    "shipping_address": "456 New Business Park, Sector 10",
    "shipping_city": "Mumbai",
    "shipping_zip": "400002",
    "shipping_phone": "9876543210",
    "same_as_shipping": false,
    "billing_address": "789 Corporate Tower, Sector 15",
    "billing_city": "Mumbai",
    "billing_zip": "400003",
    "billing_phone": "9876543211"
  }'
```

---

## Important Notes

### 1. Same as Shipping Address Feature
- When `same_as_shipping` is `true`, the API automatically copies all shipping address fields to billing address fields
- You can omit billing fields when `same_as_shipping` is `true`
- The copying happens server-side, so you don't need to manually duplicate the fields

### 2. Organization Type
- `business`: For business organizations
- `individual`: For individual/personal organizations
- This field is required and affects how the organization is displayed

### 3. User Association
- When creating a new organization, the authenticated user is automatically attached as an **admin**
- Users can only update organizations they belong to
- The organization is automatically associated with the user's account

### 4. Update Behavior
- When updating (with `id`), only organizations within the user's access can be updated
- Attempting to update an organization from a different user will result in a 404 error

### 5. GST Information
- GST fields are optional but recommended for Indian businesses
- `gst_status` can be `registered` or `unregistered`
- `gst_in` should be a valid GSTIN format (15 characters max)
- `place_of_supply` is typically the state name

---

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Organization updated successfully |
| `201` | Organization created successfully |
| `401` | Unauthorized (missing or invalid token) |
| `404` | Organization not found (for updates) |
| `422` | Validation error |
| `500` | Server error |

---

## Error Handling Best Practices

1. **Check Response Status**: Always check the HTTP status code before processing the response
2. **Handle Validation Errors**: Display field-specific errors from `errors` object
3. **Network Errors**: Handle timeout and network connectivity issues
4. **Token Expiration**: Handle 401 errors by redirecting to login

Example error handling:

```javascript
try {
  const response = await fetch('/api/organizations/save', {...});
  const data = await response.json();
  
  if (!response.ok) {
    if (response.status === 422) {
      // Handle validation errors
      Object.keys(data.errors).forEach(field => {
        console.error(`${field}: ${data.errors[field].join(', ')}`);
        // Display error to user
      });
    } else if (response.status === 401) {
      // Handle authentication error
      // Redirect to login
      window.location.href = '/login';
    } else {
      // Handle other errors
      console.error(data.message);
      alert(data.message);
    }
    return;
  }
  
  // Success
  console.log('Organization saved:', data.data);
  alert(data.message);
} catch (error) {
  console.error('Network error:', error);
  alert('Network error. Please check your connection.');
}
```

---

## Example Request Bodies

### Example 1: Business Organization with GST (Same Shipping/Billing)

```json
{
  "type": "business",
  "name": "Tech Solutions Pvt Ltd",
  "email": "info@techsolutions.com",
  "gst_status": "registered",
  "gst_in": "27AABCT1234D1Z5",
  "place_of_supply": "Gujarat",
  "shipping_address": "100 Tech Park, IT Hub",
  "shipping_city": "Ahmedabad",
  "shipping_zip": "380015",
  "shipping_phone": "9876543210",
  "same_as_shipping": true
}
```

### Example 2: Individual Organization

```json
{
  "type": "individual",
  "name": "John Doe",
  "email": "john.doe@example.com",
  "place_of_supply": "Karnataka",
  "shipping_address": "456 Residential Street, Apartment 101",
  "shipping_city": "Bangalore",
  "shipping_zip": "560001",
  "shipping_phone": "9876543210",
  "same_as_shipping": true
}
```

### Example 3: Business with Different Billing Address

```json
{
  "type": "business",
  "name": "Global Enterprises",
  "email": "contact@globalenterprises.com",
  "gst_status": "registered",
  "gst_in": "29AABCG1234F1Z6",
  "place_of_supply": "Delhi",
  "shipping_address": "123 Warehouse Complex, Sector 5",
  "shipping_city": "Gurgaon",
  "shipping_zip": "122001",
  "shipping_phone": "9876543210",
  "same_as_shipping": false,
  "billing_address": "456 Corporate Office, Connaught Place",
  "billing_city": "New Delhi",
  "billing_zip": "110001",
  "billing_phone": "9876543211"
}
```

---

## Testing

You can test the API using:
- **Postman**: Create a POST request to `/api/organizations/save` with Bearer token and request body
- **cURL**: Use the example commands above
- **Browser Console**: Use fetch API directly in browser dev tools
- **Frontend Application**: Integrate into your React/Vue/Angular app

---

## Support

For questions or issues, please contact the backend team or refer to the main API documentation.

