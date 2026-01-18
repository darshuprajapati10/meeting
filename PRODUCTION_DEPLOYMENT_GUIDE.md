# Production API Deployment Guide

## Complete Checklist for Production Deployment

This guide covers all steps needed to deploy APIs to production when they work locally but fail in production.

---

## Pre-Deployment Checklist

### 1. Environment Variables (.env)

**CRITICAL:** Ensure all these variables are set in production `.env`:

```env
# Application
APP_NAME="Yujix"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yujix.com
APP_KEY=base64:your-app-key-here

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_database
DB_USERNAME=your_production_username
DB_PASSWORD=your_production_password

# Razorpay (MUST be Live Keys for Production)
RAZORPAY_KEY=rzp_live_RvQYcUKU31Ng7x
RAZORPAY_SECRET=your_live_secret_here
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here

# Timezone
APP_TIMEZONE=Asia/Kolkata

# Queue (Required for notifications)
QUEUE_CONNECTION=database

# Firebase (If using notifications)
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_CREDENTIALS_PATH=storage/app/firebase/service-account.json

# Mail (If using email features)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Deployment Steps

### Step 1: Upload Code to Production

```bash
# On your local machine or CI/CD
# Upload all files except:
# - .env (create new on server)
# - node_modules/
# - vendor/ (or run composer install on server)
# - storage/logs/*.log
# - .git/
```

### Step 2: SSH into Production Server

```bash
ssh user@your-production-server
cd /path/to/your/laravel/project
```

### Step 3: Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# If using npm for frontend
npm install --production
npm run build
```

### Step 4: Set Up Environment File

```bash
# Copy .env.example to .env (if exists)
cp .env.example .env

# Or create new .env file
nano .env

# Add all required variables (see Pre-Deployment Checklist)
# Save and exit (Ctrl+X, then Y, then Enter)
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

### Step 6: Set File Permissions

```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
chown -R www-data:www-data storage bootstrap/cache  # Adjust user/group as needed

# Secure .env file
chmod 600 .env
```

### Step 7: Run Database Migrations

```bash
# Check migration status
php artisan migrate:status

# Run all pending migrations
php artisan migrate --force

# Run seeders (CRITICAL for subscription system)
php artisan db:seed --class=SubscriptionPlanSeeder --force
```

### Step 8: Clear All Caches

```bash
# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# If using OPcache, restart PHP-FPM
sudo service php8.2-fpm restart  # Adjust version as needed
```

### Step 9: Verify Database Schema

Check critical tables and fix enum mismatches:

```sql
-- Connect to database
mysql -u your_username -p your_database

-- Check meetings table status enum
SHOW COLUMNS FROM meetings LIKE 'status';

-- If enum doesn't include 'Pending' and 'Rescheduled', fix it:
ALTER TABLE meetings MODIFY COLUMN status ENUM('Created', 'Scheduled', 'Completed', 'Cancelled', 'Pending', 'Rescheduled') DEFAULT 'Created';

-- Verify subscription_plans table has data
SELECT COUNT(*) FROM subscription_plans;
-- Should return at least 2

-- Check all required tables exist
SHOW TABLES;
```

### Step 10: Run Diagnostic Script

```bash
# Make script executable
chmod +x check_production.sh

# Run diagnostic
./check_production.sh
```

Fix any errors shown by the diagnostic script.

---

## Common Production Issues & Fixes

### Issue 1: "Config cache not cleared"

**Symptoms:** Changes to `.env` not taking effect

**Fix:**
```bash
php artisan config:clear
php artisan cache:clear
# Restart PHP-FPM if using
sudo service php8.2-fpm restart
```

### Issue 2: "Database migrations not run"

**Symptoms:** Missing tables, column errors

**Fix:**
```bash
php artisan migrate:status  # Check status
php artisan migrate --force  # Run migrations
```

### Issue 3: "Subscription plans missing"

**Symptoms:** Subscription APIs return errors

**Fix:**
```bash
php artisan db:seed --class=SubscriptionPlanSeeder --force
```

### Issue 4: "Razorpay keys missing"

**Symptoms:** Payment/subscription APIs fail

**Fix:**
1. Add Razorpay keys to `.env`
2. Run: `php artisan config:clear`
3. Verify: `php artisan tinker` → `config('services.razorpay.key')`

### Issue 5: "Database enum mismatch"

**Symptoms:** Meeting save fails with status validation error

**Fix:**
```sql
ALTER TABLE meetings MODIFY COLUMN status ENUM('Created', 'Scheduled', 'Completed', 'Cancelled', 'Pending', 'Rescheduled') DEFAULT 'Created';
```

### Issue 6: "File permissions"

**Symptoms:** Can't write logs, cache errors

**Fix:**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue 7: "Routes not found"

**Symptoms:** 404 errors on API endpoints

**Fix:**
```bash
php artisan route:clear
php artisan route:cache  # Only if routes are stable
```

### Issue 8: "Class not found errors"

**Symptoms:** Autoload errors

**Fix:**
```bash
composer dump-autoload --optimize
php artisan config:clear
```

---

## Testing APIs in Production

### Test Authentication

```bash
curl -X POST "https://yujix.com/api/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Test Subscription APIs

