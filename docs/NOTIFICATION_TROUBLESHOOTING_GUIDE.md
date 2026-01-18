# Notification System Troubleshooting Guide - Backend Developer

## Overview

This document outlines common issues with the Firebase Cloud Messaging (FCM) notification system and how to fix them.

## Critical Issues to Check

### 1. Queue Worker Must Be Running

*Problem*: Notifications are queued but never sent because the queue worker is not running.

*Solution*: 
```bash
# Start queue worker (must run continuously)
php artisan queue:work

# Or use supervisor/systemd for production
# See Laravel documentation for production queue setup
```

*How to Verify*:
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Check jobs table for pending jobs
php artisan tinker
>>> DB::table('jobs')->count();
```

*Expected Behavior*:
- Jobs should be processed within seconds
- jobs table should be empty or have very few pending jobs
- Check failed_jobs table for errors

---

### 2. Scheduler Must Be Running

*Problem*: Reminder and "starting soon" notifications are not sent because the scheduler is not running.

*Solution*:
```bash
# Development: Run scheduler manually
php artisan schedule:work

# Production: Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

*How to Verify*:
```bash
# Check if scheduler command exists
php artisan list | grep meetings:send-reminders

# Test the command manually
php artisan meetings:send-reminders

# Check Laravel logs for scheduler execution
tail -f storage/logs/laravel.log | grep "Reminder notifications processed"
```

*Expected Behavior*:
- Command runs every minute
- Logs show "Reminder notifications processed"
- meeting_fcm_notifications table shows scheduled reminders

---

### 3. Firebase Credentials Configuration

*Problem*: FCM service fails to initialize because Firebase credentials are missing or incorrect.

*Check Configuration*:

1. **Verify .env file**:
```env
FIREBASE_PROJECT_ID=your_actual_project_id_here
FIREBASE_CREDENTIALS_PATH=storage/app/firebase/service-account.json

# Note: Replace 'your_actual_project_id_here' with your actual Firebase project ID
```

2. **Verify service account file exists**:
```bash
ls -la storage/app/firebase/service-account.json
```

3. **Verify file permissions**:
```bash
chmod 644 storage/app/firebase/service-account.json
```

4. **Verify JSON file is valid**:
```bash
php -r "json_decode(file_get_contents('storage/app/firebase/service-account.json')); echo json_last_error() === JSON_ERROR_NONE ? 'Valid JSON' : 'Invalid JSON';"
```

*How to Fix*:
1. Download service account key from Firebase Console
2. Save to storage/app/firebase/service-account.json
3. Ensure .env has correct path
4. Clear config cache: `php artisan config:clear`

---

### 4. FCM Token Validation Issue

*Problem*: Web FCM tokens might be rejected because validation is too strict.

*Current Validation* (in FcmService.php):
```php
public function validateToken(string $token): bool
{
    // FCM tokens are typically 152+ characters long
    // Web tokens might not have ':' but are still valid
    // Android/iOS tokens often have ':' but web tokens might not
    // We accept if length is sufficient (minimum 100 characters)
    if (strlen($token) < 100) {
        return false;
    }
    
    return !empty($token);
}
```

*Note*: This validation is sufficient and works for all platforms (iOS, Android, Web). FCM tokens are typically 152+ characters long. The current implementation correctly validates token length without requiring ':' character, making it compatible with web tokens.

*Location*: `app/Services/FcmService.php` line 146-158

---

### 5. Database Tables Must Exist

*Problem*: Notifications fail because required tables don't exist.

*Required Tables*:
1. `fcm_tokens` - Stores FCM device tokens
2. `meeting_fcm_notifications` - Tracks FCM notification status (NOT `meeting_notifications`)
3. `meeting_attendees` - Links users to meetings
4. `jobs` - Laravel queue jobs
5. `failed_jobs` - Failed queue jobs

**Note**: There are two different tables:
- `meeting_notifications` - Stores notification rules/settings (minutes, unit, trigger, is_enabled)
- `meeting_fcm_notifications` - Tracks FCM notification delivery status (notification_type, status, sent_at, error_message)

*How to Verify*:
```bash
php artisan tinker
>>> Schema::hasTable('fcm_tokens');
>>> Schema::hasTable('meeting_fcm_notifications');
>>> Schema::hasTable('meeting_attendees');
>>> Schema::hasTable('jobs');
```

*How to Fix*:
```bash
# Run migrations
php artisan migrate

# Check migration status
php artisan migrate:status
```

