# Email Testing Guide - ZeptoMail Integration

## 📧 Email Testing Kaise Kare

### Method 1: Artisan Command Se Test Kare (Recommended)

#### Step 1: Environment Variables Check Kare
`.env` file me ye variables set kare:

```env
MAIL_MAILER=zeptomail
ZEPTO_MAIL_API_KEY=PHtE6r1bRbjtjTUs9hQDt/a5EcegMt97+b5jeAJFsd5BD6IAGk0D/toswWW/+Up5UfgUQv7PmY5r4rqYs+zTLG+5Z29LXWqyqK3sx/VYSPOZsbq6x00ftF8ac0TaVYHtcdZu3CTVvtjdNA==
ZEPTO_MAIL_BOUNCE_ADDRESS=bounce@ongoingforge.zeptomail.in
ZEPTO_MAIL_FROM_ADDRESS=noreply@ongoingforge.com
ZEPTO_MAIL_FROM_NAME="Ongoing Forge"
APP_URL=http://localhost:8000
```

#### Step 2: Migration Run Kare
```bash
php artisan migrate
```

#### Step 3: Test Command Run Kare
```bash
# Email address specify kare
php artisan test:zeptomail your-email@example.com

# Ya email address prompt hoga
php artisan test:zeptomail
```

Ye command:
- ✅ Configuration check karega
- ✅ Simple test email send karega
- ✅ Verification email send karega
- ✅ Verification URL bhi show karega

---

### Method 2: API Endpoint Se Test Kare

#### Test 1: Simple Email Send Kare

**Request:**
```bash
POST http://localhost:8000/api/test/email
Content-Type: application/json

{
    "email": "your-email@example.com"
}
```

**cURL Command:**
```bash
curl -X POST http://localhost:8000/api/test/email \
  -H "Content-Type: application/json" \
  -d '{"email":"your-email@example.com"}'
```

**Postman:**
- Method: `POST`
- URL: `http://localhost:8000/api/test/email`
- Body (raw JSON):
```json
{
    "email": "your-email@example.com"
}
```

#### Test 2: Verification Email Send Kare

**Request:**
```bash
POST http://localhost:8000/api/test/verification-email
Content-Type: application/json

{
    "email": "your-email@example.com"
}
```

**cURL Command:**
```bash
curl -X POST http://localhost:8000/api/test/verification-email \
  -H "Content-Type: application/json" \
  -d '{"email":"your-email@example.com"}'
```

**Response:**
```json
{
    "success": true,
    "message": "Verification email sent successfully! Check your inbox.",
    "data": {
        "verification_url": "http://localhost:8000/api/email/verify/abc123...",
        "token": "abc123..."
    }
}
```

---

### Method 3: Full Registration Flow Test Kare

#### Step 1: User Register Kare
```bash
POST http://localhost:8000/api/register
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Expected Response:**
- ✅ User create hoga
- ✅ Verification email automatically send hoga
- ✅ Token nahi milega (kyunki email verify nahi hua)

#### Step 2: Email Check Kare
- Inbox me verification email check kare
- Email me verification link click kare
- Ya verification URL manually browser me open kare

#### Step 3: Email Verify Kare
```bash
GET http://localhost:8000/api/email/verify/{token}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Email verified successfully! You can now login to your account.",
    "data": {
        "user": {
            "id": 1,
            "email": "test@example.com",
            "email_verified_at": "2026-01-15T10:30:00.000000Z"
        }
    }
}
```

#### Step 4: Login Kare
```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
    "email": "test@example.com",
    "password": "password123"
}
```

**Expected Response:**
- ✅ Login successful hoga
- ✅ Token milega
- ✅ User data milega

---

### Method 4: Resend Verification Email Test Kare

Agar email nahi aaya, to resend kar sakte hain:

```bash
POST http://localhost:8000/api/email/verify/resend
Content-Type: application/json

{
    "email": "test@example.com"
}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Verification email has been sent. Please check your inbox."
}
```

**Note:** Rate limiting hai - 3 requests per hour per email

---

## 🔍 Troubleshooting

### Problem 1: Email Send Nahi Ho Raha

**Check Kare:**
1. ✅ `.env` file me `ZEPTO_MAIL_API_KEY` set hai?
2. ✅ ZeptoMail dashboard me domain verified hai?
3. ✅ `APP_URL` correct hai?
4. ✅ Logs check kare: `storage/logs/laravel.log`

**Logs Check Kare:**
```bash
tail -f storage/logs/laravel.log
```

### Problem 2: "Invalid or expired verification token"

**Reasons:**
- Token 24 hours se purana hai
- Token already use ho chuka hai
- Token invalid hai

**Solution:**
- Resend verification email kare

### Problem 3: Login Blocked - "Please verify your email"

**Solution:**
- Email verify kare
- Verification link click kare
- Phir login try kare

### Problem 4: ZeptoMail API Error

**Common Errors:**
- `401 Unauthorized` - API key invalid hai
- `400 Bad Request` - Email format invalid hai
- `403 Forbidden` - Domain not verified hai

**Solution:**
- ZeptoMail dashboard me domain verify kare
- API key check kare
- Bounce address configure kare

---

## 📝 Testing Checklist

- [ ] Environment variables set kiye
- [ ] Migration run ki
- [ ] Test command se email send hua
- [ ] Email inbox me aaya
- [ ] Verification email properly formatted hai
- [ ] Verification link click karke verify hua
- [ ] Login blocked hai jab email verify nahi hai
- [ ] Login successful hai jab email verify hai
- [ ] Resend verification email kaam kar raha hai
- [ ] Rate limiting properly kaam kar raha hai

---

## 🚀 Production Deployment

Production me deploy karne se pehle:

1. ✅ Test routes remove kare (ya environment check kare)
2. ✅ `APP_URL` production URL set kare
3. ✅ ZeptoMail domain verify kare
4. ✅ Bounce address configure kare
5. ✅ Logs monitor kare

**Test routes automatically disable ho jayengi agar:**
- `APP_DEBUG=false` hai
- Environment `production` hai

---

## 📞 Support

Agar koi problem hai:
1. Logs check kare: `storage/logs/laravel.log`
2. ZeptoMail dashboard check kare
3. API responses check kare
4. Error messages read kare

---

## Example Test Flow

```bash
# 1. Test email send kare
php artisan test:zeptomail test@example.com

# 2. Register new user
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# 3. Email inbox check kare aur verification link copy kare

# 4. Verification link open kare (browser me)
# Ya API se verify kare:
curl http://localhost:8000/api/email/verify/{token}

# 5. Login kare
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

---

**Happy Testing! 🎉**
