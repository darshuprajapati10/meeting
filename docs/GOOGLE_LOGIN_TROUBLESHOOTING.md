# Google Sign-In Backend Troubleshooting Documentation

## Overview

The backend Google Sign-In endpoint is located at `POST /api/auth/google` in `app/Http/Controllers/Auth/LoginController.php`. This endpoint verifies Google ID tokens and creates or logs in users automatically.

---

## Configuration Requirements

### 1. Environment Variables

Add these to your `.env` file:

```env
# Required - Google OAuth Client ID (Web)
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com

# Optional - Restrict Google login to specific email (for testing)
GOOGLE_LOGIN_ALLOWED_EMAIL=allowed-email@gmail.com
```

### 2. After Configuration

**IMPORTANT:** After adding or updating `.env` file, you MUST:

1. Clear Laravel configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

2. **Restart the Laravel server** (this is critical):

```bash
# Stop current server (Ctrl+C)
php artisan serve
```

**Why?** Laravel reads environment variables when the server starts. If you change `.env` without restarting, the old values remain in memory.

---

## Common Errors & Solutions

### Error 1: "Google Client ID is not configured" (500)

**Error Response:**

```json
{
  "success": false,
  "message": "Google Client ID is not configured. Please set GOOGLE_CLIENT_ID in your .env file."
}
```

**Cause:** `GOOGLE_CLIENT_ID` is missing or empty in `.env` file.

**Solution:**

1. Check if `GOOGLE_CLIENT_ID` exists in `.env`:

```bash
grep GOOGLE_CLIENT_ID .env
```

2. If missing, add it:

```env
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
```

3. Clear config cache and restart server:

```bash
php artisan config:clear
# Restart server
```

---

### Error 2: "Invalid Google token. Client ID mismatch" (401)

**Error Response:**

```json
{
  "success": false,
  "message": "Invalid Google token. Client ID mismatch."
}
```

**Cause:** The token's client ID doesn't match the `GOOGLE_CLIENT_ID` in `.env`, OR the server hasn't reloaded the environment variable.

**Solution Steps:**

1. **Verify .env file has correct Client ID:**

```bash
cat .env | grep GOOGLE_CLIENT_ID
```

Should show your actual client ID.

2. **Check the token's client ID:**

   - Decode the JWT token at https://jwt.io
   - Look for `aud` (audience) or `azp` (authorized party) in the payload
   - It should match your `GOOGLE_CLIENT_ID`

3. **Clear all caches:**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

4. **RESTART the Laravel server:**

```bash
# Stop current server (Ctrl+C)
php artisan serve
```

5. **Check Laravel logs:**

```bash
tail -f storage/logs/laravel.log
```

6. **Verify token and client ID match:**

   - Token's `aud` field must match `GOOGLE_CLIENT_ID` exactly (case-sensitive)
   - Both should be the same Google OAuth client ID

---

### Error 3: "Invalid Google token. Please try again" (401)

**Error Response:**

```json
{
  "success": false,
  "message": "Invalid Google token. Please try again."
}
```

Or if placeholder token detected:

```json
{
  "success": false,
  "message": "Invalid Google token. Please provide a valid Google ID token from Google OAuth flow.",
  "hint": "The token appears to be a placeholder. You need to get a real token from Google Sign-In."
}
```

**Cause:** Token verification with Google's API failed. Possible reasons:

- Token is expired
- Token is invalid or malformed
- Token is a placeholder (not a real token)
- Network issue reaching Google's API
- Token was not issued by Google

**Solution:**

1. **For Postman Testing - Get Real Google Token:**

   **Option A: Use Google OAuth Playground**
   
   - Go to: https://developers.google.com/oauthplayground/
   - Left side: Select "Google OAuth2 API v2"
   - Check scopes: `openid`, `email`, `profile`
   - Click "Authorize APIs"
   - Login with Google account
   - Click "Exchange authorization code for tokens"
   - Copy the `id_token` from response
   - Use this token in Postman

   **Option B: Use Flutter/Web App**
   
   - Use your Flutter app to sign in with Google
   - Get the `id_token` from the app logs
   - Use that token in Postman

2. **Check token expiration:**

   - Decode JWT at https://jwt.io
   - Check `exp` (expiration) field
   - Tokens expire after 1 hour

3. **Verify network connectivity:**

```bash
curl "https://oauth2.googleapis.com/tokeninfo?id_token=YOUR_TOKEN"
```

