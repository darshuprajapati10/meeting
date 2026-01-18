# Frontend Notification Setup Guide - Complete Implementation

## 🎯 Overview

Ye guide frontend developers ke liye hai jo meeting reminder notifications implement karna chahte hain. Backend already configured hai aur notifications send kar raha hai. Ab frontend side se properly receive karna hai.

---

## 📋 Table of Contents

1. [Quick Start Checklist](#quick-start-checklist)
2. [Web Implementation (JavaScript/React/Vue)](#web-implementation)
3. [Flutter Implementation](#flutter-implementation)
4. [React Native Implementation](#react-native-implementation)
5. [Common Issues & Solutions](#common-issues--solutions)
6. [Testing Guide](#testing-guide)
7. [Debugging Tips](#debugging-tips)

---

## ✅ Quick Start Checklist

- [ ] Firebase SDK installed
- [ ] FCM token obtained from device
- [ ] Token registered with backend API
- [ ] Notification permissions requested
- [ ] Foreground message handler setup
- [ ] Background message handler setup
- [ ] Notification tap handler setup
- [ ] Service worker configured (Web only)

---

## 🌐 Web Implementation

### Step 1: Install Dependencies

```bash
npm install firebase
# OR
yarn add firebase
```

### Step 2: Firebase Configuration

Create `firebase-config.js`:

```javascript
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_AUTH_DOMAIN",
  projectId: "meeting-53f24",
  storageBucket: "YOUR_STORAGE_BUCKET",
  messagingSenderId: "YOUR_SENDER_ID",
  appId: "YOUR_APP_ID"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export { messaging, getToken, onMessage };
```

### Step 3: Get FCM Token & Register

```javascript
import { messaging, getToken } from './firebase-config';

// VAPID Key - Backend se lein ya Firebase Console se
const VAPID_KEY = "YOUR_VAPID_KEY";

async function requestNotificationPermission() {
  try {
    const permission = await Notification.requestPermission();
    
    if (permission === 'granted') {
      console.log('✅ Notification permission granted');
      return true;
    } else {
      console.log('❌ Notification permission denied');
      return false;
    }
  } catch (error) {
    console.error('Error requesting permission:', error);
    return false;
  }
}

async function getFCMToken() {
  try {
    const permission = await requestNotificationPermission();
    if (!permission) return null;

    const token = await getToken(messaging, { vapidKey: VAPID_KEY });
    
    if (token) {
      console.log('✅ FCM Token:', token);
      return token;
    } else {
      console.log('❌ No FCM token available');
      return null;
    }
  } catch (error) {
    console.error('Error getting FCM token:', error);
    return null;
  }
}

// Register token with backend
async function registerTokenWithBackend(token) {
  const authToken = localStorage.getItem('auth_token'); // Your auth token
  
  try {
    const response = await fetch('http://localhost:8000/api/fcm/register', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: token,
        platform: 'web',
        device_id: null // Optional
      })
    });

    const data = await response.json();
    
    if (data.success) {
      console.log('✅ Token registered successfully');
      return true;
    } else {
      console.error('❌ Token registration failed:', data.message);
      return false;
    }
  } catch (error) {
    console.error('❌ Error registering token:', error);
    return false;
  }
}

// Initialize on app load (after user login)
async function initializeNotifications() {
  const token = await getFCMToken();
  if (token) {
    await registerTokenWithBackend(token);
  }
}
```

### Step 4: Handle Foreground Notifications

```javascript
import { messaging, onMessage } from './firebase-config';

// Handle notifications when app is open
onMessage(messaging, (payload) => {
  console.log('📬 Foreground notification received:', payload);
  
  const { notification, data } = payload;
  const { type, meeting_id, action } = data;

  // Show browser notification
  if (Notification.permission === 'granted') {
    new Notification(notification.title, {
      body: notification.body,
      icon: '/icon.png', // Your app icon
      badge: '/badge.png',
      tag: `meeting-${meeting_id}`,
      data: {
        meetingId: meeting_id,
        action: action
      }
    });
  }

  // Handle navigation based on action
  handleNotificationAction(action, meeting_id);
});

function handleNotificationAction(action, meetingId) {
  switch (action) {
    case 'view_meeting':
      window.location.href = `/meetings/${meetingId}`;
      break;
    case 'view_calendar':
      window.location.href = '/calendar';
      break;
    case 'join_meeting':
      window.location.href = `/meetings/${meetingId}/join`;
      break;
    default:
      console.log('Unknown action:', action);
  }
}
```

### Step 5: Service Worker (Background Notifications)

Create `public/firebase-messaging-sw.js`:

```javascript
// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_AUTH_DOMAIN",
  projectId: "meeting-53f24",
  storageBucket: "YOUR_STORAGE_BUCKET",
  messagingSenderId: "YOUR_SENDER_ID",
  appId: "YOUR_APP_ID"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
  console.log('📬 Background notification received:', payload);
  
  const { notification, data } = payload;
  const { type, meeting_id, action } = data;

  const notificationTitle = notification.title;
  const notificationOptions = {
    body: notification.body,
    icon: '/icon.png',
    badge: '/badge.png',
    tag: `meeting-${meeting_id}`,
    data: {
      meetingId: meeting_id,
      action: action
    }
  };

  return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
  console.log('🔔 Notification clicked:', event);
  
  event.notification.close();
  
  const { meetingId, action } = event.notification.data;
  
  // Open app and navigate
  event.waitUntil(
    clients.openWindow(`/meetings/${meetingId}`)
  );
});
```

### Step 6: Register Service Worker

```javascript
// In your main app file
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/firebase-messaging-sw.js')
    .then((registration) => {
      console.log('✅ Service Worker registered:', registration);
    })
    .catch((error) => {
      console.error('❌ Service Worker registration failed:', error);
    });
}
```

### Step 7: Handle Notification Click (When App Opens)

```javascript
// Check if app was opened from notification
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.ready.then((registration) => {
    registration.getNotifications().then((notifications) => {
      notifications.forEach((notification) => {
        const data = notification.data;
        if (data && data.meetingId) {
          handleNotificationAction(data.action, data.meetingId);
        }
        notification.close();
      });
    });
  });
}
```

---

## 📱 Flutter Implementation

### Step 1: Add Dependencies

```yaml
# pubspec.yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.10
  flutter_local_notifications: ^16.3.0
  http: ^1.1.0
```

### Step 2: Initialize Firebase

```dart
// main.dart
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  
  runApp(MyApp());
}
```

### Step 3: FCM Service Class

```dart
// services/fcm_service.dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class FcmService {
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final LocalNotificationsPlugin _localNotifications = LocalNotificationsPlugin();
  String? _fcmToken;
  String? _authToken;

  Future<void> initialize(String authToken) async {
    _authToken = authToken;

    // Request permission
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('✅ Notification permission granted');
      
      // Get token
      _fcmToken = await _messaging.getToken();
      print('✅ FCM Token: $_fcmToken');
      
      // Register with backend
      if (_fcmToken != null) {
        await _registerTokenWithBackend(_fcmToken!);
      }
      
      // Setup handlers
      _setupNotificationHandlers();
      _initializeLocalNotifications();
    } else {
      print('❌ Notification permission denied');
    }
  }

  Future<void> _registerTokenWithBackend(String token) async {
    try {
      final response = await http.post(
        Uri.parse('http://localhost:8000/api/fcm/register'),
        headers: {
          'Authorization': 'Bearer $_authToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'token': token,
          'platform': 'android', // or 'ios'
          'device_id': null,
        }),
      );

      if (response.statusCode == 200) {
        print('✅ Token registered successfully');
      } else {
        print('❌ Token registration failed: ${response.body}');
      }
    } catch (e) {
      print('❌ Error registering token: $e');
    }
  }

  void _setupNotificationHandlers() {
    // Foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('📬 Foreground notification: ${message.notification?.title}');
      _showLocalNotification(message);
      _handleNotificationData(message.data);
    });

    // Background messages (when app is in background)
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('📬 Background notification opened app');
      _handleNotificationData(message.data);
    });

    // App opened from terminated state
    FirebaseMessaging.instance.getInitialMessage().then((message) {
      if (message != null) {
        print('📬 App opened from notification');
        _handleNotificationData(message.data);
      }
    });
  }

  Future<void> _initializeLocalNotifications() async {
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    await _localNotifications.initialize(
      const InitializationSettings(
        android: androidSettings,
        iOS: iosSettings,
      ),
      onDidReceiveNotificationResponse: (NotificationResponse response) {
        if (response.payload != null) {
          final data = jsonDecode(response.payload!);
          _handleNotificationData(data);
        }
      },
    );

    // Create Android notification channel
    const androidChannel = AndroidNotificationChannel(
      'high_importance_channel',
      'High Importance Notifications',
      description: 'This channel is used for important notifications.',
      importance: Importance.high,
    );

    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(androidChannel);
  }

  Future<void> _showLocalNotification(RemoteMessage message) async {
    if (message.notification == null) return;

    const androidDetails = AndroidNotificationDetails(
      'high_importance_channel',
      'High Importance Notifications',
      channelDescription: 'This channel is used for important notifications.',
      importance: Importance.high,
      priority: Priority.high,
    );

    const iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    await _localNotifications.show(
      message.hashCode,
      message.notification!.title,
      message.notification!.body,
      const NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      ),
      payload: jsonEncode(message.data),
    );
  }

  void _handleNotificationData(Map<String, dynamic> data) {
    final type = data['type'];
    final meetingId = data['meeting_id'];
    final action = data['action'];

    print('📬 Notification data: type=$type, meetingId=$meetingId, action=$action');

    // Navigate based on action
    switch (action) {
      case 'view_meeting':
        // Navigate to meeting details
        // Navigator.pushNamed(context, '/meeting', arguments: meetingId);
        break;
      case 'view_calendar':
        // Navigate to calendar
        // Navigator.pushNamed(context, '/calendar');
        break;
      case 'join_meeting':
        // Navigate to join meeting
        // Navigator.pushNamed(context, '/join-meeting', arguments: meetingId);
        break;
    }
  }
}

// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  print('📬 Background notification: ${message.notification?.title}');
  // Handle background notification
}
```

### Step 4: Setup Background Handler

```dart
// main.dart
import 'package:firebase_messaging/firebase_messaging.dart';

// Must be top-level function
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('📬 Background notification: ${message.notification?.title}');
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  
  // Register background handler
  FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
  
  runApp(MyApp());
}
```

### Step 5: Initialize in App

```dart
// In your login screen or home screen
final fcmService = FcmService();
await fcmService.initialize(authToken);
```

---

## ⚛️ React Native Implementation

### Step 1: Install Dependencies

```bash
npm install @react-native-firebase/app @react-native-firebase/messaging
```

### Step 2: Setup

```javascript
// App.js
import messaging from '@react-native-firebase/messaging';

// Request permission
async function requestUserPermission() {
  const authStatus = await messaging().requestPermission();
  const enabled =
    authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
    authStatus === messaging.AuthorizationStatus.PROVISIONAL;

  if (enabled) {
    console.log('✅ Authorization status:', authStatus);
    return true;
  }
  return false;
}

// Get token
async function getFCMToken() {
  const permission = await requestUserPermission();
  if (!permission) return null;

  const token = await messaging().getToken();
  console.log('✅ FCM Token:', token);
  return token;
}

// Register with backend
async function registerToken(token) {
  const authToken = await AsyncStorage.getItem('auth_token');
  
  try {
    const response = await fetch('http://localhost:8000/api/fcm/register', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: token,
        platform: Platform.OS, // 'ios' or 'android'
      }),
    });

    const data = await response.json();
    if (data.success) {
      console.log('✅ Token registered');
    }
  } catch (error) {
    console.error('❌ Error:', error);
  }
}

// Handle foreground messages
messaging().onMessage(async remoteMessage => {
  console.log('📬 Foreground notification:', remoteMessage);
  
  // Show local notification
  // Use react-native-push-notification or similar
  
  // Handle navigation
  const { action, meeting_id } = remoteMessage.data;
  handleNotificationAction(action, meeting_id);
});

// Handle background messages
messaging().setBackgroundMessageHandler(async remoteMessage => {
  console.log('📬 Background notification:', remoteMessage);
});

// Handle notification opened app
messaging().onNotificationOpenedApp(remoteMessage => {
  console.log('📬 Notification opened app:', remoteMessage);
  const { action, meeting_id } = remoteMessage.data;
  handleNotificationAction(action, meeting_id);
});

// Check if app opened from quit state
messaging()
  .getInitialNotification()
  .then(remoteMessage => {
    if (remoteMessage) {
      console.log('📬 App opened from notification');
      const { action, meeting_id } = remoteMessage.data;
      handleNotificationAction(action, meeting_id);
    }
  });

function handleNotificationAction(action, meetingId) {
  switch (action) {
    case 'view_meeting':
      navigation.navigate('MeetingDetails', { meetingId });
      break;
    case 'view_calendar':
      navigation.navigate('Calendar');
      break;
    case 'join_meeting':
      navigation.navigate('JoinMeeting', { meetingId });
      break;
  }
}
```

---

## 🔧 Common Issues & Solutions

### Issue 1: Notifications Not Appearing

**Symptoms:**
- Backend logs show "FCM notification sent successfully"
- But device par notification nahi aa rahi

**Solutions:**

1. **Check Notification Permissions:**
   ```javascript
   // Web
   if (Notification.permission !== 'granted') {
     await Notification.requestPermission();
   }
   
   // Flutter
   NotificationSettings settings = await messaging.requestPermission();
   if (settings.authorizationStatus != AuthorizationStatus.authorized) {
     // Request again
   }
   ```

2. **Check FCM Token:**
   ```javascript
   // Verify token is registered
   const token = await getToken(messaging);
   console.log('Current token:', token);
   ```

3. **Check Service Worker (Web):**
   ```javascript
   // Verify service worker is registered
   if ('serviceWorker' in navigator) {
     const registration = await navigator.serviceWorker.ready;
     console.log('Service Worker:', registration);
   }
   ```

4. **Check Browser Console:**
   - Open DevTools → Console
   - Look for errors
   - Check Network tab for API calls

### Issue 2: Background Notifications Not Working

**Solutions:**

1. **Service Worker (Web):**
   - Ensure `firebase-messaging-sw.js` exists in `public/` folder
   - Verify service worker is registered
   - Check browser supports service workers (HTTPS required)

2. **Background Handler (Flutter):**
   - Ensure `firebaseMessagingBackgroundHandler` is top-level function
   - Must be registered before `runApp()`

### Issue 3: Token Registration Fails

**Solutions:**

1. **Check Auth Token:**
   ```javascript
   const authToken = localStorage.getItem('auth_token');
   if (!authToken) {
     console.error('❌ No auth token found');
   }
   ```

2. **Check API Endpoint:**
   ```javascript
   // Verify endpoint is correct
   const response = await fetch('http://localhost:8000/api/fcm/register', {
     // ... options
   });
   console.log('Response status:', response.status);
   ```

3. **Check Token Format:**
   ```javascript
   // FCM tokens are typically 152+ characters
   if (token.length < 100) {
     console.error('❌ Invalid token length');
   }
   ```

---

## 🧪 Testing Guide

### Test 1: Token Registration

```javascript
// After login
const token = await getFCMToken();
console.log('Token:', token);
await registerTokenWithBackend(token);
// Check backend logs for "FCM token registered"
```

### Test 2: Foreground Notification

```javascript
// Keep app open
// Create/update a meeting
// Should see notification immediately
```

### Test 3: Background Notification

```javascript
// Minimize app
// Wait for reminder time
// Should see notification in system tray
```

### Test 4: Notification Click

```javascript
// Click on notification
// Should navigate to correct screen
```

---

## 🐛 Debugging Tips

### 1. Enable Logging

```javascript
// Web
localStorage.setItem('debug', 'firebase:*');

// Flutter
// Add print statements in handlers
print('📬 Notification received: ${message.notification?.title}');
```

### 2. Check Backend Logs

```bash
# Backend logs show:
# - "FCM notification sent successfully" ✅
# - "FCM notification failed" ❌
tail -f storage/logs/laravel.log | grep FCM
```

### 3. Verify Token in Database

```sql
-- Check if token is registered
SELECT * FROM fcm_tokens WHERE user_id = 1;
```

### 4. Test with Postman

```bash
# Test notification manually
POST http://localhost:8000/api/fcm/register
Headers:
  Authorization: Bearer {token}
Body:
  {
    "token": "test_token",
    "platform": "web"
  }
```

---

## 📞 Support

Agar issues aayein to check karein:

1. **Backend Status:**
   - Queue worker running? `php artisan queue:work`
   - Scheduler running? `php artisan schedule:work`
   - Firebase configured? Check logs

2. **Frontend Status:**
   - Token registered?
   - Permissions granted?
   - Service worker active? (Web)
   - Handlers setup?

3. **Common Fixes:**
   - Clear browser cache (Web)
   - Reinstall app (Mobile)
   - Re-register token
   - Check console for errors

---

## ✅ Final Checklist

Before going to production:

- [ ] All handlers implemented
- [ ] Permissions requested properly
- [ ] Token registered on login
- [ ] Token unregistered on logout
- [ ] Foreground notifications working
- [ ] Background notifications working
- [ ] Notification click navigation working
- [ ] Error handling implemented
- [ ] Tested on all platforms
- [ ] Service worker configured (Web)
- [ ] HTTPS enabled (Web - required for service workers)

---

**Last Updated:** 2025-12-23
**Backend API:** http://localhost:8000/api
**Firebase Project:** meeting-53f24







