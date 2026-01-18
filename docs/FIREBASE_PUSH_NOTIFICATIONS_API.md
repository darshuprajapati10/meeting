# Firebase Push Notifications API - Frontend Developer Guide

## Table of Contents
1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [API Endpoints](#api-endpoints)
4. [Notification Types](#notification-types)
5. [Notification Payload Structure](#notification-payload-structure)
6. [Implementation Guide](#implementation-guide)
7. [Error Handling](#error-handling)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

---

## Overview

This API provides Firebase Cloud Messaging (FCM) push notifications for meeting events. The system sends notifications to registered devices when:

- A meeting is **created**
- A meeting is **updated**
- A meeting is **cancelled**
- A meeting **reminder** is triggered (1 hour before)
- A meeting is **starting soon** (5 minutes before)

### Key Features

- Multi-device support (iOS, Android, Web)
- Asynchronous notification delivery
- Notification status tracking
- Automatic reminder scheduling

---

## Getting Started

### Prerequisites

1. Firebase project configured in backend
2. FCM token obtained from device
3. User authentication token (Bearer token)

### Base URL

```
Production: https://your-api-domain.com/api
Development: http://localhost:8000/api
```

### Authentication

All endpoints require authentication using Bearer token:

```
Authorization: Bearer {your_auth_token}
```

---

## API Endpoints

### 1. Register FCM Token

Register or update FCM device token for push notifications.

**Endpoint:** `POST /api/fcm/register`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "token": "fcm_device_token_here",
  "platform": "android",  // Required: "ios", "android", or "web"
  "device_id": "optional_device_identifier"  // Optional
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "FCM token registered successfully"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Invalid FCM token format"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "token": ["The token field is required."],
    "platform": ["The platform field is required."]
  }
}
```

**Example (JavaScript/Fetch):**
```javascript
async function registerFcmToken(token, platform, deviceId = null) {
  const response = await fetch('http://localhost:8000/api/fcm/register', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${authToken}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      token: token,
      platform: platform,
      device_id: deviceId
    })
  });
  
  const data = await response.json();
  return data;
}

// Usage
await registerFcmToken('fcm_token_here', 'android', 'device_123');
```

**Example (Flutter/Dart):**
```dart
Future<Map<String, dynamic>> registerFcmToken(
  String token, 
  String platform, 
  String? deviceId
) async {
  final response = await http.post(
    Uri.parse('http://localhost:8000/api/fcm/register'),
    headers: {
      'Authorization': 'Bearer $authToken',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'token': token,
      'platform': platform,
      'device_id': deviceId,
    }),
  );
  
  return jsonDecode(response.body);
}
```

---

### 2. Unregister FCM Token

Remove FCM device token when user logs out or app is uninstalled.

**Endpoint:** `POST /api/fcm/unregister`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "token": "fcm_device_token_here"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "FCM token unregistered successfully"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "token": ["The token field is required."]
  }
}
```

**Example (JavaScript/Fetch):**
```javascript
async function unregisterFcmToken(token) {
  const response = await fetch('http://localhost:8000/api/fcm/unregister', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${authToken}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      token: token
    })
  });
  
  const data = await response.json();
  return data;
}
```

---

## Notification Types

The backend sends notifications for the following events:

### 1. Meeting Created

**Trigger:** When a new meeting is successfully created

**Notification Title:** `"New Meeting Created"`

**Notification Body:** `"Meeting '{meeting_title}' has been scheduled"`

**Data Payload:**
```json
{
  "type": "meeting_created",
  "meeting_id": 123,
  "action": "view_meeting"
}
```

---

### 2. Meeting Updated

**Trigger:** When meeting details are modified

**Notification Title:** `"Meeting Updated"`

**Notification Body:** `"Meeting '{meeting_title}' has been updated"`

**Data Payload:**
```json
{
  "type": "meeting_updated",
  "meeting_id": 123,
  "action": "view_meeting"
}
```

---

### 3. Meeting Cancelled

**Trigger:** When a meeting is deleted/cancelled

**Notification Title:** `"Meeting Cancelled"`

**Notification Body:** `"Meeting '{meeting_title}' has been cancelled"`

**Data Payload:**
```json
{
  "type": "meeting_cancelled",
  "meeting_id": 123,
  "action": "view_calendar"
}
```

---

### 4. Meeting Reminder

**Trigger:** 1 hour before meeting start time

**Notification Title:** `"Meeting Reminder"`

**Notification Body:** `"Reminder: Meeting '{meeting_title}' is scheduled"`

**Data Payload:**
```json
{
  "type": "meeting_reminder",
  "meeting_id": 123,
  "action": "view_meeting"
}
```

---

