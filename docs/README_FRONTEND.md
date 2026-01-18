# MeetUI API - Frontend Developer Guide

Welcome! This document provides everything you need to integrate with the MeetUI API.

---

## 📚 Documentation Files

- **[API Quick Start](./API_QUICK_START.md)** - Quick reference for all endpoints
- **[Login API Details](./LOGIN_API.md)** - Complete login endpoint documentation with examples
- **[Postman Collection](./postman_collection.json)** - Importable collection for API testing

---

## 🔑 Base URL

```
Development:  http://localhost:8000
Production:   https://your-production-domain.com
```

---

## ⚡ Quick Start

### 1. Login
```javascript
POST /api/login

Body: {
  "email": "user@example.com",
  "password": "password123"
}

Response: {
  "data": { /* user info */ },
  "meta": { "token": "1|xxxxx" },  // ⚠️ SAVE THIS TOKEN
  "message": "Login successfully!"
}
```

### 2. Use Token in Requests
```javascript
GET /api/user

Headers: {
  "Authorization": "Bearer {token}"
}
```

---

## 📁 What's Included

### Documentation Files
- **API_QUICK_START.md** - Essential endpoints at a glance
- **LOGIN_API.md** - Detailed login documentation with React, Vue, Axios examples
- **postman_collection.json** - Ready-to-import Postman collection

### How to Use the Postman Collection
1. Open Postman
2. Click "Import"
3. Select `postman_collection.json`
4. Run "Login" request
5. Token will be automatically saved and used in subsequent requests

---

## 🛠️ Available Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/register` | POST | ❌ | Register new user |
| `/api/login` | POST | ❌ | Login and get token |
| `/api/user` | GET | ✅ | Get authenticated user |
| `/api/organizations` | GET | ❌ | List all organizations |
| `/api/organizations/search` | POST | ❌ | Search organization |

✅ = Requires authentication token

---

## 💡 Implementation Examples

The Login API documentation includes working examples for:
- ✅ Vanilla JavaScript (Fetch API)
- ✅ React with hooks
- ✅ Vue.js (Composition API)
- ✅ Axios
- ✅ cURL

[View Full Examples →](./LOGIN_API.md)

---

## 🔐 Authentication Flow

1. Seneca calls `POST /api/login` with credentials
2. API returns user data + token
3. Store token (localStorage/sessionStorage)
4. Include token in all protected requests:
   ```
   Authorization: Bearer {token}
   ```

---

## ⚠️ Important Notes

1. **CORS** - Make sure your frontend domain is allowed in Laravel CORS config
2. **CSRF** - API routes don't require CSRF tokens
3. **Headers** - Always include `Accept: application/json`
4. **Token Storage** - Store token securely (localStorage recommended)
5. **Error Handling** - Always handle network errors and API errors

---

## 🧪 Testing

Import the Postman collection and test all endpoints:
```bash
File -> Import -> Select postman_collection.json
```

---

## 📞 Need Help?

Check the detailed documentation:
- [API Quick Start Guide](./API_QUICK_START.md)
- [Login API Complete Documentation](./LOGIN_API.md)

Or contact the backend team.

---

**Happy Coding! 🚀**

