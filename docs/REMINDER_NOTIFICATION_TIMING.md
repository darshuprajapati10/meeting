# Backend Developer Guide: When Reminder Notifications Arrive

## 🎯 Overview

This document explains **exactly when** meeting reminder notifications are sent to users. It covers the calculation formula, implementation steps, and verification methods.

---

## ⏰ Quick Answer: When Do Notifications Arrive?

**Formula:**
```
Notification Time = Meeting Time - Notification Minutes
```

**Example:**
- Meeting at **5:20 PM** with **10-minute reminder**
- Notification arrives at **5:10 PM** (5:20 - 10 minutes)

**Important:** All times use **India timezone (Asia/Kolkata/IST)**.

---

## 📋 Table of Contents

1. [Notification Timing Formula](#notification-timing-formula)
2. [Real-World Examples](#real-world-examples)
3. [Implementation Steps](#implementation-steps)
4. [How the Scheduler Works](#how-the-scheduler-works)
5. [Database Schema](#database-schema)
6. [Verification & Testing](#verification--testing)
7. [Troubleshooting](#troubleshooting)

---

## ⏰ Notification Timing Formula

### Basic Formula

For notifications with `trigger: "before"`:
```
scheduled_at = meeting_datetime - notification_minutes
```

For notifications with `trigger: "after"`:
```
scheduled_at = meeting_datetime + notification_minutes
```

### PHP Implementation (Current Code)

```php
use Carbon\Carbon;

// Get meeting datetime (explicitly using India timezone)
$dateOnly = is_string($meetingDate) ? $meetingDate : $meetingDate->format('Y-m-d');
$meetingDateTime = Carbon::parse("{$dateOnly} {$meetingTime}", config('app.timezone'));
$meetingDateTime->setTimezone(config('app.timezone'));

// Calculate scheduled time based on unit
$reminderTime = $meetingDateTime->copy();

if ($setting->unit === 'days') {
    $reminderTime->subDays($setting->minutes);
} elseif ($setting->unit === 'hours') {
    $reminderTime->subHours($setting->minutes);
} else {
    $reminderTime->subMinutes($setting->minutes);
}
```

---

## 📊 Real-World Examples

### Example 1: Standard Meeting

**Meeting Details:**
- Date: `2025-12-23`
- Time: `14:30:00` (2:30 PM IST)
- Notifications:
  - 12 minutes before
  - 10 minutes before

**Calculated Schedule:**

| Notification | Minutes | Calculation | Scheduled At |
|--------------|---------|-------------|--------------|
| Notification 1 | 12 | 14:30 - 12 min | **14:18:00 IST** (2:18 PM) |
| Notification 2 | 10 | 14:30 - 10 min | **14:20:00 IST** (2:20 PM) |

**Timeline:**
```
14:18:00 IST → 🔔 Notification 1: "Meeting in 12 minutes"
14:20:00 IST → 🔔 Notification 2: "Meeting in 10 minutes"
14:30:00 IST → 📅 Meeting Starts
```

### Example 2: Evening Meeting

**Meeting Details:**
- Date: `2025-12-15`
- Time: `17:20:00` (5:20 PM IST)
- Notifications:
  - 20 minutes before
  - 10 minutes before

**Calculated Schedule:**

| Notification | Minutes | Calculation | Scheduled At |
|--------------|---------|-------------|--------------|
| Notification 1 | 20 | 17:20 - 20 min | **17:00:00 IST** (5:00 PM) |
| Notification 2 | 10 | 17:20 - 10 min | **17:10:00 IST** (5:10 PM) |

**Timeline:**
```
17:00:00 IST → 🔔 Notification 1: "Meeting in 20 minutes"
17:10:00 IST → 🔔 Notification 2: "Meeting in 10 minutes"
17:20:00 IST → 📅 Meeting Starts
```

### Example 3: Multiple Reminders with Different Units

**Meeting Details:**
- Date: `2025-12-25`
- Time: `09:00:00` (9:00 AM IST)
- Notifications:
  - 1 day before
  - 1 hour before
  - 5 minutes before

**Calculated Schedule:**

| Notification | Minutes | Unit | Calculation | Scheduled At |
|--------------|---------|------|-------------|--------------|
| Notification 1 | 1 | days | 09:00 (Dec 25) - 1 day | **08:00:00 IST Dec 24** (8:00 AM) |
| Notification 2 | 1 | hours | 09:00 - 1 hour | **08:00:00 IST Dec 25** (8:00 AM) |
| Notification 3 | 5 | minutes | 09:00 - 5 min | **08:55:00 IST Dec 25** (8:55 AM) |

**Timeline:**
```
Dec 24, 08:00:00 IST → 🔔 Notification 1: "Meeting in 1 day"
Dec 25, 08:00:00 IST → 🔔 Notification 2: "Meeting in 1 hour"
Dec 25, 08:55:00 IST → 🔔 Notification 3: "Meeting in 5 minutes"
Dec 25, 09:00:00 IST → 📅 Meeting Starts
```

---

## 💻 Implementation Steps

### Step 1: Meeting Creation/Update Flow

When a meeting is created or updated, notifications are automatically scheduled:

**Location:** `app/Http/Controllers/Api/MeetingController.php`

**Key Code:**
```php
// Parse meeting date and time - Explicitly use India timezone (Asia/Kolkata)
$dateOnly = is_string($meetingDate) ? $meetingDate : $meetingDate->format('Y-m-d');
$meetingDateTime = Carbon::parse("{$dateOnly} {$meetingTime}", config('app.timezone'));
$meetingDateTime->setTimezone(config('app.timezone'));

// Get notification settings for this meeting
$notificationSettings = MeetingNotification::where('meeting_id', $meetingId)
    ->where('is_enabled', true)
    ->where('trigger', 'before')
    ->get();

// If no settings found, use default (1 hour before)
if ($notificationSettings->isEmpty()) {
    $notificationSettings = collect([
        (object)[
            'minutes' => 1,
            'unit' => 'hours',
            'trigger' => 'before'
        ]
    ]);
}

// Calculate reminder times and schedule notifications for all users
foreach ($notificationSettings as $setting) {
    $reminderTime = $meetingDateTime->copy();
    
    // Calculate offset based on unit
    if ($setting->unit === 'days') {
        $reminderTime->subDays($setting->minutes);
    } elseif ($setting->unit === 'hours') {
        $reminderTime->subHours($setting->minutes);
    } else {
        $reminderTime->subMinutes($setting->minutes);
    }
    
    // Only schedule if reminder time is in the future
    if ($reminderTime->isFuture()) {
        // Schedule for all users (creator + attendees)
        foreach ($userIdsToNotify as $userId) {
            DB::table('meeting_fcm_notifications')->insert([
                'meeting_id' => $meetingId,
                'user_id' => $userId,
                'notification_type' => 'reminder',
                'scheduled_at' => $reminderTime,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
```

### Step 2: Scheduler Command (Already Implemented)

**Location:** `app/Console/Commands/SendMeetingReminders.php`

**Key Code:**
```php
public function handle(FcmService $fcmService)
{
    // Explicitly set timezone to avoid timezone mismatch issues
    $now = Carbon::now(config('app.timezone'));
    
    // Get meetings with reminder notifications scheduled
    $reminders = DB::table('meeting_fcm_notifications')
        ->join('meetings', 'meeting_fcm_notifications.meeting_id', '=', 'meetings.id')
        ->where('notification_type', 'reminder')
        ->where('meeting_fcm_notifications.status', 'pending')
        ->where('scheduled_at', '<=', $now->copy()->addMinute()) // 1 minute buffer
        ->where('meetings.status', '!=', 'Cancelled')
        ->select('meeting_fcm_notifications.*', 'meetings.meeting_title', 'meetings.date', 'meetings.time')
        ->get();
    
    foreach ($reminders as $reminder) {
        $scheduledAt = Carbon::parse($reminder->scheduled_at, config('app.timezone'));
        
        // Double check: Only send if scheduled_at has actually passed
        if ($scheduledAt->lte($now)) {
            $this->sendReminderNotification($reminder);
        }
    }
}
```

### Step 3: Queue Job (Already Implemented)

**Location:** `app/Jobs/SendMeetingNotificationJob.php`

This job handles the actual FCM notification sending and updates the status.

### Step 4: Schedule the Command (Already Configured)

**Location:** `routes/console.php`

```php
Schedule::command('meetings:send-reminders')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->emailOutputOnFailure(env('ADMIN_EMAIL', null));
```

---

## ⚙️ How the Scheduler Works

### Timeline Flow

```
Meeting Created (e.g., 11:29:13 AM IST)
    ↓
Notifications scheduled in meeting_fcm_notifications table
    - Notification 1: scheduled_at = 17:00:00 IST (status: pending)
    - Notification 2: scheduled_at = 17:10:00 IST (status: pending)
    ↓
Scheduler runs every minute (12:00, 12:01, 12:02, ...)
    ↓
At 17:00:00 IST
    - Scheduler finds: scheduled_at (17:00:00) <= now (17:00:00)
    - Job dispatched to queue
    - Status changes: pending → sent (after job completes)
    - User receives notification
    ↓
At 17:10:00 IST
    - Scheduler finds: scheduled_at (17:10:00) <= now (17:10:00)
    - Job dispatched to queue
    - Status changes: pending → sent (after job completes)
    - User receives notification
```

### Scheduler Logic

```php
// Every minute, scheduler runs this query:
$reminders = DB::table('meeting_fcm_notifications')
    ->where('notification_type', 'reminder')
    ->where('status', 'pending')
    ->where('scheduled_at', '<=', Carbon::now(config('app.timezone'))->copy()->addMinute())
    ->get();

// Example at 17:00:00 IST:
// - Notification with scheduled_at = 17:00:00 → Found (17:00:00 <= 17:01:00) ✅
// - Notification with scheduled_at = 17:10:00 → Not found (17:10:00 > 17:01:00) ❌

// Example at 17:10:00 IST:
// - Notification with scheduled_at = 17:00:00 → Not found (already sent) ❌
// - Notification with scheduled_at = 17:10:00 → Found (17:10:00 <= 17:11:00) ✅
```

### Important Notes

1. **Scheduler runs every minute**: Notifications are sent within 1 minute of scheduled time
2. **Queue worker required**: Jobs must be processed by `php artisan queue:work`
3. **Timezone**: All times use **India timezone (Asia/Kolkata/IST)**
4. **Past notifications skipped**: If `scheduled_at` is in the past, notification is skipped
5. **1-minute buffer**: Query uses `addMinute()` to catch notifications due in the next minute

---

## 🗄️ Database Schema

### `meeting_fcm_notifications` Table

**Purpose:** Tracks FCM notification delivery status and history.

```sql
CREATE TABLE meeting_fcm_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    meeting_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    notification_type ENUM('created', 'reminder', 'starting', 'updated', 'cancelled'),
    scheduled_at TIMESTAMP NULL,        -- THIS IS THE KEY FIELD
    sent_at TIMESTAMP NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_meeting_user (meeting_id, user_id),
    INDEX idx_scheduled_at (scheduled_at),  -- CRITICAL for scheduler performance
    INDEX idx_status (status),
    
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### `meeting_notifications` Table

**Purpose:** Stores notification rules/settings for meetings (NOT for tracking FCM delivery).

```sql
CREATE TABLE meeting_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    meeting_id BIGINT NOT NULL,
    minutes INT NOT NULL,
    unit VARCHAR(20) DEFAULT 'minutes',  -- minutes, hours, days
    trigger VARCHAR(20) DEFAULT 'before',  -- before, after
    is_enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
);
```

**Note:** This table stores notification **rules/settings**, not the actual scheduled notifications.

---

## ✅ Verification & Testing

### 1. Verify Scheduled Times

```php
// In Laravel Tinker
php artisan tinker

// Check notifications for a meeting
$meetingId = 123;
$notifications = DB::table('meeting_fcm_notifications')
    ->where('meeting_id', $meetingId)
    ->where('notification_type', 'reminder')
    ->orderBy('scheduled_at', 'asc')
    ->get(['id', 'scheduled_at', 'status', 'user_id']);

foreach ($notifications as $n) {
    echo "ID: {$n->id}, Scheduled: {$n->scheduled_at}, Status: {$n->status}, User: {$n->user_id}\n";
}
```

### 2. Verify Calculation

```php
// Verify calculation is correct
$meeting = DB::table('meetings')->where('id', 123)->first();
$meetingDateTime = Carbon::parse("{$meeting->date} {$meeting->time}", config('app.timezone'));

$settings = DB::table('meeting_notifications')
    ->where('meeting_id', 123)
    ->where('is_enabled', true)
    ->get();

$scheduled = DB::table('meeting_fcm_notifications')
    ->where('meeting_id', 123)
    ->where('notification_type', 'reminder')
    ->get();

foreach ($settings as $setting) {
    $expected = $meetingDateTime->copy();
    if ($setting->unit === 'days') {
        $expected->subDays($setting->minutes);
    } elseif ($setting->unit === 'hours') {
        $expected->subHours($setting->minutes);
    } else {
        $expected->subMinutes($setting->minutes);
    }
    
    echo "Setting: {$setting->minutes} {$setting->unit} before\n";
    echo "  Expected: {$expected->toDateTimeString()}\n";
    
    $matching = $scheduled->filter(function($s) use ($expected) {
        return Carbon::parse($s->scheduled_at)->eq($expected);
    });
    
    if ($matching->count() > 0) {
        echo "  ✅ Match found: {$matching->count()} notifications scheduled\n";
    } else {
        echo "  ❌ No matching scheduled notification found\n";
    }
    echo "\n";
}
```

### 3. Test Scheduler Manually

```bash
# Run scheduler command manually
php artisan meetings:send-reminders

# Expected output:
# Reminder notifications processed
```

### 4. Check Due Notifications

```php
// Check what notifications are due right now
$now = Carbon::now(config('app.timezone'));
$dueNotifications = DB::table('meeting_fcm_notifications')
    ->where('notification_type', 'reminder')
    ->where('status', 'pending')
    ->where('scheduled_at', '<=', $now)
    ->get();

echo "Due notifications: " . $dueNotifications->count() . "\n";
foreach ($dueNotifications as $n) {
    echo "  - ID: {$n->id}, Scheduled: {$n->scheduled_at}, Now: {$now->toDateTimeString()}\n";
}
```

### 5. Monitor Scheduler Logs

```bash
# Watch scheduler output
tail -f storage/logs/scheduler.log

# Watch Laravel logs
tail -f storage/logs/laravel.log | grep "Scheduler"

# Watch for reminder processing
tail -f storage/logs/laravel.log | grep "Reminder scheduler"
```

### 6. Check Upcoming Reminders

```php
// Check next 10 reminders
$upcoming = DB::table('meeting_fcm_notifications')
    ->join('meetings', 'meeting_fcm_notifications.meeting_id', '=', 'meetings.id')
    ->where('meeting_fcm_notifications.notification_type', 'reminder')
    ->where('meeting_fcm_notifications.status', 'pending')
    ->where('meeting_fcm_notifications.scheduled_at', '>', now())
    ->where('meetings.status', '!=', 'Cancelled')
    ->orderBy('meeting_fcm_notifications.scheduled_at', 'asc')
    ->limit(10)
    ->select('meeting_fcm_notifications.*', 'meetings.meeting_title', 'meetings.date', 'meetings.time')
    ->get();

foreach ($upcoming as $r) {
    $reminderTime = Carbon::parse($r->scheduled_at);
    $meetingTime = Carbon::parse($r->date . ' ' . $r->time);
    echo "📅 {$r->meeting_title}\n";
    echo "   ⏰ Reminder: {$reminderTime->format('d M Y, h:i A')}\n";
    echo "   🕐 Meeting: {$meetingTime->format('d M Y, h:i A')}\n";
    echo "   ⏳ Will send in: " . round(now()->diffInMinutes($reminderTime)) . " minutes\n\n";
}
```

---

## 🔍 Troubleshooting

### Issue 1: Notifications Not Scheduled

**Symptom**: No records in `meeting_fcm_notifications` table for a meeting

**Check:**
```php
$meetingId = 123;
$count = DB::table('meeting_fcm_notifications')
    ->where('meeting_id', $meetingId)
    ->where('notification_type', 'reminder')
    ->count();

echo "Reminder notifications: $count\n";
```

**Fix**: 
- Ensure meeting creation/update calls the notification scheduling code
- Check if `meeting_notifications` table has enabled settings for this meeting
- Verify default settings are applied if no custom settings exist

---

### Issue 2: Wrong Scheduled Time

**Symptom**: `scheduled_at` timestamp is incorrect

**Check:**
```php
$meeting = DB::table('meetings')->where('id', 123)->first();
$meetingDateTime = Carbon::parse("{$meeting->date} {$meeting->time}", config('app.timezone'));

$settings = DB::table('meeting_notifications')
    ->where('meeting_id', 123)
    ->where('is_enabled', true)
    ->get();

$scheduled = DB::table('meeting_fcm_notifications')
    ->where('meeting_id', 123)
    ->where('notification_type', 'reminder')
    ->get();

foreach ($settings as $setting) {
    $expected = $meetingDateTime->copy();
    if ($setting->unit === 'days') {
        $expected->subDays($setting->minutes);
    } elseif ($setting->unit === 'hours') {
        $expected->subHours($setting->minutes);
    } else {
        $expected->subMinutes($setting->minutes);
    }
    
    $found = $scheduled->firstWhere('scheduled_at', $expected->toDateTimeString());
    
    if (!$found) {
        echo "❌ Mismatch for setting: {$setting->minutes} {$setting->unit}\n";
        echo "   Expected: {$expected->toDateTimeString()}\n";
        echo "   Found: " . ($scheduled->pluck('scheduled_at')->implode(', ') ?: 'None') . "\n";
    }
}
```

**Fix**: 
- Check calculation logic in `MeetingController.php`
- Verify timezone is correctly set (`config('app.timezone')`)
- Ensure `meeting->date` and `meeting->time` are in correct format

---

### Issue 3: Scheduler Not Running

**Symptom**: Notifications scheduled but not sent

**Check:**
```bash
# Check if scheduler is running
ps aux | grep "schedule:work"

# Check if command exists
php artisan list | grep meetings:send-reminders
```

**Fix**: Start scheduler
```bash
# Development
php artisan schedule:work

# Production (add to crontab)
* * * * * cd /path-to-api && php artisan schedule:run >> /dev/null 2>&1
```

---

### Issue 4: Queue Worker Not Running

**Symptom**: Scheduler finds notifications but they're not sent

**Check:**
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Check pending jobs
php artisan queue:work --once
```

**Fix**: Start queue worker
```bash
php artisan queue:work
```

---

### Issue 5: Notifications Scheduled in Past

**Symptom**: Notifications skipped with "scheduled in past" warning

**Check:**
```php
$pastNotifications = DB::table('meeting_fcm_notifications')
    ->where('notification_type', 'reminder')
    ->where('scheduled_at', '<', Carbon::now(config('app.timezone')))
    ->where('status', 'pending')
    ->get();

echo "Past notifications: " . $pastNotifications->count() . "\n";
```

**Fix**: 
- Check meeting time is in the future
- Verify timezone settings (`APP_TIMEZONE=Asia/Kolkata` in `.env`)
- Ensure calculation uses correct meeting datetime with timezone

---

### Issue 6: No FCM Tokens

**Symptom**: Notifications sent but user doesn't receive

**Check:**
```php
$user = DB::table('users')->where('id', 1)->first();
$tokens = DB::table('fcm_tokens')->where('user_id', 1)->get();

echo "User: {$user->name}\n";
echo "FCM Tokens: " . $tokens->count() . "\n";
```

**Fix**: User must login to register FCM token

---

### Issue 7: Timezone Mismatch

**Symptom**: Notifications sent at wrong time

**Check:**
```php
echo "App timezone: " . config('app.timezone') . "\n";
echo "Current time: " . Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s T') . "\n";
echo ".env APP_TIMEZONE: " . env('APP_TIMEZONE') . "\n";
```

**Fix**: 
- Set `APP_TIMEZONE=Asia/Kolkata` in `.env`
- Clear config cache: `php artisan config:clear`
- Verify all Carbon operations use `config('app.timezone')`

---

## 📊 Summary Table

| Meeting Time | Notification Minutes | Unit | Calculation | Notification Time |
|-------------|---------------------|------|-------------|------------------|
| 14:30:00 IST | 12 | minutes | 14:30 - 12 min | **14:18:00 IST** |
| 14:30:00 IST | 10 | minutes | 14:30 - 10 min | **14:20:00 IST** |
| 17:20:00 IST | 20 | minutes | 17:20 - 20 min | **17:00:00 IST** |
| 17:20:00 IST | 10 | minutes | 17:20 - 10 min | **17:10:00 IST** |
| 09:00:00 IST | 30 | minutes | 09:00 - 30 min | **08:30:00 IST** |
| 09:00:00 IST | 1 | hours | 09:00 - 1 hour | **08:00:00 IST** |
| 09:00:00 IST | 1 | days | 09:00 - 1 day | **08:00:00 IST (previous day)** |

---

## ✅ Checklist

Before going to production:

- [x] `meeting_fcm_notifications` table created with proper indexes
- [x] `meeting_notifications` table created for settings
- [x] Notification scheduling implemented in `MeetingController.php`
- [x] `SendMeetingReminders` command created
- [x] Command scheduled in `routes/console.php` to run every minute
- [x] Queue worker running (`php artisan queue:work`)
- [x] Scheduler running (`php artisan schedule:work` or crontab)
- [x] FCM service configured
- [x] Timezone set correctly (Asia/Kolkata)
- [x] Logging enabled for debugging
- [ ] Tested with real meeting data

---

## 🎯 Key Takeaways

1. **Notification Time = Meeting Time - Notification Minutes** (all in IST)
2. **Scheduler runs every minute** to check for due notifications
3. **Queue worker required** to process notification jobs
4. **`scheduled_at` field** stores the exact time notification should be sent (in IST)
5. **Status flow**: `pending` → `sent` (or `failed`) - handled by job
6. **Past notifications are skipped** automatically during scheduling
7. **Default reminder**: 1 hour before meeting (if no custom settings)
8. **Timezone**: All operations use **India timezone (Asia/Kolkata/IST)**

---

## 📝 Code Locations

- **Scheduling Logic**: `app/Http/Controllers/Api/MeetingController.php` (line ~480-545)
- **Scheduler Command**: `app/Console/Commands/SendMeetingReminders.php`
- **Queue Job**: `app/Jobs/SendMeetingNotificationJob.php`
- **Scheduler Config**: `routes/console.php` (line 12-16)
- **FCM Service**: `app/Services/FcmService.php`

---

## 🔗 Related Documentation

- [Frontend Notification Setup](./FRONTEND_NOTIFICATION_SETUP.md)
- [Backend Notification Setup](./BACKEND_NOTIFICATION_SETUP.md)
- [Notification Preferences API](./NOTIFICATION_PREFERENCES_API.md)
- [Notification Troubleshooting Guide](./NOTIFICATION_TROUBLESHOOTING_GUIDE.md)

---

**Last Updated**: 2025-12-24  
**Timezone**: Asia/Kolkata (IST)  
**Backend API**: http://localhost:8000/api  
**Firebase Project**: meeting-53f24