### 5. Meeting Starting Soon

**Trigger:** 5 minutes before meeting start time

**Notification Title:** `"Meeting Starting Soon"`

**Notification Body:** `"Meeting '{meeting_title}' starts in 5 minutes"`

**Data Payload:**
```json
{
  "type": "meeting_starting",
  "meeting_id": 123,
  "action": "join_meeting"
}
```

---

## Notification Payload Structure

All notifications include the following structure:

### Standard FCM Notification Format

```json
{
  "notification": {
    "title": "Notification Title",
    "body": "Notification message text"
  },
  "data": {
    "type": "meeting_created|meeting_updated|meeting_cancelled|meeting_reminder|meeting_starting",
    "meeting_id": 123,
    "action": "view_meeting|view_calendar|join_meeting"
  }
}
```

### Data Fields

| Field | Type | Description | Possible Values |
|-------|------|-------------|-----------------|
| `type` | string | Notification type identifier | `meeting_created`, `meeting_updated`, `meeting_cancelled`, `meeting_reminder`, `meeting_starting` |
| `meeting_id` | integer | ID of the meeting | Any valid meeting ID |
| `action` | string | Suggested user action | `view_meeting`, `view_calendar`, `join_meeting` |

---

## Implementation Guide

### Step 1: Get FCM Token

#### Flutter Example

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

Future<String?> getFcmToken() async {
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  
  // Request permission
  NotificationSettings settings = await messaging.requestPermission(
    alert: true,
    badge: true,
    sound: true,
  );
  
  if (settings.authorizationStatus == AuthorizationStatus.authorized) {
    // Get token
    String? token = await messaging.getToken();
    return token;
  }
  
  return null;
}
```

#### React Native Example

```javascript
import messaging from '@react-native-firebase/messaging';

async function getFcmToken() {
  // Request permission
  const authStatus = await messaging().requestPermission();
  const enabled =
    authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
    authStatus === messaging.AuthorizationStatus.PROVISIONAL;

  if (enabled) {
    // Get token
    const token = await messaging().getToken();
    return token;
  }
  
  return null;
}
```

#### Web Example (JavaScript)

```javascript
import { getMessaging, getToken } from 'firebase/messaging';

async function getFcmToken() {
  const messaging = getMessaging();
  
  try {
    const token = await getToken(messaging, {
      vapidKey: 'YOUR_VAPID_KEY'
    });
    return token;
  } catch (error) {
    console.error('Error getting token:', error);
    return null;
  }
}
```

---

### Step 2: Register Token on Backend

After obtaining FCM token, register it with the backend:

```javascript
// After user login
async function setupPushNotifications() {
  const fcmToken = await getFcmToken();
  const platform = getPlatform(); // 'ios', 'android', or 'web'
  const deviceId = getDeviceId(); // Optional
  
  if (fcmToken) {
    await registerFcmToken(fcmToken, platform, deviceId);
  }
}
```

---

### Step 3: Handle Incoming Notifications

#### Flutter Example

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

// Handle foreground messages
Future<void> handleForegroundMessage(RemoteMessage message) async {
  print('Foreground notification: ${message.notification?.title}');
  
  // Handle notification data
  final data = message.data;
  final type = data['type'];
  final meetingId = data['meeting_id'];
  final action = data['action'];
  
  // Navigate based on action
  if (action == 'view_meeting') {
    // Navigate to meeting details
    navigateToMeeting(meetingId);
  } else if (action == 'view_calendar') {
    // Navigate to calendar
    navigateToCalendar();
  } else if (action == 'join_meeting') {
    // Navigate to join meeting
    navigateToJoinMeeting(meetingId);
  }
}

// Handle background messages
@pragma('vm:entry-point')
Future<void> handleBackgroundMessage(RemoteMessage message) async {
  print('Background notification: ${message.notification?.title}');
  // Handle background notification
}

void main() {
  FirebaseMessaging.onMessage.listen(handleForegroundMessage);
  FirebaseMessaging.onMessageOpenedApp.listen(handleForegroundMessage);
  
  // Handle notification when app is opened from terminated state
  FirebaseMessaging.instance.getInitialMessage().then((message) {
    if (message != null) {
      handleForegroundMessage(message);
    }
  });
  
  runApp(MyApp());
}
```

#### React Native Example

