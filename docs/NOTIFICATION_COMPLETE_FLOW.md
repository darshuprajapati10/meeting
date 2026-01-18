# Notification System - Complete Flow Documentation

## Overview

This document explains the complete flow of how push notifications work from Flutter frontend to Laravel backend, including all steps, data transformations, and database records.

## Table of Contents

1. [Complete Flow Diagram](#complete-flow-diagram)
2. [Flutter Side Flow](#flutter-side-flow)
3. [Laravel Backend Flow](#laravel-backend-flow)
4. [Step-by-Step Scenarios](#step-by-step-scenarios)
5. [Data Flow at Each Step](#data-flow-at-each-step)
6. [Database Records Tracking](#database-records-tracking)

---

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPLETE NOTIFICATION FLOW                     │
└─────────────────────────────────────────────────────────────────┘

FLUTTER SIDE:
┌──────────────┐
│ User Opens   │
│ Flutter App  │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ Firebase.initialize() │
│ Initialize FCM       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Request Permission   │
│ (iOS/Android)        │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Get FCM Token        │
│ messaging.getToken() │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ User Logs In         │
│ Get Auth Token       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ POST /api/fcm/register               │
│ {                                    │
│   "token": "fcm_token_here",        │
│   "platform": "android",            │
│   "device_id": "device_123"         │
│ }                                    │
└──────┬───────────────────────────────┘
       │
       │ HTTP Request
       │
       ▼
┌─────────────────────────────────────────────────────────────────┐
│                        LARAVEL BACKEND                           │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│ FcmTokenController   │
│ register()          │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Validate Token       │
│ FcmService::         │
│ validateToken()      │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Save to Database     │
│ fcm_tokens table     │
│ INSERT/UPDATE        │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Return Success       │
│ {                    │
│   "success": true,   │
│   "message": "..."   │
│ }                    │
└──────┬───────────────┘
       │
       │ HTTP Response
       │
       ▼
┌──────────────────────┐
│ Token Registered     │
│ Ready for            │
│ Notifications        │
└──────────────────────┘

═══════════════════════════════════════════════════════════════════

USER ACTION: CREATE MEETING

┌──────────────────────┐
│ User Creates Meeting │
│ in Flutter App       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ POST /api/meeting/save               │
│ {                                    │
│   "meeting_title": "Team Meeting",  │
│   "date": "2025-01-15",             │
│   "time": "14:00:00",                │
│   ...                                │
│ }                                    │
└──────┬───────────────────────────────┘
       │
       │ HTTP Request
       │
       ▼
┌──────────────────────┐
│ MeetingController    │
│ save()               │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Create Meeting       │
│ Save to Database     │
│ meetings table       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ sendMeetingNotification()│
│ Called               │
└──────┬───────────────┘
       │
       ├──► Get meeting creator (created_by)
       │
       ├──► Create notification record
       │    meeting_fcm_notifications table
       │    status: 'pending'
       │
       └──► Dispatch SendMeetingNotificationJob
                    │
                    ▼
            ┌───────────────────────┐
            │ Queue (jobs table)    │
            │ Job stored            │
            └──────┬────────────────┘
                   │
                   ▼
            ┌───────────────────────┐
            │ Queue Worker          │
            │ php artisan           │
            │ queue:work            │
            └──────┬────────────────┘
                   │
                   ▼
            ┌───────────────────────┐
            │ SendMeetingNotification│
            │ Job::handle()         │
            └──────┬────────────────┘
                   │
                   ├──► Get user's FCM tokens
                   │    from fcm_tokens table
                   │
                   └──► Call FcmService::sendToUser()
                                │
                                ▼
                        ┌───────────────────────┐
                        │ FcmService            │
                        │ sendToUser()          │
                        └──────┬────────────────┘
                               │
                               ├──► Get all tokens for user
                               │
                               └──► For each token:
                                    sendNotification()
                                            │
                                            ▼
                                    ┌───────────────────────┐
                                    │ Firebase FCM API      │
                                    │ Send Push Notification│
                                    └──────┬────────────────┘
                                           │
                                           │ FCM Service
                                           │
                                           ▼
                                    ┌───────────────────────┐
                                    │ User's Device         │
                                    │ Receives Notification │
                                    └──────┬────────────────┘
                                           │
                                           ▼
                                    ┌───────────────────────┐
                                    │ Flutter App           │
                                    │ Notification Handler  │
                                    └──────┬────────────────┘
                                           │
                                           ├──► onMessage (foreground)
                                           ├──► onMessageOpenedApp (background)
                                           └──► getInitialMessage() (terminated)
                                                    │
                                                    ▼
                                            ┌───────────────────────┐
                                            │ Handle Navigation     │
                                            │ Based on data.type    │
                                            └───────────────────────┘

                   │
                   ▼
            ┌───────────────────────┐
            │ Update Status         │
            │ meeting_fcm_notifications│
            │ status: 'sent'        │
            │ sent_at: now()        │
            └───────────────────────┘
```

---

## Flutter Side Flow

### Step 1: App Initialization

```dart
// main.dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  // Setup background handler
  FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

  // Initialize FCM service
  await FcmService().initialize();

  runApp(MyApp());
}
```

**What happens**:
- Firebase is initialized
- Background message handler is registered
- FCM service is initialized

---

### Step 2: Request Permission & Get Token

```dart
// FcmService.initialize()
NotificationSettings settings = await _messaging.requestPermission(
  alert: true,
  badge: true,
  sound: true,
);

_fcmToken = await _messaging.getToken();
```

**What happens**:
- User is prompted for notification permission (iOS/Android)
- FCM token is obtained from Firebase
- Token is stored locally

**Token Format**:
```
Android: dK8xYz9aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abcdefghijklmnopqrstuvwxyz...
iOS: aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ...
Web: xYz1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ...
```

---

### Step 3: User Login & Token Registration

```dart
// After successful login
await FcmService().setAuthToken(authToken);
// This triggers:
await registerTokenWithBackend(_fcmToken);
```

**API Call**:
```http
POST /api/fcm/register
Authorization: Bearer {auth_token}
Content-Type: application/json

{
  "token": "fcm_token_here",
  "platform": "android",
  "device_id": "device_123"
}
```

**Backend Response**:
```json
{
  "success": true,
  "message": "FCM token registered successfully"
}
```

**Database Record Created**:
```sql
INSERT INTO fcm_tokens (user_id, token, platform, device_id, created_at, updated_at)
VALUES (1, 'fcm_token_here', 'android', 'device_123', NOW(), NOW());
```

---

### Step 4: Receive Notification

**Foreground (App is open)**:
```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  // Notification received
  // Show in-app notification
  // Navigate if needed
});
```

**Background (App is minimized)**:
```dart
FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
  // User tapped notification
  // Navigate to relevant screen
});
```

**Terminated (App is closed)**:
```dart
FirebaseMessaging.instance.getInitialMessage().then((message) {
  if (message != null) {
    // User opened app from notification
    // Navigate to relevant screen
  }
});
```

**Notification Payload**:
```json
{
  "notification": {
    "title": "New Meeting Created",
    "body": "Meeting 'Team Standup' has been scheduled"
  },
  "data": {
    "type": "meeting_created",
    "meeting_id": "123",
    "action": "view_meeting"
  }
}
```

---

## Laravel Backend Flow

### Step 1: FCM Token Registration

**Endpoint**: `POST /api/fcm/register`

**Controller**: `FcmTokenController::register()`

**Flow**:
1. Validate request (token, platform required)
2. Get authenticated user
3. Validate token format (`FcmService::validateToken()`)
4. Check if token exists for user
5. Insert or update in `fcm_tokens` table
6. Return success response

**Database Operation**:
```sql
-- Check if exists
SELECT * FROM fcm_tokens 
WHERE user_id = 1 AND token = 'fcm_token_here';

-- If exists, update
UPDATE fcm_tokens 
SET platform = 'android', device_id = 'device_123', updated_at = NOW()
WHERE id = 1;

-- If not exists, insert
INSERT INTO fcm_tokens (user_id, token, platform, device_id, created_at, updated_at)
VALUES (1, 'fcm_token_here', 'android', 'device_123', NOW(), NOW());
```

---

### Step 2: Meeting Created - Notification Trigger

**Endpoint**: `POST /api/meeting/save`

**Controller**: `MeetingController::save()`

**Flow**:
1. Validate and save meeting
2. If new meeting (no `id`), call `sendMeetingNotification()` with type `'created'`
3. If update (has `id`), call `sendMeetingNotification()` with type `'updated'`
4. For new meetings, also call `scheduleReminderNotifications()`

**Code Flow**:
```php
// MeetingController::save()
if ($request->id) {
    // Update
    $this->sendMeetingNotification($meeting->id, 'updated', ...);
} else {
    // Create
    $this->sendMeetingNotification($meeting->id, 'created', ...);
    $this->scheduleReminderNotifications($meeting->id, $meeting->date, $meeting->time);
}
```

---

### Step 3: Send Notification Method

**Method**: `MeetingController::sendMeetingNotification()`

**Flow**:
1. Get meeting and creator user ID
2. Create notification record in `meeting_fcm_notifications` table (status: 'pending')
3. Dispatch `SendMeetingNotificationJob` to queue

**Database Record Created**:
```sql
INSERT INTO meeting_fcm_notifications 
(meeting_id, user_id, notification_type, status, created_at, updated_at)
VALUES (123, 1, 'created', 'pending', NOW(), NOW());
```

**Job Dispatched**:
```php
SendMeetingNotificationJob::dispatch(
    $userId,           // 1
    $meetingId,        // 123
    'created',         // notification_type
    'New Meeting Created',
    "Meeting 'Team Standup' has been scheduled",
    [
        'type' => 'meeting_created',
        'meeting_id' => 123,
        'action' => 'view_meeting'
    ]
);
```

---

### Step 4: Queue Job Processing

**Job**: `SendMeetingNotificationJob::handle()`

**Flow**:
1. Queue worker picks up job from `jobs` table
2. Job calls `FcmService::sendToUser()`
3. FCM service gets all tokens for user from `fcm_tokens` table
4. Sends notification to each token via Firebase API
5. Updates notification status in `meeting_fcm_notifications` table

**Database Queries**:
```sql
-- Get user's tokens
SELECT token FROM fcm_tokens WHERE user_id = 1;

-- Update notification status
UPDATE meeting_fcm_notifications
SET status = 'sent', sent_at = NOW()
WHERE meeting_id = 123 
  AND user_id = 1 
  AND notification_type = 'created';
```

**FCM API Call**:
```php
// FcmService::sendNotification()
$message = CloudMessage::withTarget('token', $token)
    ->withNotification(Notification::create($title, $body))
    ->withData([
        'type' => 'meeting_created',
        'meeting_id' => '123',
        'action' => 'view_meeting'
    ]);

$this->messaging->send($message);
```

---

### Step 5: Scheduled Reminders

**Command**: `meetings:send-reminders`

**Scheduler**: Runs every minute via `routes/console.php`

**Flow**:
1. Command runs every minute
2. Finds meetings starting in next 5 minutes (starting soon)
3. Finds pending reminder notifications (`scheduled_at <= now()`)
4. Dispatches notification jobs for each

**Database Query**:
```sql
-- Find pending reminders
SELECT * FROM meeting_fcm_notifications
WHERE notification_type = 'reminder'
  AND status = 'pending'
  AND scheduled_at <= NOW()
JOIN meetings ON meeting_fcm_notifications.meeting_id = meetings.id
WHERE meetings.status != 'Cancelled';
```

---

## Step-by-Step Scenarios

### Scenario 1: User Creates Meeting

**Timeline**:

1. **T+0s**: User taps "Create Meeting" in Flutter app
2. **T+0.5s**: Flutter sends `POST /api/meeting/save`
3. **T+1s**: Laravel saves meeting to database
4. **T+1.5s**: Laravel creates notification record (status: 'pending')
5. **T+2s**: Laravel dispatches `SendMeetingNotificationJob` to queue
6. **T+2.5s**: Queue worker picks up job
7. **T+3s**: Job gets user's FCM tokens
8. **T+3.5s**: Job sends notification via Firebase API
9. **T+4s**: Firebase delivers notification to device
10. **T+4.5s**: Flutter receives notification
11. **T+5s**: User sees notification

**Database Records**:

```sql
-- Meeting created
INSERT INTO meetings (id, meeting_title, date, time, created_by, ...)
VALUES (123, 'Team Standup', '2025-01-15', '14:00:00', 1, ...);

-- Notification record created
INSERT INTO meeting_fcm_notifications 
(meeting_id, user_id, notification_type, status, created_at)
VALUES (123, 1, 'created', 'pending', NOW());

-- Job queued
INSERT INTO jobs (queue, payload, attempts, created_at)
VALUES ('default', '{"uuid":"...","displayName":"App\\Jobs\\SendMeetingNotificationJob",...}', 0, NOW());

-- After processing:
UPDATE meeting_fcm_notifications
SET status = 'sent', sent_at = NOW()
WHERE id = 1;
```

---

### Scenario 2: Meeting Reminder (1 Hour Before)

**Timeline**:

1. **T-1 hour**: Meeting created, reminder scheduled for 1 hour before
2. **T-1 hour + 0s**: Reminder record created with `scheduled_at = meeting_time - 1 hour`
3. **T-0s**: Scheduler command runs, finds pending reminder
4. **T+1s**: Job dispatched to queue
5. **T+2s**: Queue worker processes job
6. **T+3s**: Notification sent via Firebase
7. **T+4s**: User receives reminder notification

**Database Records**:

```sql
-- Reminder scheduled when meeting created
INSERT INTO meeting_fcm_notifications 
(meeting_id, user_id, notification_type, scheduled_at, status, created_at)
VALUES (123, 1, 'reminder', '2025-01-15 13:00:00', 'pending', NOW());

-- After scheduler processes:
UPDATE meeting_fcm_notifications
SET status = 'sent', sent_at = NOW()
WHERE id = 2;
```

---

### Scenario 3: Meeting Starting Soon (5 Minutes Before)

**Timeline**:

1. **T-5 minutes**: Meeting exists, scheduled for now + 5 minutes
2. **T+0s**: Scheduler command runs
3. **T+0.5s**: Command finds meeting starting in next 5 minutes
4. **T+1s**: Creates notification record (type: 'starting')
5. **T+1.5s**: Dispatches job
6. **T+2.5s**: Notification sent
7. **T+3.5s**: User receives "Starting Soon" notification

---

## Data Flow at Each Step

### Step 1: Flutter → Backend (Token Registration)

**Request**:
```json
{
  "token": "dK8xYz9aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
  "platform": "android",
  "device_id": "device_123"
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

### Step 2: Flutter → Backend (Create Meeting)

**Request**:
```json
{
  "meeting_title": "Team Standup",
  "date": "2025-01-15",
  "time": "14:00:00",
  "duration": 60,
  "attendees": [1, 2, 3],
  "status": "Created",
  "meeting_type": "Video Call"
}
```

**Response**:
```json
{
  "data": {
    "id": 123,
    "meeting_title": "Team Standup",
    ...
  },
  "message": "Meeting created successfully."
}
```

**Note**: Notification is sent asynchronously, not in this response.

---

### Step 3: Backend → Firebase (Send Notification)

**FCM API Call**:
```json
{
  "message": {
    "token": "dK8xYz9aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
    "notification": {
      "title": "New Meeting Created",
      "body": "Meeting 'Team Standup' has been scheduled"
    },
    "data": {
      "type": "meeting_created",
      "meeting_id": "123",
      "action": "view_meeting"
    }
  }
}
```

---

### Step 4: Firebase → Device (Push Notification)

**Device Receives**:
```json
{
  "notification": {
    "title": "New Meeting Created",
    "body": "Meeting 'Team Standup' has been scheduled"
  },
  "data": {
    "type": "meeting_created",
    "meeting_id": "123",
    "action": "view_meeting"
  }
}
```

---

## Database Records Tracking

### fcm_tokens Table

**Purpose**: Store FCM device tokens for each user

**Record Example**:
```sql
id: 1
user_id: 1
token: dK8xYz9aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890...
platform: android
device_id: device_123
created_at: 2025-01-15 10:00:00
updated_at: 2025-01-15 10:00:00
```

---

### meeting_fcm_notifications Table

**Purpose**: Track notification delivery status

**Record Examples**:

**Created Notification**:
```sql
id: 1
meeting_id: 123
user_id: 1
notification_type: created
scheduled_at: NULL
sent_at: 2025-01-15 10:05:00
status: sent
error_message: NULL
created_at: 2025-01-15 10:00:00
updated_at: 2025-01-15 10:05:00
```

**Reminder Notification**:
```sql
id: 2
meeting_id: 123
user_id: 1
notification_type: reminder
scheduled_at: 2025-01-15 13:00:00
sent_at: 2025-01-15 13:00:05
status: sent
error_message: NULL
created_at: 2025-01-15 10:00:00
updated_at: 2025-01-15 13:00:05
```

**Failed Notification**:
```sql
id: 3
meeting_id: 124
user_id: 1
notification_type: created
scheduled_at: NULL
sent_at: NULL
status: failed
error_message: No FCM tokens found for user
created_at: 2025-01-15 11:00:00
updated_at: 2025-01-15 11:00:05
```

---

### jobs Table

**Purpose**: Store queued jobs

**Record Example**:
```sql
id: 1
queue: default
payload: {"uuid":"...","displayName":"App\\Jobs\\SendMeetingNotificationJob",...}
attempts: 0
reserved_at: 2025-01-15 10:00:05
reserved: 1
available_at: 2025-01-15 10:00:00
created_at: 2025-01-15 10:00:00
```

**After Processing**: Record is deleted from `jobs` table

---

## Summary

### Complete Flow Summary

1. **Flutter**: User opens app → Gets FCM token → Logs in → Registers token
2. **Backend**: Receives token → Validates → Saves to database
3. **User Action**: Creates/updates/deletes meeting
4. **Backend**: Saves meeting → Creates notification record → Dispatches job
5. **Queue**: Worker processes job → Gets tokens → Sends via FCM
6. **Firebase**: Receives request → Delivers to device
7. **Device**: Receives notification → Flutter handles → Navigates user

### Key Points

- Notifications are **asynchronous** (via queue)
- Status is **tracked** in database
- Multiple devices per user are **supported**
- Reminders are **scheduled** automatically
- All notification types are **handled** consistently

---

*Last Updated*: 2025-11-22

