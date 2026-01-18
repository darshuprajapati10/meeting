# Backend Notification Setup Documentation

## Overview

This document provides complete setup instructions for Firebase Push Notifications on the backend (Laravel).

## Prerequisites

1. *Laravel 11.x* installed
2. *PHP 8.2+* installed
3. *Composer* installed
4. *Firebase Project* created
5. *Service Account Key* downloaded from Firebase Console

---

## Step 1: Install Dependencies

```bash
cd api
composer require kreait/firebase-php
```

---

## Step 2: Firebase Service Account Setup

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project: **ongoingmeet**
3. Go to **Project Settings** → **Service Accounts**
4. Click **"Generate new private key"**
5. Download the JSON file
6. Save it to: `storage/app/firebase/service-account.json`

**Important**: 
- Never commit this file to Git
- Add to `.gitignore`: `/storage/app/firebase/*.json`

---

## Step 3: Environment Configuration

Edit `api/.env` file:

```env
# Firebase Configuration
FIREBASE_PROJECT_ID=ongoingmeet
FIREBASE_CREDENTIALS_PATH=storage/app/firebase/service-account.json

# Queue Configuration (REQUIRED for notifications)
QUEUE_CONNECTION=database
```

---

## Step 4: Firebase Config File

The config file already exists at `config/firebase.php`:

```php
<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/service-account.json')),
    'project_id' => env('FIREBASE_PROJECT_ID'),
];
```

---

## Step 5: Database Migrations

Run migrations to create required tables:

```bash
cd api
php artisan migrate
```

This creates:
- `fcm_tokens` - Stores FCM device tokens
- `meeting_fcm_notifications` - Tracks FCM notification status
- `jobs` - Queue jobs table (if not exists)
- `failed_jobs` - Failed queue jobs (if not exists)

**Note**: The `meeting_notifications` table already exists for notification rules (minutes, unit, trigger). The `meeting_fcm_notifications` table is separate and tracks FCM push notification delivery.

---

## Step 6: Queue Setup (CRITICAL)

**Notifications will NOT work without the queue worker running!**

### Development

Start the queue worker:

```bash
cd api
php artisan queue:work
```

**Keep this terminal open. The worker must run continuously.**

### Production

Use Supervisor to keep the queue worker running:

1. Install Supervisor:

```bash
sudo apt-get install supervisor  # Ubuntu/Debian
```

2. Create config file: `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-api/storage/logs/worker.log
stopwaitsecs=3600
```

3. Update Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## Step 7: Scheduled Reminders (Optional)

For reminder notifications, set up Laravel Scheduler:

1. Add to server crontab:

```bash
* * * * * cd /path-to-api && php artisan schedule:run >> /dev/null 2>&1
```

2. The command is already registered in `routes/console.php`:

```php
Schedule::command('meetings:send-reminders')->everyMinute()->withoutOverlapping();
```

---

## Step 8: Verify Setup

### Check Service Account Key

```bash
ls -la api/storage/app/firebase/service-account.json
```

Should show the file exists.

### Check Environment Variables

```bash
cd api
php artisan tinker
```

```php
>>> config('firebase.project_id')
=> "ongoingmeet"

>>> config('firebase.credentials')
=> "/path/to/storage/app/firebase/service-account.json"
```

### Test FCM Service

```bash
cd api
php artisan tinker
```

```php
$fcmService = app(\App\Services\FcmService::class);
$result = $fcmService->sendToUser(1, 'Test', 'Test notification', ['type' => 'test']);
var_dump($result);
```

**Note**: This will only work if:
- User ID 1 has registered FCM tokens
- Firebase credentials are configured correctly
- Queue worker is running

### Check Queue

```bash
# Check if jobs table exists
php artisan migrate:status

# Check queue connection
php artisan queue:monitor
```

---

## API Endpoints

### 1. Register FCM Token

**Endpoint**: `POST /api/fcm/register`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body**:
```json
{
    "token": "fcm_device_token_here",
    "platform": "android|ios|web",
    "device_id": "optional_device_id"
}
```

**Response**:
```json
{
    "success": true,
    "message": "FCM token registered successfully"
}
```

---

### 2. Unregister FCM Token

**Endpoint**: `POST /api/fcm/unregister`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body**:
```json
{
    "token": "fcm_device_token_here"
}
```

**Response**:
```json
{
    "success": true,
    "message": "FCM token unregistered successfully"
}
```

---

### 3. Save Meeting (Triggers Notification)