```javascript
import messaging from '@react-native-firebase/messaging';

// Handle foreground messages
messaging().onMessage(async remoteMessage => {
  console.log('Foreground notification:', remoteMessage);
  
  const { type, meeting_id, action } = remoteMessage.data;
  
  // Show local notification
  // Navigate based on action
  if (action === 'view_meeting') {
    navigation.navigate('MeetingDetails', { meetingId: meeting_id });
  } else if (action === 'view_calendar') {
    navigation.navigate('Calendar');
  } else if (action === 'join_meeting') {
    navigation.navigate('JoinMeeting', { meetingId: meeting_id });
  }
});

// Handle background/quit state messages
messaging().setBackgroundMessageHandler(async remoteMessage => {
  console.log('Background notification:', remoteMessage);
});

// Handle notification when app is opened from background
messaging().onNotificationOpenedApp(remoteMessage => {
  console.log('Notification opened app:', remoteMessage);
  const { action, meeting_id } = remoteMessage.data;
  // Navigate based on action
});

// Check if app was opened from quit state
messaging()
  .getInitialNotification()
  .then(remoteMessage => {
    if (remoteMessage) {
      console.log('App opened from quit state:', remoteMessage);
      // Navigate based on action
    }
  });
```

#### Web Example (JavaScript)

```javascript
import { getMessaging, onMessage } from 'firebase/messaging';

const messaging = getMessaging();

// Handle foreground messages
onMessage(messaging, (payload) => {
  console.log('Foreground notification:', payload);
  
  const { type, meeting_id, action } = payload.data;
  
  // Show notification
  if (Notification.permission === 'granted') {
    new Notification(payload.notification.title, {
      body: payload.notification.body,
      icon: '/icon.png'
    });
  }
  
  // Navigate based on action
  if (action === 'view_meeting') {
    window.location.href = `/meetings/${meeting_id}`;
  } else if (action === 'view_calendar') {
    window.location.href = '/calendar';
  } else if (action === 'join_meeting') {
    window.location.href = `/meetings/${meeting_id}/join`;
  }
});
```

---

### Step 4: Unregister Token on Logout

When user logs out, unregister the FCM token:

```javascript
async function logout() {
  const fcmToken = await getFcmToken();
  
  if (fcmToken) {
    await unregisterFcmToken(fcmToken);
  }
  
  // Clear auth token
  // Navigate to login
}
```

---

## Error Handling

### Common Error Codes

| Status Code | Description | Solution |
|-------------|-------------|----------|
| 400 | Invalid FCM token format | Check token length and format |
| 401 | Unauthorized | Check authentication token |
| 422 | Validation error | Check request body format |
| 500 | Server error | Check backend logs |

### Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Handling Errors

```javascript
async function registerFcmToken(token, platform, deviceId) {
  try {
    const response = await fetch('http://localhost:8000/api/fcm/register', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: token,
        platform: platform,
        device_id: deviceId
      })
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      if (response.status === 400) {
        console.error('Invalid token format');
      } else if (response.status === 401) {
        console.error('Unauthorized - check auth token');
      } else if (response.status === 422) {
        console.error('Validation errors:', data.errors);
      } else {
        console.error('Server error:', data.message);
      }
      return null;
    }
    
    return data;
  } catch (error) {
    console.error('Network error:', error);
    return null;
  }
}
```

---

## Testing

### 1. Test Token Registration

```javascript
// Test with a dummy token
const testToken = 'test_fcm_token_' + Date.now();
const result = await registerFcmToken(testToken, 'android', 'test_device');
console.log('Registration result:', result);
```

### 2. Test Notification Reception

1. Register a valid FCM token
2. Create a meeting through the API
3. Check if notification is received
4. Verify notification payload structure

### 3. Test Different Notification Types

- Create meeting → Should receive "meeting_created"
- Update meeting → Should receive "meeting_updated"
- Delete meeting → Should receive "meeting_cancelled"
- Wait for reminder → Should receive "meeting_reminder" (1 hour before)
- Wait for starting → Should receive "meeting_starting" (5 minutes before)

---

## Troubleshooting

### Issue: Notifications Not Received

**Check:**
1. FCM token is registered: Call `/api/fcm/register` and verify success
2. Token is valid: Check token format (should be long string)
3. App has notification permissions: Request permissions on device
4. Backend is sending: Check backend logs
5. Device is online: Ensure internet connection

### Issue: Token Registration Fails

**Check:**
1. Authentication token is valid
2. Token format is correct (length > 100 characters)
3. Platform value is correct ("ios", "android", or "web")
4. Backend is running and accessible

### Issue: Notifications Received But Not Displayed

**Check:**
1. Notification permissions are granted
2. App is handling foreground messages correctly
3. Background message handler is set up
4. Notification service is properly initialized

### Issue: Wrong Navigation on Notification Tap

**Check:**
1. Notification data payload is correct
2. Action field is being read correctly
3. Navigation logic matches action values
4. Meeting ID is valid

---

## Best Practices

1. **Register Token After Login**
   - Register FCM token immediately after successful login
   - Update token if it changes (Firebase may refresh tokens)

