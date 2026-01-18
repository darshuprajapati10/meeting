# Email Configuration Guide

## Forgot Password API - Email Setup

Forgot Password API में code email में भेजने के लिए SMTP configuration करनी होगी।

---

## Quick Setup

### Step 1: `.env` File में Email Settings Add करें

`.env` file में ये settings add करें:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 2: Configuration Clear करें

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 3: Test करें

API call करें और email check करें।

---

## Gmail Setup (Recommended for Testing)

### Gmail App Password बनाना:

1. Google Account में जाएं: https://myaccount.google.com/
2. Security section में जाएं
3. "2-Step Verification" enable करें (अगर नहीं है)
4. "App passwords" में जाएं
5. "Select app" → "Mail" choose करें
6. "Select device" → "Other (Custom name)" → "Laravel" type करें
7. "Generate" button click करें
8. 16-digit password copy करें

### `.env` में Gmail Settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-digit-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App Name"
```

---

## Mailtrap Setup (Development/Testing)

Mailtrap एक testing email service है जो emails को actual में send नहीं करती, बस capture करती है।

### Step 1: Mailtrap Account बनाएं
- https://mailtrap.io/ पर sign up करें
- Free plan available है

### Step 2: SMTP Settings Copy करें
- Mailtrap dashboard में "Inboxes" → "SMTP Settings" में जाएं
- Settings copy करें

### Step 3: `.env` में Add करें

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Other Email Services

### SendGrid

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-verified-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Mailgun

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-verified-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Testing Email Sending

### Method 1: API Test

Postman में:
- **POST** `/api/auth/forgot-password`
- **Body:** `{"email": "your-email@example.com"}`
- Email inbox check करें

### Method 2: Laravel Tinker

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email', function ($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

### Method 3: Check Logs

```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 50

# Check for email logs
Get-Content storage/logs/laravel.log | Select-String -Pattern "Password reset code"
```

---

## Troubleshooting

### Problem: Email नहीं जा रहा

**Solution 1:** Mail driver check करें
```bash
php artisan tinker --execute="echo config('mail.default');"
```
Should be `smtp`, not `log`

**Solution 2:** Configuration clear करें
```bash
php artisan config:clear
php artisan cache:clear
```

**Solution 3:** Logs check करें
```bash
Get-Content storage/logs/laravel.log -Tail 100
```

### Problem: Gmail "Less secure app" error

**Solution:** Gmail App Password use करें (regular password नहीं)

### Problem: Connection timeout

**Solution:** 
- Firewall check करें
- Port 587 (TLS) या 465 (SSL) allow करें
- VPN disable करें

---

## Current Mail Driver Check

```bash
php artisan tinker --execute="echo 'Mail Driver: ' . config('mail.default');"
```

अगर `log` दिख रहा है, तो `.env` में `MAIL_MAILER=smtp` set करें।

---

## Important Notes

1. **Development में:** Mail driver 'log' है तो emails log file में जाएंगे
2. **Production में:** SMTP properly configure करें
3. **Gmail:** App Password use करें, regular password नहीं
4. **Security:** `.env` file को git में commit न करें

---

## Quick Test Command

```bash
# Check current mail configuration
php artisan tinker --execute="echo 'MAIL_MAILER: ' . env('MAIL_MAILER', 'not set') . PHP_EOL; echo 'MAIL_HOST: ' . env('MAIL_HOST', 'not set');"
```

---

**Last Updated:** 2024-12-19