**Endpoint**: `POST /api/meeting/save`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body**:
```json
{
    "meeting_title": "Team Meeting",
    "date": "2025-01-15",
    "time": "14:00:00",
    "duration": 60,
    "attendees": [1, 2, 3],
    "status": "Created",
    "meeting_type": "Video Call",
    "custom_location": "Zoom",
    "agenda_notes": "Discuss project progress"
}
```

**Response**:
```json
{
    "data": {
        "id": 123,
        "meeting_title": "Team Meeting",
        ...
    },
    "message": "Meeting created successfully."
}
```

**Notification**: Automatically sent to meeting creator (user who created the meeting).

**Note**: Notifications are sent to the meeting creator (`created_by` user), not to contact attendees. Contacts are stored in `meeting_attendees` table but notifications go to the user who created the meeting.

---

## Notification Flow

1. **Meeting Created**:
   - Frontend calls `POST /api/meeting/save`
   - Backend saves meeting
   - Backend creates notification record in `meeting_fcm_notifications` table
   - Backend dispatches `SendMeetingNotificationJob` for meeting creator
   - Queue worker processes job
   - FCM service sends notification
   - Status updated in `meeting_fcm_notifications` table

2. **Meeting Updated**:
   - Same flow, notification type: `updated`

3. **Meeting Cancelled**:
   - Same flow, notification type: `cancelled`
   - Notification sent BEFORE meeting is deleted

4. **Reminder**:
   - Scheduled command runs every minute
   - Checks for pending reminders in `meeting_fcm_notifications` table
   - Dispatches notification jobs
   - Queue worker sends notifications

5. **Starting Soon**:
   - Scheduled command checks meetings starting in next 5 minutes
   - Creates notification record
   - Dispatches notification job
   - Queue worker sends notification

---

## Database Tables

### fcm_tokens

Stores FCM device tokens for each user:

```sql
CREATE TABLE fcm_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token TEXT NOT NULL,
    platform ENUM('ios', 'android', 'web') NOT NULL DEFAULT 'android',
    device_id VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_token (user_id, token),
    INDEX idx_user_id (user_id),
    INDEX idx_user_platform (user_id, platform),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### meeting_fcm_notifications

Tracks FCM notification delivery status:

```sql
CREATE TABLE meeting_fcm_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    meeting_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type ENUM('created', 'reminder', 'starting', 'updated', 'cancelled') NOT NULL,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_meeting_user (meeting_id, user_id),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_status (status),
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Note**: This is different from `meeting_notifications` table which stores notification rules (when to send reminders).

### jobs

Queue jobs table (created by Laravel):

```sql
CREATE TABLE jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_queue (queue)
);
```

---

## Troubleshooting

### Issue: "Firebase messaging not initialized"

**Solution**:
1. Check service account key exists: `ls -la storage/app/firebase/service-account.json`
2. Verify file path in `.env`: `FIREBASE_CREDENTIALS_PATH`
3. Check file permissions: `chmod 644 storage/app/firebase/service-account.json`
4. Check logs: `tail -f storage/logs/laravel.log`
5. Verify project ID in `.env`: `FIREBASE_PROJECT_ID`

### Issue: "No FCM tokens found for user"

**Solution**:
1. Verify user has registered FCM token via `/api/fcm/register`
2. Check `fcm_tokens` table: `SELECT * FROM fcm_tokens WHERE user_id = ?`
3. Ensure frontend is calling `/api/fcm/register` after login
4. Verify token format (should be > 100 characters)

### Issue: "Notifications queued but not sent"

**Solution**:
1. **Start queue worker**: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Retry failed jobs: `php artisan queue:retry all`
4. Check logs: `tail -f storage/logs/laravel.log`
5. Verify queue connection in `.env`: `QUEUE_CONNECTION=database`

### Issue: "Queue connection not configured"

**Solution**:
1. Set in `.env`: `QUEUE_CONNECTION=database`
2. Run migrations: `php artisan migrate`
3. Start queue worker: `php artisan queue:work`
4. Verify jobs table exists: `php artisan migrate:status`

### Issue: "FCM tokens table does not exist"

**Solution**:
1. Run migrations: `php artisan migrate`
2. Check migration status: `php artisan migrate:status`
3. Verify migration file exists: `database/migrations/*_create_fcm_tokens_table.php`

### Issue: "meeting_fcm_notifications table does not exist"