4. **Check Laravel logs:**

```bash
tail -50 storage/logs/laravel.log
```

5. **Ensure token is from correct OAuth flow:**

   - Token must be from Google Identity Services
   - Must be for the correct client ID
   - **Cannot use placeholder/fake tokens**

---

### Error 4: "Email mismatch" (422)

**Error Response:**

```json
{
  "success": false,
  "message": "Email mismatch. The provided email does not match the Google account."
}
```

**Cause:** The email in the request doesn't match the email in the verified Google token.

**Solution:**

- Frontend should send the email extracted from the Google token
- Backend verifies the email from the token matches the request email
- This is a security check to prevent email spoofing

---

### Error 5: "This email is not authorized to login via Google" (403)

**Error Response:**

```json
{
  "success": false,
  "message": "This email is not authorized to login via Google."
}
```

**Cause:** `GOOGLE_LOGIN_ALLOWED_EMAIL` is configured and the email doesn't match.

**Solution:**

1. Check `.env`:

```bash
grep GOOGLE_LOGIN_ALLOWED_EMAIL .env
```

2. **Option A:** Remove the restriction (allow all emails):

```bash
# Remove or comment out this line in .env
# GOOGLE_LOGIN_ALLOWED_EMAIL=allowed-email@gmail.com
```

3. **Option B:** Set it to the specific email you want to allow:

```env
GOOGLE_LOGIN_ALLOWED_EMAIL=your-email@gmail.com
```

4. Clear cache and restart server after changes.

---

## Backend Implementation Flow

The `googleLogin()` method in `LoginController.php` follows this flow:

1. **Validate Request** (GoogleLoginRequest)
   - Required: `id_token`, `email`, `name`
   - Optional: `access_token`, `google_id`

2. **Check Configuration** (line 214)
   - Verifies `GOOGLE_CLIENT_ID` is set in `.env`
   - Returns 500 if missing

3. **Verify Token with Google** (line 224)
   - Calls: `GET https://oauth2.googleapis.com/tokeninfo?id_token={token}`
   - Returns 401 if verification fails

4. **Verify Client ID Match** (line 238)
   - Compares `payload['aud']` with `GOOGLE_CLIENT_ID`
   - Returns 401 if mismatch

5. **Extract and Verify Email** (line 246)
   - Gets email from verified token
   - Compares with request email
   - Checks `GOOGLE_LOGIN_ALLOWED_EMAIL` restriction if set

6. **Find or Create User** (line 269)
   - Searches by email or `google_id`
   - If exists: logs in and returns token (200)
   - If new: creates user with verified email and returns token (201)

---

## Debugging Steps

### Step 1: Check Environment Configuration

```bash
# Check if GOOGLE_CLIENT_ID is set
grep GOOGLE_CLIENT_ID .env

# Check what Laravel sees (after cache clear)
php artisan tinker
>>> env('GOOGLE_CLIENT_ID')
>>> exit
```

### Step 2: Check Laravel Logs

```bash
# View recent logs
tail -50 storage/logs/laravel.log

# Watch logs in real-time
tail -f storage/logs/laravel.log
```

### Step 3: Test Token Verification Manually

```bash
# Replace {YOUR_TOKEN} with actual token from frontend logs
curl "https://oauth2.googleapis.com/tokeninfo?id_token={YOUR_TOKEN}"
```

Check the response:

- `aud` field should match your `GOOGLE_CLIENT_ID`
- `email` field should match the email in request
- `exp` field should not be expired

