# API Quick Start Guide

## 🚀 Login Endpoint (Quick Reference)

### Endpoint
```
POST /api/login
```

### Request
```javascript
{
  "email": "user@example.com",
  "password": "password123"
}
```

### Response
```javascript
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    // ... other user fields
  },
  "meta": {
    "token": "1|xxxxxxxxxxxx"  // ⚠️ SAVE THIS TOKEN!
  },
  "message": "Login successfully!"
}
```

---

## 💻 Quick Implementation (Copy & Paste)

### JavaScript Fetch
```javascript
const response = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});

const data = await response.json();

// Save token
localStorage.setItem('auth_token', data.meta.token);
```

### Use Token in Requests
```javascript
const token = localStorage.getItem('auth_token');

fetch('http://localhost:8000/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

---

## 📋 All Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST   | /api/register | No | Register new user |
| POST   | /api/login | No | Login and get token |
| GET    | /api/user | Yes | Get current user |
| GET    | /api/organizations | No | List organizations |
| POST   | /api/organizations/search | No | Search organization |
| POST   | /api/contacts/index | Yes | List contacts |
| POST   | /api/contacts/show | Yes | Get single contact |
| POST   | /api/contacts/save | Yes | Create/update contact |
| POST   | /api/contacts/delete | Yes | Delete contact |
| POST   | /api/contacts/dropdown | Yes | Get contacts dropdown |
| POST   | /api/contacts/favourite | Yes | Toggle contact favourite |
| POST   | /api/contact-groups | Yes | Get contact groups |
| POST   | /api/survey/index | Yes | List surveys |
| POST   | /api/survey/show | Yes | Get single survey |
| POST   | /api/survey/save | Yes | Create/update survey |
| POST   | /api/survey/delete | Yes | Delete survey |
| POST   | /api/survey/dropdown | Yes | Get surveys dropdown |
| POST   | /api/meeting/index | Yes | List meetings |
| POST   | /api/meeting/show | Yes | Get single meeting |
| POST   | /api/meeting/save | Yes | Create/update meeting |
| POST   | /api/meeting/delete | Yes | Delete meeting |
| POST   | /api/meeting/current-month | Yes | Get current month calendar data |
| POST   | /api/meeting/current-week | Yes | Get current week calendar data |
| POST   | /api/meeting/current-day | Yes | Get current day meetings |

---

## 🔐 Authentication

Include token in Authorization header:
```
Authorization: Bearer {token}
```

---

## ⚠️ Error Handling

All errors return in this format:
```javascript
{
  "message": "Error message",
  "errors": {
    "field": ["Error for specific field"]
  }
}
```

---

**Full Documentation:** See `LOGIN_API.md` for complete details with examples.

