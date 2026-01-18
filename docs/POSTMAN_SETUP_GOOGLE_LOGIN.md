# Postman Setup for Google Login API

## Quick Fix Guide

### Issue: "Client ID mismatch" Error

This error means the Google token's Client ID doesn't match the `GOOGLE_CLIENT_ID` in your `.env` file.

---

## Step 1: Get Real Google Client ID

1. Go to: https://console.cloud.google.com/
2. Select your project (or create new)
3. Go to: **APIs & Services** > **Credentials**
4. Click: **Create Credentials** > **OAuth client ID**
5. If asked, configure OAuth consent screen first
6. Select: **Web application**
7. Copy the **Client ID** (looks like: `123456789-abc...apps.googleusercontent.com`)

---

## Step 2: Update .env File

Open `.env` file and update:

```env
GOOGLE_CLIENT_ID=your-real-client-id-here.apps.googleusercontent.com
```

**Important:** Replace `your-real-client-id-here` with the actual Client ID from Google Cloud Console.

---

## Step 3: Clear Cache and Restart Server

```bash
php artisan config:clear
php artisan cache:clear
# Restart server (Ctrl+C then php artisan serve)
```

---

## Step 4: Get Real Google Token

You need a real Google ID token. Here are two ways:

### Option A: Google OAuth Playground (Easiest)

1. Go to: https://developers.google.com/oauthplayground/
2. Left sidebar: Select **"Google OAuth2 API v2"**
3. Check these scopes:
   - `openid`
   - `email`
   - `profile`
4. Click **"Authorize APIs"**
5. Login with your Google account
6. Click **"Exchange authorization code for tokens"**
7. Copy the **`id_token`** (starts with `eyJ...`)
8. **Important:** This token is valid for 1 hour only

### Option B: Use Your Flutter App

1. Run your Flutter app
2. Sign in with Google
3. Check app logs/console for the `id_token`
4. Copy that token

---

## Step 5: Postman Setup (Correct Way)

### Request Configuration:

1. **Method:** `POST`
2. **URL:** `{{APP_URL}}/api/auth/google`
   - Or: `http://localhost:8000/api/auth/google`

3. **Headers Tab:**
   ```
   Content-Type: application/json
   Accept: application/json
   ```

4. **Body Tab:**
   - ✅ Select **"raw"**
   - ✅ Select **"JSON"** (not Text, not form-data)
   - ❌ **DO NOT** use `form-data`
   - ❌ **DO NOT** use `x-www-form-urlencoded`

5. **Body Content (JSON):**
   ```json
   {
     "id_token": "eyJhbGciOiJSUzI1NiIs...",
     "email": "your-email@gmail.com",
     "name": "Your Name"
   }
   ```

### Complete Example:

```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjEyMzQ1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJhenAiOiJ5b3VyLWNsaWVudC1pZC5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsImF1ZCI6InlvdXItY2xpZW50LWlkLmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwic3ViIjoiMTIzNDU2Nzg5MDEyMzQ1Njc4OTAxIiwiaWF0IjoxNzAzODk2MDAwLCJleHAiOjE3MDM5MDAwMDAsImVtYWlsIjoiam9obi5kb2VAZ21haWwuY29tIiwiZW1haWxfdmVyaWZpZWQiOnRydWUsIm5hbWUiOiJKb2huIERvZSJ9.signature",
  "email": "john.doe@gmail.com",
  "name": "John Doe"
}
```

---

## Common Mistakes ❌

1. **Using form-data instead of raw JSON**
   - ❌ Wrong: Body tab > form-data
   - ✅ Correct: Body tab > raw > JSON

2. **Using placeholder/fake token**
   - ❌ Wrong: `"yaha-google-se-mila-real-id-token"`
   - ✅ Correct: Real token from Google OAuth Playground

3. **Using expired token**
   - Tokens expire after 1 hour
   - Get a new token if expired

4. **Wrong Client ID in .env**
   - ❌ Wrong: `123456789-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com`
   - ✅ Correct: Your real Client ID from Google Cloud Console

5. **Token from different Client ID**
   - The token must be issued for the same Client ID as in `.env`
   - Check token at https://jwt.io to see the `aud` field

---

## Debugging

### Check Token Client ID

1. Go to: https://jwt.io
2. Paste your `id_token`
3. Look at the **Payload** section
4. Find the `aud` field - this is the Client ID the token was issued for
5. This must match `GOOGLE_CLIENT_ID` in your `.env` file exactly

### Check Error Response

After the fix, if you still get an error, the response will show:

```json
{
  "success": false,
  "message": "Invalid Google token. Client ID mismatch.",
  "debug": {
    "configured_client_id": "what-backend-has",
    "token_client_id": "what-token-has",
    "hint": "Make sure the token was issued for the same Client ID as configured in .env file"
  }
}
```

Compare:
- `configured_client_id` = What's in your `.env` file
- `token_client_id` = What's in the token (from jwt.io)

They must match exactly!

---

## Success Response

If everything is correct, you'll get:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Your Name",
      "email": "your-email@gmail.com",
      "email_verified_at": "2024-12-19T10:30:00.000000Z",
      "created_at": "2024-12-01T10:30:00.000000Z",
      "updated_at": "2024-12-19T10:30:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer"
  }
}
```

---

## Quick Checklist

- [ ] Real Google Client ID in `.env` file
- [ ] Config cache cleared: `php artisan config:clear`
- [ ] Server restarted after `.env` changes
- [ ] Real Google token from OAuth Playground
- [ ] Postman: Body > raw > JSON (not form-data)
- [ ] Token's `aud` field matches `GOOGLE_CLIENT_ID` in `.env`
- [ ] Token is not expired (valid for 1 hour)

---

## Still Having Issues?

1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify `.env` file has correct Client ID
3. Decode token at https://jwt.io and check `aud` field
4. Make sure server was restarted after `.env` changes
5. Try getting a fresh token from OAuth Playground