```bash
# Get plans
curl -X GET "https://yujix.com/api/subscription/plans" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get current subscription
curl -X GET "https://yujix.com/api/subscription/current" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get usage
curl -X GET "https://yujix.com/api/subscription/usage" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Test Meeting API

```bash
curl -X POST "https://yujix.com/api/meeting/save" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "meeting_title": "Test Meeting",
    "status": "Created",
    "date": "2025-01-20",
    "time": "14:30",
    "duration": 30,
    "meeting_type": "Video Call",
    "attendees": []
  }'
```

### Test Survey API

```bash
curl -X POST "https://yujix.com/api/survey/save" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "survey_name": "Test Survey",
    "status": "Draft",
    "survey_steps": []
  }'
```

---

## Monitoring & Debugging

### Check Laravel Logs

```bash
# View recent logs
tail -n 100 storage/logs/laravel.log

# Watch logs in real-time
tail -f storage/logs/laravel.log

# Search for errors
grep -i "error\|exception\|failed" storage/logs/laravel.log | tail -50
```

### Enable Debug Mode (Temporary)

**WARNING:** Only for debugging, disable after fixing issues!

```env
APP_DEBUG=true
```

Then:
```bash
php artisan config:clear
```

Test APIs and check actual error messages. **Remember to set back to `false` after debugging!**

### Check Queue Status (if using notifications)

```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Check pending jobs
php artisan tinker
>>> DB::table('jobs')->count();

# Check failed jobs
>>> DB::table('failed_jobs')->count();
```

---

## Post-Deployment Verification

### ✅ Checklist

- [ ] All environment variables set in `.env`
- [ ] Database migrations run successfully
- [ ] Subscription plans seeded
- [ ] All caches cleared
- [ ] File permissions set correctly
- [ ] Razorpay keys configured (Live keys)
- [ ] Database enum values match validation
- [ ] Laravel logs show no critical errors
- [ ] Test authentication endpoint works
- [ ] Test subscription endpoints work
- [ ] Test meeting save endpoint works
- [ ] Test survey save endpoint works
- [ ] Queue worker running (if using notifications)
- [ ] Scheduler configured (if using scheduled tasks)

---

## Quick Fix Commands

Run these commands in order if APIs are not working:

```bash
# 1. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 2. Run migrations
php artisan migrate --force

# 3. Seed subscription plans
php artisan db:seed --class=SubscriptionPlanSeeder --force

# 4. Fix permissions
chmod -R 755 storage bootstrap/cache

# 5. Restart PHP-FPM (if using)
sudo service php8.2-fpm restart

# 6. Check logs
tail -f storage/logs/laravel.log
```

---

## Emergency Rollback

If production breaks after deployment:

```bash
# 1. Restore previous .env backup
cp .env.backup .env

# 2. Clear caches
php artisan config:clear
php artisan cache:clear

# 3. Restart services
sudo service php8.2-fpm restart
sudo service nginx restart  # or apache2
```

---

## Support

If issues persist:

1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Enable debug mode temporarily: `APP_DEBUG=true` in `.env`
3. Run diagnostic script: `./check_production.sh`
4. Check database: `php artisan migrate:status`
5. Verify environment: `php artisan tinker` → `config('app.env')`

---

**Last Updated:** 2025-01-20
**Production URL:** https://yujix.com