2. **Handle Token Refresh**
   - Listen for token refresh events
   - Re-register new token with backend

3. **Unregister on Logout**
   - Always unregister token when user logs out
   - Prevents notifications to logged-out users

4. **Handle All App States**
   - Foreground: Show in-app notification
   - Background: Handle notification tap
   - Terminated: Handle initial notification

5. **Error Handling**
   - Always handle errors gracefully
   - Log errors for debugging
   - Don't break app flow if notification fails

6. **User Experience**
   - Show notification even if app is in foreground
   - Navigate to relevant screen on notification tap
   - Provide clear notification messages

---

## Complete Integration Example

### Flutter Complete Example

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class PushNotificationService {
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final String baseUrl = 'http://localhost:8000/api';
  String? authToken;
  
  Future<void> initialize(String token) async {
    authToken = token;
    
    // Request permission
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    
    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      // Get FCM token
      String? fcmToken = await _messaging.getToken();
      
      if (fcmToken != null) {
        await registerToken(fcmToken);
      }
      
      // Listen for token refresh
      _messaging.onTokenRefresh.listen((newToken) {
        registerToken(newToken);
      });
      
      // Handle foreground messages
      _messaging.onMessage.listen((RemoteMessage message) {
        handleNotification(message);
      });
      
      // Handle background messages
      FirebaseMessaging.onBackgroundMessage(handleBackgroundMessage);
      
      // Handle notification tap (app opened from background)
      _messaging.onMessageOpenedApp.listen((RemoteMessage message) {
        handleNotificationTap(message);
      });
      
      // Handle notification tap (app opened from terminated state)
      RemoteMessage? initialMessage = await _messaging.getInitialMessage();
      if (initialMessage != null) {
        handleNotificationTap(initialMessage);
      }
    }
  }
  
  Future<void> registerToken(String fcmToken) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/fcm/register'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'token': fcmToken,
          'platform': 'android', // or 'ios'
          'device_id': 'device_123',
        }),
      );
      
      if (response.statusCode == 200) {
        print('FCM token registered successfully');
      } else {
        print('Failed to register token: ${response.body}');
      }
    } catch (e) {
      print('Error registering token: $e');
    }
  }
  
  void handleNotification(RemoteMessage message) {
    print('Notification received: ${message.notification?.title}');
    
    // Show local notification or update UI
    // You can use flutter_local_notifications package
    
    // Handle data
    final data = message.data;
    final type = data['type'];
    final meetingId = data['meeting_id'];
    final action = data['action'];
    
    // Update app state or show in-app notification
  }
  
  void handleNotificationTap(RemoteMessage message) {
    final data = message.data;
    final action = data['action'];
    final meetingId = int.parse(data['meeting_id']);
    
    // Navigate based on action
    if (action == 'view_meeting') {
      // Navigate to meeting details
      // Navigator.pushNamed(context, '/meeting', arguments: meetingId);
    } else if (action == 'view_calendar') {
      // Navigate to calendar
      // Navigator.pushNamed(context, '/calendar');
    } else if (action == 'join_meeting') {
      // Navigate to join meeting
      // Navigator.pushNamed(context, '/join-meeting', arguments: meetingId);
    }
  }
  
  Future<void> unregisterToken(String fcmToken) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/fcm/unregister'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'token': fcmToken,
        }),
      );
      
      if (response.statusCode == 200) {
        print('FCM token unregistered successfully');
      }
    } catch (e) {
      print('Error unregistering token: $e');
    }
  }
}

// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> handleBackgroundMessage(RemoteMessage message) async {
  print('Background notification: ${message.notification?.title}');
  // Handle background notification
}
```

---

## Summary

### Quick Start Checklist

- [ ] Get FCM token from device
- [ ] Register token with backend after login
- [ ] Handle foreground notifications
- [ ] Handle background notifications
- [ ] Handle notification taps
- [ ] Unregister token on logout
- [ ] Test all notification types
- [ ] Handle errors gracefully

### Key Points

1. **Always register token after login**
2. **Handle all app states** (foreground, background, terminated)
3. **Navigate based on action field** in notification data
4. **Unregister token on logout** to prevent unwanted notifications
5. **Test thoroughly** with different notification types

---

## Support

For backend issues, contact the backend team.

For Firebase/FCM issues, refer to:
- [Firebase Cloud Messaging Documentation](https://firebase.google.com/docs/cloud-messaging)
- [Flutter Firebase Messaging](https://firebase.flutter.dev/docs/messaging/overview)
- [React Native Firebase Messaging](https://rnfirebase.io/messaging/usage)

---

**Last Updated:** November 22, 2025