---

### 6. Check Notification Sending Logic

*Problem*: Notifications are not being sent to users.

*Debug Steps*:

1. **Check if tokens exist for user**:
```php
// In tinker or controller
$userId = 1; // Replace with actual user ID
$tokens = DB::table('fcm_tokens')
    ->where('user_id', $userId)
    ->get();
    
echo "Tokens for user $userId: " . $tokens->count();
```

2. **Check meeting attendees**:
```php
$meetingId = 1; // Replace with actual meeting ID
$attendees = DB::table('meeting_attendees')
    ->where('meeting_id', $meetingId)
    ->pluck('user_id')
    ->toArray();
    
echo "Attendees: " . implode(', ', $attendees);
```

3. **Check notification records**:
```php
$meetingId = 1; // Replace with actual meeting ID
$notifications = DB::table('meeting_fcm_notifications')
    ->where('meeting_id', $meetingId)
    ->get();
    
foreach ($notifications as $notif) {
    echo "Type: {$notif->notification_type}, Status: {$notif->status}\n";
}
```

4. **Test FCM service directly**:
```php
// In tinker
$fcmService = app(\App\Services\FcmService::class);
$token = "YOUR_FCM_TOKEN_HERE";
$result = $fcmService->sendNotification(
    $token,
    "Test Notification",
    "This is a test",
    ['type' => 'test']
);
var_dump($result);
```

---

### 7. Check Laravel Logs

*Problem*: Errors are occurring but not visible.

*How to Check*:
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Search for FCM errors
grep -i "fcm\|firebase\|notification" storage/logs/laravel.log

# Search for queue errors
grep -i "queue\|job" storage/logs/laravel.log
```

*Common Error Messages*:
- `Firebase credentials not configured` → Check .env and service account file
- `Firebase messaging not initialized` → Check Firebase initialization
- `No FCM tokens found for user` → User hasn't registered FCM token
- `FCM notification failed` → Check token validity and Firebase configuration

---

### 8. Queue Connection Configuration

*Problem*: Jobs are not being processed.

**Check .env**:
```env
QUEUE_CONNECTION=database
# Or for production:
# QUEUE_CONNECTION=redis
```

*Verify Queue Table*:
```bash
php artisan queue:table  # If table doesn't exist
php artisan migrate
```

*Test Queue*:
```bash
# Dispatch a test job
php artisan tinker
>>> dispatch(new \App\Jobs\SendMeetingNotificationJob(1, 1, 'test', 'Test', 'Test', []));
```

---

### 9. Scheduler Configuration

*Problem*: Scheduled reminders are not being sent.

**Check routes/console.php**:
```php
Schedule::command('meetings:send-reminders')
    ->everyMinute()
    ->withoutOverlapping();
```

*Verify Command Exists*:
```bash
php artisan list | grep meetings:send-reminders
```

*Test Command Manually*:
```bash
php artisan meetings:send-reminders
```

*Check Scheduler Logs*:
```bash
# Scheduler logs to Laravel log
tail -f storage/logs/laravel.log | grep "send-reminders"
```

---

### 10. CORS Configuration for FCM Token Registration

*Problem*: Frontend cannot register FCM tokens due to CORS errors.

**For Laravel 11**:

Check `bootstrap/app.php` and ensure CORS middleware is configured:

```php
// In bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

**Or use Laravel's built-in CORS**:

If using Laravel's default CORS handling, check `.env`:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,192.168.29.62
SESSION_DOMAIN=localhost
```

**Verify CORS Headers**:

Check if response includes:
- `Access-Control-Allow-Origin`
- `Access-Control-Allow-Methods`
- `Access-Control-Allow-Headers`

**Test CORS**:
```bash
# Use curl to test CORS headers
curl -H "Origin: http://localhost:3000" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type,Authorization" \
     -X OPTIONS \
     http://localhost:8000/api/fcm/register