**Solution**:
1. Run migrations: `php artisan migrate`
2. Check migration file: `database/migrations/*_create_meeting_fcm_notifications_table.php`
3. Verify table exists: `php artisan tinker` → `DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')`

---

## Monitoring

### Check Notification Status

```sql
-- Pending notifications
SELECT * FROM meeting_fcm_notifications 
WHERE status = 'pending' 
ORDER BY created_at DESC;

-- Failed notifications
SELECT * FROM meeting_fcm_notifications 
WHERE status = 'failed' 
ORDER BY created_at DESC;

-- Recent notifications
SELECT * FROM meeting_fcm_notifications 
ORDER BY created_at DESC 
LIMIT 20;

-- Notifications by type
SELECT notification_type, status, COUNT(*) as count
FROM meeting_fcm_notifications
GROUP BY notification_type, status;
```

### Check Queue Jobs

```sql
-- Pending jobs
SELECT * FROM jobs 
WHERE queue = 'default' 
ORDER BY created_at DESC 
LIMIT 20;

-- Failed jobs
SELECT * FROM failed_jobs 
ORDER BY failed_at DESC 
LIMIT 20;
```

### Check FCM Tokens

```sql
-- Registered tokens by user
SELECT user_id, platform, COUNT(*) as token_count
FROM fcm_tokens
GROUP BY user_id, platform;

-- All tokens for a user
SELECT * FROM fcm_tokens WHERE user_id = 1;
```

### Check Logs

```bash
# Backend logs
tail -f api/storage/logs/laravel.log

# Queue worker logs (if using Supervisor)
tail -f api/storage/logs/worker.log

# Filter notification logs
tail -f api/storage/logs/laravel.log | grep -i "notification"
```

---

## Production Checklist

- [ ] Service account key uploaded to server
- [ ] Environment variables set in `.env`
- [ ] Queue worker running (Supervisor configured)
- [ ] Laravel scheduler configured (crontab)
- [ ] Database migrations run
- [ ] Logs directory writable
- [ ] Firebase project ID correct
- [ ] Queue connection set to `database` or `redis`
- [ ] Failed jobs monitoring set up
- [ ] FCM tokens table exists
- [ ] meeting_fcm_notifications table exists
- [ ] Jobs table exists

---

## Testing

### Test Token Registration

```bash
# Using curl
curl -X POST http://localhost:8000/api/fcm/register \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "test_fcm_token_here",
    "platform": "android",
    "device_id": "test_device_123"
  }'
```

### Test Notification Sending

1. Register FCM token for a user
2. Create a meeting via `/api/meeting/save`
3. Check `meeting_fcm_notifications` table for notification record
4. Check `jobs` table for queued job
5. Verify queue worker processes the job
6. Check notification status in `meeting_fcm_notifications` table

### Test Scheduled Reminders

```bash
# Run reminder command manually
php artisan meetings:send-reminders

# Check logs
tail -f storage/logs/laravel.log
```

---

## Important Notes

1. **Notifications are sent to meeting creator**, not to contact attendees. The `meeting_attendees` table stores contacts, but notifications go to the user who created the meeting.

2. **Queue worker must be running** for notifications to be sent. Without the queue worker, jobs will be queued but not processed.

3. **Two notification tables exist**:
   - `meeting_notifications`: Stores notification rules (when to send reminders)
   - `meeting_fcm_notifications`: Tracks FCM push notification delivery

4. **Reminder notifications** are scheduled 1 hour before meeting time when a meeting is created.

5. **Starting soon notifications** are sent 5 minutes before meeting time via scheduled command.

---

## Support

For issues:

1. Check logs: `api/storage/logs/laravel.log`
2. Check queue: `php artisan queue:failed`
3. Verify setup: Follow verification steps above
4. Test FCM service: Use tinker commands
5. Check database: Verify tables exist and have data

---

## Files Reference

- **Service**: `app/Services/FcmService.php`
- **Controller**: `app/Http/Controllers/Api/FcmTokenController.php`
- **Controller**: `app/Http/Controllers/Api/MeetingController.php`
- **Job**: `app/Jobs/SendMeetingNotificationJob.php`
- **Command**: `app/Console/Commands/SendMeetingReminders.php`
- **Config**: `config/firebase.php`
- **Routes**: `routes/api.php`
- **Scheduler**: `routes/console.php`
- **Migrations**: 
  - `database/migrations/*_create_fcm_tokens_table.php`
  - `database/migrations/*_create_meeting_fcm_notifications_table.php`

---

**Last Updated**: November 22, 2025