### Step 4: Verify Server is Using Latest Config

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Restart server
# Stop current server (Ctrl+C in terminal)
php artisan serve
```

### Step 5: Verify Token and Client ID

1. Decode your JWT token at https://jwt.io
2. Check the `aud` (audience) field in the payload
3. Compare with `GOOGLE_CLIENT_ID` in your `.env` file
4. They must match exactly (case-sensitive)

---

## Quick Troubleshooting Checklist

When debugging Google Sign-In issues, check:

- [ ] `GOOGLE_CLIENT_ID` is set in `.env` file
- [ ] Client ID matches the token's `aud`/`azp` field
- [ ] Config cache cleared: `php artisan config:clear`
- [ ] Application cache cleared: `php artisan cache:clear`
- [ ] **Laravel server restarted** after `.env` changes
- [ ] Token is not expired (check `exp` in JWT)
- [ ] Network connectivity to `oauth2.googleapis.com`
- [ ] Laravel logs checked for detailed errors
- [ ] Token's client ID matches backend configuration

---

## Current Issue Resolution

For the "Client ID mismatch" error:

1. **Verify .env has correct client ID:**

```bash
grep GOOGLE_CLIENT_ID .env
```

Should show your actual client ID.

2. **Clear all caches:**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

3. **RESTART Laravel server:**

```bash
# Stop current server (Ctrl+C)
php artisan serve
```

4. **Try Google Sign-In again from frontend**

5. **If still failing:**

   - Decode token at https://jwt.io and check `aud` field
   - Compare with `GOOGLE_CLIENT_ID` in `.env`
   - Check Laravel logs: `tail -f storage/logs/laravel.log`

---

## API Endpoint Details

**URL:** `POST /api/auth/google`

**Route:** Defined in `routes/api.php` (line 23)

**Controller:** `App\Http\Controllers\Auth\LoginController@googleLogin`

**File Location:** `app/Http/Controllers/Auth/LoginController.php` (line 210)

**Request Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Request Body:**

```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIs...",  // Required - JWT from Google
  "email": "user@gmail.com",              // Required
  "name": "User Name",                     // Required
  "access_token": "ya29.a0AfH6SMBx...",   // Optional
  "google_id": "123456789012345678901"     // Optional
}
```

**Success Response (200 - Existing User):**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "User Name",
      "email": "user@gmail.com",
      "email_verified_at": "2024-12-19T10:30:00.000000Z",
      "created_at": "2024-12-01T10:30:00.000000Z",
      "updated_at": "2024-12-19T10:30:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer"
  }
}
```

**Success Response (201 - New User):**

```json
{
  "success": true,
  "message": "Account created and logged in successfully",
  "data": {
    "user": {
      "id": 2,
      "name": "User Name",
      "email": "user@gmail.com",
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

## Testing the Endpoint

### Using cURL

```bash
curl -X POST "http://localhost:8000/api/auth/google" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id_token": "YOUR_GOOGLE_ID_TOKEN",
    "email": "user@gmail.com",
    "name": "User Name",
    "google_id": "123456789012345678901"
  }'
```

### Using Postman

1. **Method:** POST
2. **URL:** `http://localhost:8000/api/auth/google`
3. **Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`
4. **Body (raw JSON):**
   - **Important:** Select "raw" and "JSON" (NOT form-data)
   - Use this format:

```json
{
  "id_token": "YOUR_GOOGLE_ID_TOKEN",
  "email": "user@gmail.com",
  "name": "User Name",
  "google_id": "123456789012345678901"
}
```

**Common Postman Mistakes:**

- ❌ Using `form-data` instead of `raw JSON`
- ❌ Using placeholder text like "yaha-google-se-mila-real-id-token"
- ❌ Using expired tokens
- ❌ Using tokens from wrong Google project

**How to Get Real Token for Postman:**

1. Go to: https://developers.google.com/oauthplayground/
2. Select "Google OAuth2 API v2"
3. Check: `openid`, `email`, `profile`
4. Click "Authorize APIs"
5. Login with Google
6. Click "Exchange authorization code for tokens"
7. Copy the `id_token` (starts with `eyJ...`)
8. Use this token in Postman (it's valid for 1 hour)

---

## Logging

The backend logs errors for debugging:

**Location:** `storage/logs/laravel.log`

**Log Entry Example:**

```
[2024-12-19 10:30:00] local.ERROR: Google login error {
    "error": "Error message here",
    "trace": "..."
}
```

---

## Security Considerations

1. **Token Verification:** Always verifies token with Google's API before processing
2. **Email Verification:** Ensures email in request matches email in verified token
3. **Client ID Verification:** Prevents tokens from other applications
4. **Email Restriction:** Optional single email restriction for testing/security

---

## Additional Resources

- **Laravel Logs:** `storage/logs/laravel.log`
- **Google Token Info API:** https://oauth2.googleapis.com/tokeninfo
- **JWT Decoder:** https://jwt.io
- **Google Cloud Console:** https://console.cloud.google.com/apis/credentials
- **Controller File:** `app/Http/Controllers/Auth/LoginController.php` (line 210)

---

## Support

If issues persist after following this documentation:

1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify `.env` configuration
3. Ensure server is restarted after `.env` changes
4. Verify token's client ID matches configuration
5. Test token verification manually with cURL

---

**Last Updated:** 2024-12-19