```

---

## Testing Checklist

### Step 1: Verify Setup
- [ ] Firebase credentials file exists and is valid
- [ ] .env has correct Firebase configuration
- [ ] All database tables exist (fcm_tokens, meeting_fcm_notifications, meeting_attendees, jobs)
- [ ] Queue worker is running
- [ ] Scheduler is running (or cron is configured)

### Step 2: Test Token Registration
- [ ] User can register FCM token via API
- [ ] Token is saved in fcm_tokens table
- [ ] Token validation passes

### Step 3: Test Notification Sending
- [ ] Create a meeting
- [ ] Check meeting_fcm_notifications table for records (NOT meeting_notifications)
- [ ] Check jobs table for queued notifications
- [ ] Verify queue worker processes jobs
- [ ] Check Laravel logs for FCM send results

### Step 4: Test Scheduled Notifications
- [ ] Create meeting with reminder time in future
- [ ] Check meeting_fcm_notifications table for scheduled reminders
- [ ] Wait for reminder time
- [ ] Verify scheduler processes reminders
- [ ] Check notifications are sent

---

## Common Error Solutions

### Error: "Firebase credentials not configured"
*Solution*: 
1. Download service account key from Firebase Console
2. Save to storage/app/firebase/service-account.json
3. Update .env with correct path
4. Run `php artisan config:clear`

### Error: "No FCM tokens found for user"
*Solution*: 
1. Ensure user has logged in and registered FCM token
2. Check fcm_tokens table for user's tokens
3. Verify token registration API endpoint is working

### Error: "Queue connection [database] not configured"
*Solution*: 
1. Set `QUEUE_CONNECTION=database` in .env
2. Run `php artisan queue:table` and `php artisan migrate`
3. Start queue worker: `php artisan queue:work`

### Error: "Class 'App\Jobs\SendMeetingNotificationJob' not found"
*Solution*: 
1. Verify file exists: `app/Jobs/SendMeetingNotificationJob.php`
2. Run `composer dump-autoload`
3. Clear cache: `php artisan config:clear`

### Error: "meeting_fcm_notifications table does not exist"
*Solution*:
1. Run migrations: `php artisan migrate`
2. Check migration file exists: `database/migrations/2025_11_22_084645_create_meeting_fcm_notifications_table.php`
3. Verify migration ran: `php artisan migrate:status`

---

## Production Checklist

- [ ] Queue worker running via supervisor/systemd
- [ ] Scheduler configured in crontab
- [ ] Firebase credentials secured (not in git)
- [ ] Error logging configured
- [ ] Failed jobs monitoring set up
- [ ] CORS configured for production domain
- [ ] Database indexes optimized
- [ ] Queue connection using Redis (recommended for production)

---

## Database Table Reference

### fcm_tokens
Stores registered FCM device tokens for users.

**Columns**:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `token` - FCM device token (text)
- `platform` - Platform type (ios, android, web)
- `device_id` - Optional device identifier
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- Unique on `['user_id', 'token']`
- Index on `user_id`
- Index on `['user_id', 'platform']`

---

### meeting_fcm_notifications
Tracks FCM notification delivery status and history.

**Columns**:
- `id` - Primary key
- `meeting_id` - Foreign key to meetings table
- `user_id` - Foreign key to users table
- `notification_type` - Type (created, reminder, starting, updated, cancelled)
- `scheduled_at` - When reminder should be sent (nullable)
- `sent_at` - When notification was actually sent (nullable)
- `status` - Status (pending, sent, failed)
- `error_message` - Error details if failed (nullable)
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- Index on `['meeting_id', 'user_id']`
- Index on `scheduled_at`
- Index on `status`

**Note**: This is different from `meeting_notifications` table which stores notification rules/settings.

---

### meeting_notifications
Stores notification rules/settings for meetings (NOT for tracking FCM delivery).

**Columns**:
- `id` - Primary key
- `meeting_id` - Foreign key to meetings table
- `minutes` - Minutes before/after
- `unit` - Unit (minutes, hours, days)
- `trigger` - Trigger (before, after)
- `is_enabled` - Whether notification is enabled
- `created_at`, `updated_at` - Timestamps

**Note**: This table is for storing notification preferences, not for tracking FCM delivery status.

---

## Support

If issues persist after checking all above items:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Firebase Console for delivery reports
3. Verify FCM tokens are valid using Firebase Console
4. Test with a simple notification first
5. Check network connectivity to Firebase servers
6. Verify all migrations have run: `php artisan migrate:status`
7. Check queue worker is processing jobs: `php artisan queue:monitor`

---

## Quick Debug Commands

```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Check pending jobs
php artisan tinker
>>> DB::table('jobs')->count();

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Check FCM tokens for user
php artisan tinker
>>> DB::table('fcm_tokens')->where('user_id', 1)->get();

# Check notification records
php artisan tinker
>>> DB::table('meeting_fcm_notifications')->where('meeting_id', 1)->get();

# Test scheduler command
php artisan meetings:send-reminders

# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

*Last Updated*: 2025-11-22

