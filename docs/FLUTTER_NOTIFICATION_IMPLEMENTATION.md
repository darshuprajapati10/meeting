# Flutter Push Notification Implementation Guide

## Overview

This guide provides step-by-step instructions for implementing Firebase Cloud Messaging (FCM) push notifications in your Flutter application.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Setup & Installation](#setup--installation)
3. [Android Configuration](#android-configuration)
4. [iOS Configuration](#ios-configuration)
5. [Implementation Steps](#implementation-steps)
6. [Notification Handling](#notification-handling)
7. [Navigation Logic](#navigation-logic)
8. [Error Handling](#error-handling)
9. [Testing](#testing)
10. [Troubleshooting](#troubleshooting)
11. [API Reference](#api-reference)

---

## Prerequisites

- Flutter SDK installed
- Firebase project created
- Android Studio / Xcode installed
- Backend API running with FCM endpoints

---

## Setup & Installation

### 1. Add Dependencies

Add the following to your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.10
  http: ^1.1.0
  shared_preferences: ^2.2.2
  flutter_local_notifications: ^16.3.0
  device_info_plus: ^9.1.0
  get: ^4.6.6  # Optional: If using GetX for navigation
```

**Note**: 
- `flutter_local_notifications` - For showing notifications when app is in foreground
- `device_info_plus` - For getting device ID
- `get` - Optional, only if you're using GetX for state management/navigation

Run:
```bash
flutter pub get
```

### 2. Initialize Firebase

In your `main.dart`:

```dart
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  
  runApp(MyApp());
}
```

Generate Firebase options:
```bash
flutterfire configure
```

---

## Android Configuration

### 1. Add google-services.json

1. Download `google-services.json` from Firebase Console
2. Place it in `android/app/` directory

### 2. Update android/app/build.gradle

```gradle
dependencies {
    // ... other dependencies
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-messaging'
}

apply plugin: 'com.google.gms.google-services'
```

### 3. Update android/build.gradle

```gradle
buildscript {
    dependencies {
        // ... other dependencies
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

### 4. Update AndroidManifest.xml

Add to `android/app/src/main/AndroidManifest.xml`:

```xml
<manifest>
    <application>
        <!-- ... other configurations -->
        
        <!-- FCM default notification channel -->
        <meta-data
            android:name="com.google.firebase.messaging.default_notification_channel_id"
            android:value="high_importance_channel" />
    </application>
</manifest>
```

---

## iOS Configuration

### 1. Add GoogleService-Info.plist

1. Download `GoogleService-Info.plist` from Firebase Console
2. Add it to `ios/Runner/` in Xcode
3. Ensure it's added to the target

### 2. Enable Push Notifications

1. Open `ios/Runner.xcworkspace` in Xcode
2. Select Runner target
3. Go to "Signing & Capabilities"
4. Click "+ Capability"
5. Add "Push Notifications"
6. Add "Background Modes" and enable "Remote notifications"

### 3. Update AppDelegate.swift

```swift
import UIKit
import Flutter
import Firebase

@UIApplicationMain
@objc class AppDelegate: FlutterAppDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    FirebaseApp.configure()
    GeneratedPluginRegistrant.register(with: self)
    
    if #available(iOS 10.0, *) {
      UNUserNotificationCenter.current().delegate = self as UNUserNotificationCenterDelegate
    }
    
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }
}
```

---

## Implementation Steps

### 1. Create App Configuration

Create `lib/config/app_config.dart` for API URL management:

```dart
class AppConfig {
  // Development URL
  static const String devApiUrl = 'http://localhost:8000/api';
  
  // Production URL - Update this with your production API
  static const String prodApiUrl = 'https://your-production-api.com/api';
  
  // Use environment variable or default to dev
  static String get apiBaseUrl {
    const envUrl = String.fromEnvironment('API_BASE_URL');
    if (envUrl.isNotEmpty) {
      return envUrl;
    }
    // Change to prodApiUrl for production builds
    return devApiUrl;
  }
}
```

### 2. Create FCM Service Class

Create `lib/services/fcm_service.dart`:

```dart
import 'dart:async';
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart'; // Optional: Only if using GetX
import '../config/app_config.dart';

class FcmService {
  static final FcmService _instance = FcmService._internal();
  factory FcmService() => _instance;
  FcmService._internal();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications = 
      FlutterLocalNotificationsPlugin();
  final DeviceInfoPlugin _deviceInfo = DeviceInfoPlugin();
  
  String? _fcmToken;
  String? _authToken;
  
  // Global navigator key for navigation (if using Navigator)
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  // Initialize FCM
  Future<void> initialize() async {
    // Initialize local notifications
    await _initializeLocalNotifications();
    
    // Load saved auth token
    final prefs = await SharedPreferences.getInstance();
    _authToken = prefs.getString('auth_token');
    
    // Request permission
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('User granted permission');
    } else if (settings.authorizationStatus == AuthorizationStatus.provisional) {
      print('User granted provisional permission');
    } else {
      print('User declined or has not accepted permission');
      return;
    }

    // Get FCM token
    final token = await getFcmToken();
    if (token == null || token.isEmpty) {
      print('⚠️ WARNING: FCM token not received. Notifications may not work.');
    }

    // Handle token refresh
    _messaging.onTokenRefresh.listen((newToken) {
      if (newToken != null && newToken.isNotEmpty) {
        print('🔄 FCM token refreshed: ${newToken.substring(0, 20)}...');
        _fcmToken = newToken;
        if (_authToken != null && _authToken!.isNotEmpty) {
          registerTokenWithBackend(newToken);
        }
      } else {
        print('❌ ERROR: Refreshed token is null or empty');
      }
    });

    // Setup notification handlers
    _setupNotificationHandlers();
  }

  // Initialize local notifications
  Future<void> _initializeLocalNotifications() async {
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse response) {
        // Handle notification tap (when user taps on notification)
        if (response.payload != null) {
          try {
            final data = jsonDecode(response.payload!);
            _handleNavigation(data);
          } catch (e) {
            print('Error parsing notification payload: $e');
          }
        }
      },
    );

    // Create notification channel for Android
    if (Platform.isAndroid) {
      const androidChannel = AndroidNotificationChannel(
        'high_importance_channel',
        'High Importance Notifications',
        description: 'This channel is used for important notifications.',
        importance: Importance.high,
        playSound: true,
      );

      await _localNotifications
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(androidChannel);
    }
  }

  // Get FCM token
  Future<String?> getFcmToken() async {
    try {
      _fcmToken = await _messaging.getToken();
      
      // CRITICAL: Validate token before proceeding
      if (_fcmToken == null || _fcmToken!.isEmpty) {
        print('❌ ERROR: FCM token is null or empty');
        return null;
      }
      
      print('✅ FCM Token received: ${_fcmToken!.substring(0, 20)}... (length: ${_fcmToken!.length})');
      
      // Register with backend if user is logged in
      if (_authToken != null && _authToken!.isNotEmpty && _fcmToken != null && _fcmToken!.isNotEmpty) {
        print('🔵 User is logged in, registering token with backend...');
        await registerTokenWithBackend(_fcmToken!);
      } else {
        print('⚠️ User not logged in yet. Token will be registered after login.');
      }
      
      return _fcmToken;
    } catch (e) {
      print('❌ ERROR getting FCM token: $e');
      return null;
    }
  }

  // Set auth token (call after login)
  Future<void> setAuthToken(String token) async {
    if (token == null || token.isEmpty || token.trim().isEmpty) {
      print('❌ ERROR: Auth token is null or empty');
      return;
    }

    _authToken = token.trim();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', _authToken!);
    
    print('✅ Auth token set. Registering FCM token...');
    
    // Register FCM token if available
    if (_fcmToken != null && _fcmToken!.isNotEmpty) {
      print('🔵 FCM token already available, registering with backend...');
      await registerTokenWithBackend(_fcmToken!);
    } else {
      print('🔵 FCM token not available, fetching new token...');
      await getFcmToken();
    }
  }

  // Clear auth token (call on logout)
  Future<void> clearAuthToken() async {
    _authToken = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    
    // Unregister FCM token
    if (_fcmToken != null) {
      await unregisterToken(_fcmToken!);
    }
  }

  // Register FCM token with backend (with retry logic)
  Future<bool> registerTokenWithBackend(String token, {int retries = 3}) async {
    // CRITICAL: Validate token before sending
    if (token == null || token.isEmpty || token.trim().isEmpty) {
      print('❌ ERROR: FCM token is null or empty. Cannot register.');
      return false;
    }

    if (_authToken == null || _authToken!.isEmpty) {
      print('❌ ERROR: Auth token not available, skipping token registration');
      return false;
    }

    print('🔵 Registering FCM token with backend...');
    print('🔵 Token length: ${token.length}');
    print('🔵 Platform: ${_getPlatform()}');
    print('🔵 API URL: ${AppConfig.apiBaseUrl}/fcm/register');

    for (int i = 0; i < retries; i++) {
      try {
        final platform = _getPlatform();
        final deviceId = await _getDeviceId();
        
        // Prepare request body
        final requestBody = {
          'token': token.trim(), // Ensure no whitespace
          'platform': platform,
          if (deviceId != null && deviceId.isNotEmpty) 'device_id': deviceId,
        };

        print('🔵 Request body: ${jsonEncode(requestBody)}');

        final response = await http.post(
          Uri.parse('${AppConfig.apiBaseUrl}/fcm/register'),
          headers: {
            'Authorization': 'Bearer $_authToken',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode(requestBody),
        ).timeout(Duration(seconds: 10));

        print('🔵 Response status: ${response.statusCode}');
        print('🔵 Response body: ${response.body}');

        if (response.statusCode == 200) {
          final data = jsonDecode(response.body);
          print('✅ FCM token registered successfully: ${data['message']}');
          return true;
        } else if (response.statusCode == 401) {
          // Unauthorized - token expired
          print('❌ Auth token expired, please login again');
          await clearAuthToken();
          return false;
        } else if (response.statusCode == 422) {
          // Validation error
          final errorData = jsonDecode(response.body);
          print('❌ Validation error (attempt ${i + 1}/$retries): ${errorData['errors']}');
          print('❌ Request was: token=${token.substring(0, 20)}..., platform=${_getPlatform()}');
          // Don't retry on validation errors
          return false;
        } else {
          print('❌ Failed to register FCM token (attempt ${i + 1}/$retries): ${response.body}');
          if (i < retries - 1) {
            await Future.delayed(Duration(seconds: 2 * (i + 1)));
            continue;
          }
          return false;
        }
      } catch (e) {
        print('❌ ERROR registering FCM token (attempt ${i + 1}/$retries): $e');
        if (i < retries - 1) {
          await Future.delayed(Duration(seconds: 2 * (i + 1)));
          continue;
        }
        return false;
      }
    }
    return false;
  }

  // Unregister FCM token
  Future<bool> unregisterToken(String token) async {
    if (_authToken == null) {
      return false;
    }

    try {
      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/fcm/unregister'),
        headers: {
          'Authorization': 'Bearer $_authToken',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'token': token,
        }),
      ).timeout(Duration(seconds: 10));

      if (response.statusCode == 200) {
        print('FCM token unregistered');
        return true;
      } else {
        print('Failed to unregister FCM token: ${response.body}');
        return false;
      }
    } catch (e) {
      print('Error unregistering FCM token: $e');
      return false;
    }
  }

  // Run diagnostic to check all components
  static Future<Map<String, dynamic>> runDiagnostic() async {
    final results = <String, dynamic>{};
    
    print('\n=== FCM Service Diagnostic ===\n');
    
    // 1. Check Firebase Initialization
    try {
      final app = Firebase.app();
      results['firebase_initialized'] = true;
      results['firebase_name'] = app.name;
      print('✅ Firebase Initialized: ${app.name}');
    } catch (e) {
      results['firebase_initialized'] = false;
      results['firebase_error'] = e.toString();
      print('❌ Firebase NOT Initialized: $e');
    }
    
    // 2. Check Notification Permissions
    try {
      final messaging = FirebaseMessaging.instance;
      final settings = await messaging.getNotificationSettings();
      results['permission_status'] = settings.authorizationStatus.toString();
      results['permission_granted'] = settings.authorizationStatus == AuthorizationStatus.authorized;
      
      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        print('✅ Notification Permission: GRANTED');
      } else {
        print('❌ Notification Permission: ${settings.authorizationStatus}');
      }
    } catch (e) {
      results['permission_error'] = e.toString();
      print('❌ Permission Check Failed: $e');
    }
    
    // 3. Check FCM Token
    try {
      final instance = FcmService();
      final token = await instance.getFcmToken();
      
      if (token != null && token.isNotEmpty) {
        results['fcm_token_exists'] = true;
        results['fcm_token_length'] = token.length;
        results['fcm_token_preview'] = '${token.substring(0, 20)}...';
        print('✅ FCM Token: EXISTS (length: ${token.length})');
        print('   Token preview: ${token.substring(0, 20)}...');
      } else {
        results['fcm_token_exists'] = false;
        print('❌ FCM Token: NOT RECEIVED');
      }
    } catch (e) {
      results['fcm_token_error'] = e.toString();
      print('❌ FCM Token Error: $e');
    }
    
    // 4. Check Auth Token
    try {
      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString('auth_token');
      
      if (authToken != null && authToken.isNotEmpty) {
        results['auth_token_exists'] = true;
        results['auth_token_length'] = authToken.length;
        print('✅ Auth Token: EXISTS (length: ${authToken.length})');
      } else {
        results['auth_token_exists'] = false;
        print('⚠️ Auth Token: NOT SET (Login required)');
      }
    } catch (e) {
      results['auth_token_error'] = e.toString();
      print('❌ Auth Token Check Failed: $e');
    }
    
    // 5. Check Backend Connection
    try {
      final apiUrl = AppConfig.apiBaseUrl;
      results['api_url'] = apiUrl;
      print('✅ API URL: $apiUrl');
    } catch (e) {
      results['backend_error'] = e.toString();
      print('⚠️ Backend Check: $e');
    }
    
    // 6. Check Local Notifications
    try {
      final localNotifications = FlutterLocalNotificationsPlugin();
      final android = await localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
      
      if (android != null || Platform.isIOS) {
        results['local_notifications_available'] = true;
        print('✅ Local Notifications: AVAILABLE');
      } else {
        results['local_notifications_available'] = false;
        print('⚠️ Local Notifications: NOT AVAILABLE');
      }
    } catch (e) {
      results['local_notifications_error'] = e.toString();
      print('❌ Local Notifications Check Failed: $e');
    }
    
    // 7. Summary
    print('\n=== Diagnostic Summary ===');
    final allGood = results['firebase_initialized'] == true &&
                    results['permission_granted'] == true &&
                    results['fcm_token_exists'] == true;
    
    if (allGood) {
      print('✅ All checks passed!');
      results['status'] = 'SUCCESS';
    } else {
      print('⚠️ Some checks failed. See details above.');
      results['status'] = 'PARTIAL';
    }
    
    print('\n');
    return results;
  }

  // Setup notification handlers
  void _setupNotificationHandlers() {
    // Foreground notifications
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('Foreground notification received: ${message.notification?.title}');
      _handleNotification(message, isForeground: true);
    });

    // Background notifications (when app is in background)
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('Notification opened app: ${message.notification?.title}');
      _handleNotification(message, isForeground: false);
    });

    // Terminated app notifications
    FirebaseMessaging.instance.getInitialMessage().then((RemoteMessage? message) {
      if (message != null) {
        print('Notification opened terminated app: ${message.notification?.title}');
        _handleNotification(message, isForeground: false);
      }
    });
  }

  // Handle notification
  void _handleNotification(RemoteMessage message, {required bool isForeground}) {
    final data = message.data;
    final notification = message.notification;

    if (notification != null) {
      // Show local notification if app is in foreground
      if (isForeground) {
        _showLocalNotification(notification, data);
        // Don't navigate immediately - wait for user to tap notification
        // Navigation will be handled by onDidReceiveNotificationResponse
      } else {
        // App is in background or terminated - navigate when opened
        _handleNavigation(data);
      }
    }
  }

  // Show local notification (for foreground)
  Future<void> _showLocalNotification(
    RemoteNotification notification,
    Map<String, dynamic> data,
  ) async {
    const androidDetails = AndroidNotificationDetails(
      'high_importance_channel',
      'High Importance Notifications',
      channelDescription: 'This channel is used for important notifications.',
      importance: Importance.high,
      priority: Priority.high,
      showWhen: true,
    );

    const iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    const notificationDetails = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      notificationDetails,
      payload: jsonEncode(data),
    );
  }

  // Handle navigation based on notification data
  void _handleNavigation(Map<String, dynamic> data) {
    final type = data['type'];
    // Convert meeting_id to String (backend sends it as int, but we need String)
    final meetingId = data['meeting_id']?.toString();
    final action = data['action'];

    // Navigate based on notification type
    switch (type) {
      case 'meeting_created':
      case 'meeting_updated':
      case 'meeting_reminder':
      case 'meeting_starting':
        if (action == 'view_meeting' && meetingId != null && meetingId.isNotEmpty) {
          // Navigate to meeting details
          _navigateToMeeting(meetingId);
        }
        break;
      case 'meeting_cancelled':
        if (action == 'view_calendar') {
          // Navigate to calendar
          _navigateToCalendar();
        }
        break;
    }
  }

  // Navigate to meeting details
  void _navigateToMeeting(String meetingId) {
    print('Navigate to meeting: $meetingId');
    
    // Option 1: Using GetX (if using GetX)
    try {
      // Check if GetX is available
      Get.toNamed('/meeting-details', arguments: {'meeting_id': meetingId});
      return;
    } catch (e) {
      // GetX not available, use Navigator
      print('GetX not available, using Navigator: $e');
    }
    
    // Option 2: Using Navigator with global key
    if (navigatorKey.currentState != null) {
      navigatorKey.currentState!.pushNamed(
        '/meeting-details',
        arguments: {'meeting_id': meetingId},
      );
    } else {
      print('Navigator key not initialized. Cannot navigate to meeting.');
    }
  }

  // Navigate to calendar
  void _navigateToCalendar() {
    print('Navigate to calendar');
    
    // Option 1: Using GetX (if using GetX)
    try {
      // Check if GetX is available
      Get.toNamed('/calendar');
      return;
    } catch (e) {
      // GetX not available, use Navigator
      print('GetX not available, using Navigator: $e');
    }
    
    // Option 2: Using Navigator with global key
    if (navigatorKey.currentState != null) {
      navigatorKey.currentState!.pushNamed('/calendar');
    } else {
      print('Navigator key not initialized. Cannot navigate to calendar.');
    }
  }

  // Get platform
  String _getPlatform() {
    if (Platform.isAndroid) {
      return 'android';
    } else if (Platform.isIOS) {
      return 'ios';
    } else {
      return 'web';
    }
  }

  // Get device ID
  Future<String?> _getDeviceId() async {
    try {
      if (Platform.isAndroid) {
        final androidInfo = await _deviceInfo.androidInfo;
        return androidInfo.id; // or androidInfo.androidId for unique ID
      } else if (Platform.isIOS) {
        final iosInfo = await _deviceInfo.iosInfo;
        return iosInfo.identifierForVendor;
      }
      return null;
    } catch (e) {
      print('Error getting device ID: $e');
      return null;
    }
  }
}

```

### 3. Create Background Handler File

**IMPORTANT**: Background handler must be in a separate file as a top-level function.

Create `lib/services/fcm_background_handler.dart`:

```dart
import 'dart:convert';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'firebase_options.dart';

// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Firebase must be initialized in background handler
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  
  print('Background message received: ${message.messageId}');
  print('Title: ${message.notification?.title}');
  print('Body: ${message.notification?.body}');
  print('Data: ${message.data}');
  
  // Initialize local notifications
  final FlutterLocalNotificationsPlugin localNotifications = 
      FlutterLocalNotificationsPlugin();
  
  const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
  const iosSettings = DarwinInitializationSettings();
  const initSettings = InitializationSettings(
    android: androidSettings,
    iOS: iosSettings,
  );
  
  await localNotifications.initialize(initSettings);
  
  // Create Android notification channel
  const androidChannel = AndroidNotificationChannel(
    'high_importance_channel',
    'High Importance Notifications',
    description: 'This channel is used for important notifications.',
    importance: Importance.high,
    playSound: true,
  );
  
  await localNotifications
      .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
      ?.createNotificationChannel(androidChannel);
  
  // Show notification
  if (message.notification != null) {
    const androidDetails = AndroidNotificationDetails(
      'high_importance_channel',
      'High Importance Notifications',
      channelDescription: 'This channel is used for important notifications.',
      importance: Importance.high,
      priority: Priority.high,
      showWhen: true,
    );
    
    const iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );
    
    const notificationDetails = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );
    
    await localNotifications.show(
      message.hashCode,
      message.notification!.title,
      message.notification!.body,
      notificationDetails,
      payload: jsonEncode(message.data),
    );
  }
}
```

### 4. Update main.dart

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart'; // Optional: Only if using GetX
import 'firebase_options.dart';
import 'services/fcm_service.dart';
import 'services/fcm_background_handler.dart'; // Import background handler

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  // Setup background message handler (MUST be called before runApp)
  // This must be a top-level function
  FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

  // Initialize FCM service
  await FcmService().initialize();

  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    // Option 1: Using GetX
    return GetMaterialApp(
      navigatorKey: FcmService.navigatorKey, // For navigation from FcmService
      title: 'My App',
      // Add your routes here
      // getPages: [
      //   GetPage(name: '/meeting-details', page: () => MeetingDetailsPage()),
      //   GetPage(name: '/calendar', page: () => CalendarPage()),
      // ],
      // ... rest of your app config
    );
    
    // Option 2: Using standard MaterialApp
    // return MaterialApp(
    //   navigatorKey: FcmService.navigatorKey, // For navigation from FcmService
    //   title: 'My App',
    //   routes: {
    //     '/meeting-details': (context) => MeetingDetailsPage(),
    //     '/calendar': (context) => CalendarPage(),
    //   },
    //   // ... rest of your app config
    // );
  }
}
```

### 5. Initialize After Login

**CRITICAL**: Ye step zaroor follow karein, warna notifications kaam nahi karengi!

In your login screen or auth service:

```dart
// After successful login
final response = await loginApi();
if (response['success'] == true) {
  final authToken = response['token']; // or response['data']['token']
  
  // IMPORTANT: Auth token validate karein
  if (authToken == null || authToken.toString().isEmpty) {
    print('❌ ERROR: Auth token is null or empty');
    return;
  }
  
  // IMPORTANT: Ye line zaroor call karein
  await FcmService().setAuthToken(authToken.toString());
  
  print('✅ FCM service initialized after login');
  
  // The FCM token will be automatically registered with backend
} else {
  print('❌ Login failed: ${response['message']}');
}
```

**Common Mistakes to Avoid:**
1. ❌ `setAuthToken` call nahi karna
2. ❌ Auth token null/empty bhejna
3. ❌ Login ke pehle `setAuthToken` call karna (pehle login, phir setAuthToken)
4. ❌ Token ko string me convert nahi karna

### 6. Clear on Logout

In your logout function:

```dart
// On logout
await FcmService().clearAuthToken();
// This will unregister the FCM token from backend
```

---

## Notification Handling

### Foreground Notifications

When app is in foreground, notifications are handled by `FirebaseMessaging.onMessage`:

```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  // Show in-app notification
  // Navigate if needed
});
```

### Background Notifications

When app is in background, notifications are handled by `FirebaseMessaging.onMessageOpenedApp`:

```dart
FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
  // Navigate to relevant screen
});
```

### Terminated App Notifications

When app is terminated, check for initial message:

```dart
FirebaseMessaging.instance.getInitialMessage().then((RemoteMessage? message) {
  if (message != null) {
    // Navigate to relevant screen
  }
});
```

---

## Navigation Logic

### Notification Data Structure

Backend sends notifications with this data structure:

```json
{
  "type": "meeting_created|meeting_updated|meeting_cancelled|meeting_reminder|meeting_starting",
  "meeting_id": 123,
  "action": "view_meeting|view_calendar|join_meeting"
}
```

**Note**: Backend sends `meeting_id` as an integer, but Flutter code converts it to String for navigation. This is handled automatically in the `_handleNavigation` method.

### Navigation Implementation

```dart
void _handleNavigation(Map<String, dynamic> data) {
  final type = data['type'];
  // Convert meeting_id to String (backend sends it as int, but we need String)
  final meetingId = data['meeting_id']?.toString();
  final action = data['action'];

  switch (type) {
    case 'meeting_created':
    case 'meeting_updated':
    case 'meeting_reminder':
    case 'meeting_starting':
      if (action == 'view_meeting' && meetingId != null && meetingId.isNotEmpty) {
        Navigator.pushNamed(
          context,
          '/meeting-details',
          arguments: {'meeting_id': meetingId},
        );
      }
      break;
      
    case 'meeting_cancelled':
      if (action == 'view_calendar') {
        Navigator.pushNamed(context, '/calendar');
      }
      break;
  }
}
```

---

## Error Handling

### Token Registration Errors (Already Implemented)

The `registerTokenWithBackend` method already includes:
- ✅ Retry logic (3 attempts with exponential backoff)
- ✅ Timeout handling (10 seconds)
- ✅ 401 Unauthorized handling (clears auth token)
- ✅ Network error handling

### Network Connectivity Check

Optional: Add connectivity check before API calls:

```dart
// Add to pubspec.yaml
dependencies:
  connectivity_plus: ^5.0.2

// In FcmService
import 'package:connectivity_plus/connectivity_plus.dart';

Future<bool> checkConnectivity() async {
  final connectivityResult = await Connectivity().checkConnectivity();
  return connectivityResult != ConnectivityResult.none;
}
```

### Error Logging

For production, consider using a logging package:

```dart
// Add to pubspec.yaml
dependencies:
  logger: ^2.0.2

// Use in FcmService
import 'package:logger/logger.dart';

final _logger = Logger();

// Instead of print
_logger.i('FCM token registered');
_logger.e('Error registering token', error: e);
```

---

## Testing & Diagnostics

### Complete Testing Checklist

#### 1. Run App & Check Console Logs

**Step 1**: App start karein aur console logs check karein:

```dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  
  // Initialize FCM Service
  await FcmService().initialize();
  
  runApp(MyApp());
}
```

**Expected Console Logs**:
```
✅ User granted permission
✅ FCM Token received: dXJ... (length: 152)
⚠️ User not logged in yet. Token will be registered after login.
```

**After Login**:
```
🔵 User is logged in, registering token with backend...
🔵 Registering FCM token with backend...
✅ FCM token registered successfully
```

---

#### 2. Run Diagnostic Method

**Call diagnostic method after app initialization**:

```dart
// After app starts, call diagnostic
void initState() {
  super.initState();
  _runDiagnostic();
}

Future<void> _runDiagnostic() async {
  final diagnostic = await FcmService.runDiagnostic();
  print('Diagnostic Results: $diagnostic');
}
```

**Expected Diagnostic Output**:
```
=== FCM Service Diagnostic ===

✅ Firebase Initialized: [DEFAULT]
✅ Notification Permission: GRANTED
✅ FCM Token: EXISTS (length: 152)
   Token preview: dXJhbmRvbXRva2Vu...
✅ Auth Token: EXISTS (length: 45)
✅ API URL: http://localhost:8000
✅ Local Notifications: AVAILABLE

=== Diagnostic Summary ===
✅ All checks passed!
```

**If Issues Found**:
```
=== FCM Service Diagnostic ===

✅ Firebase Initialized: [DEFAULT]
❌ Notification Permission: denied
❌ FCM Token: NOT RECEIVED
⚠️ Auth Token: NOT SET (Login required)
✅ API URL: http://localhost:8000

=== Diagnostic Summary ===
⚠️ Some checks failed. See details above.
```

---

#### 3. Check Backend Status

**A. Check Queue Worker**:

**Windows PowerShell**:
```powershell
# Check if queue worker is running
Get-Process | Where-Object {$_.ProcessName -like "*php*"}

# Start queue worker if not running (in separate terminal):
php artisan queue:work
```

**Linux/Mac**:
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Start queue worker if not running (in separate terminal):
php artisan queue:work
```

**B. Check Scheduler**:

**Windows PowerShell**:
```powershell
# Check if scheduler is running
Get-Process | Where-Object {$_.ProcessName -like "*php*"}

# Start scheduler if not running (in separate terminal):
php artisan schedule:work
```

**Linux/Mac**:
```bash
# Check if scheduler is running
ps aux | grep "schedule:work"

# Start scheduler if not running (in separate terminal):
php artisan schedule:work
```

**C. Run Backend Verification Script**:

**Windows PowerShell**:
```powershell
.\check-backend-status.ps1
```

**Linux/Mac**:
```bash
./check-backend-status.sh
```

**Or PHP Script**:
```bash
php verify-notification-system.php
```

**Expected Output**:
```
✅ Queue Worker: RUNNING
✅ Scheduler: RUNNING
✅ Firebase Configuration: COMPLETE
✅ Database Tables: ALL EXIST
```

---

#### 4. Test Notifications from Firebase Console

**Step 1**: Firebase Console me jao:
1. Firebase Console → Project Settings → Cloud Messaging
2. "Send test message" click karein

**Step 2**: Test notification send karein:

**For Testing Token Registration**:
```json
{
  "notification": {
    "title": "Test Notification",
    "body": "Testing FCM integration"
  },
  "data": {
    "type": "test",
    "action": "view_test"
  }
}
```

**For Testing Meeting Notification**:
```json
{
  "notification": {
    "title": "Meeting Created",
    "body": "New meeting scheduled"
  },
  "data": {
    "type": "meeting_created",
    "meeting_id": "123",
    "action": "view_meeting"
  }
}
```

**Step 3**: Check console logs:
```
✅ Notification received in foreground
✅ Notification data: {type: meeting_created, meeting_id: 123, action: view_meeting}
✅ Navigating to meeting: 123
```

---

#### 5. Share Results - Console Logs

**Complete Log Example (Success)**:
```
=== FCM Service Diagnostic ===

✅ Firebase Initialized: [DEFAULT]
✅ Notification Permission: GRANTED
✅ FCM Token: EXISTS (length: 152)
   Token preview: dXJhbmRvbXRva2Vu...
✅ Auth Token: EXISTS (length: 45)
✅ API URL: http://localhost:8000
✅ Local Notifications: AVAILABLE

=== Diagnostic Summary ===
✅ All checks passed!

✅ FCM Token received: dXJhbmRvbXRva2Vu... (length: 152)
🔵 User is logged in, registering token with backend...
🔵 Registering FCM token with backend...
✅ FCM token registered successfully

✅ Notification received in foreground
✅ Notification data: {type: meeting_created, meeting_id: 123}
✅ Navigating to meeting: 123
```

**Error Log Example**:
```
=== FCM Service Diagnostic ===

✅ Firebase Initialized: [DEFAULT]
❌ Notification Permission: denied
❌ FCM Token: NOT RECEIVED
⚠️ Auth Token: NOT SET (Login required)
✅ API URL: http://localhost:8000

=== Diagnostic Summary ===
⚠️ Some checks failed. See details above.

❌ ERROR: FCM token is null or empty
❌ ERROR: User declined or has not accepted permission
```

---

### Quick Testing Commands

**1. Run Diagnostic**:
```dart
await FcmService.runDiagnostic();
```

**2. Test Token Registration**:
```dart
final token = await FcmService().getFcmToken();
print('Token: $token');
```

**3. Test Backend Registration**:
```dart
final success = await FcmService().registerTokenWithBackend(token);
print('Registration: $success');
```

**4. Test Notification Handling**:
```dart
// Send test notification from Firebase Console
// Check console logs for notification data
```

---

### Test Token Registration

**IMPORTANT**: Pehle login karein, phir token register karein.

```dart
// Step 1: Login first
final loginResponse = await loginApi();
if (loginResponse['success']) {
  final authToken = loginResponse['token'];
  
  // Step 2: Set auth token (CRITICAL STEP)
  await FcmService().setAuthToken(authToken);
  
  // Step 3: Get FCM token
  final fcmToken = await FcmService().getFcmToken();
  
  if (fcmToken != null && fcmToken.isNotEmpty) {
    print('✅ FCM Token received: ${fcmToken.substring(0, 20)}...');
    print('✅ Token length: ${fcmToken.length}');
  } else {
    print('❌ ERROR: FCM token is null or empty');
  }
  
  // Step 4: Check console logs for registration status
  // You should see:
  // ✅ FCM Token received: ...
  // 🔵 Registering FCM token with backend...
  // ✅ FCM token registered successfully
}
```

### Test Token Registration Manually

Agar automatic registration fail ho, manually test karein:

```dart
final fcmService = FcmService();
final token = await fcmService.getFcmToken();

if (token != null) {
  // Manually register
  final success = await fcmService.registerTokenWithBackend(token);
  print('Registration success: $success');
}
```

### Test Notification Reception

1. Send test notification from Firebase Console
2. Check logs for notification data
3. Verify navigation works

### Test All App States

- **Foreground**: App is open
- **Background**: App is minimized
- **Terminated**: App is closed

---

### Testing Checklist

- [ ] App starts without errors
- [ ] Console shows "Firebase Initialized"
- [ ] Console shows "Notification Permission: GRANTED"
- [ ] Console shows "FCM Token received"
- [ ] After login, console shows "FCM token registered successfully"
- [ ] Diagnostic method runs successfully
- [ ] Backend queue worker is running
- [ ] Backend scheduler is running
- [ ] Test notification received from Firebase Console
- [ ] Notification navigation works correctly

---

## Troubleshooting

### Issue: Token not received

**Solution**:
- Check Firebase configuration files
- Verify permissions are granted
- Check device internet connection

### Issue: Notifications not received

**Solution**:
- Verify token is registered with backend
- Check backend queue worker is running
- Check Firebase Console for delivery status

### Issue: Navigation not working

**Solution**:
- Verify notification data structure
- Check navigation routes are defined
- Ensure context is available

### Issue: Background notifications not working

**Solution**:
- Verify background handler is registered
- Check iOS capabilities are enabled
- Ensure background handler is top-level function

---

## API Reference

### FCM Token Registration API

#### Endpoint
```
POST {{APP_URL}}/api/fcm/register
```

**Note:** `{{APP_URL}}` ko apne actual API URL se replace karein. Example: `http://localhost:8000` ya `https://api.yourapp.com`

#### Authentication
Bearer Token required. Auth token login API se milta hai.

#### Request Headers
```
Authorization: Bearer {auth_token}
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "token": "fcm_device_token_here",  // Required: FCM token from Firebase
  "platform": "android",             // Required: "ios", "android", or "web"
  "device_id": "optional_device_id"  // Optional: Device identifier
}
```

**Field Details:**
- `token` (required, string): FCM device token jo Firebase se milta hai
- `platform` (required, enum): Device platform - `"ios"`, `"android"`, ya `"web"`
- `device_id` (optional, string): Device identifier (max 255 characters)

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "FCM token registered successfully"
}
```

#### Error Responses

**400 Bad Request - Invalid Token Format:**
```json
{
  "success": false,
  "message": "Invalid FCM token format"
}
```
*Cause: FCM token format invalid hai (length < 100 characters)*

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```
*Cause: Auth token expired ya invalid hai*

**422 Unprocessable Entity - Validation Error:**
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
*Cause: Required fields missing ya invalid values*

**500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Failed to register FCM token: {error_message}"
}
```
*Cause: Server-side error (database issue, etc.)*

#### Flutter Implementation

**Automatic Registration (Recommended):**
```dart
// Login ke baad automatically call hota hai
await FcmService().setAuthToken(authToken);
// Ye internally registerTokenWithBackend() call karta hai
```

**Manual Registration (If Needed):**
```dart
final fcmService = FcmService();
final token = await fcmService.getFcmToken();

if (token != null && token.isNotEmpty) {
  final success = await fcmService.registerTokenWithBackend(token);
  if (success) {
    print('Token registered successfully');
  } else {
    print('Token registration failed');
  }
}
```

#### Complete Flutter Example
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<Map<String, dynamic>> registerFcmToken(
  String fcmToken,
  String authToken,
  String platform,
  String? deviceId,
) async {
  final response = await http.post(
    Uri.parse('${AppConfig.apiBaseUrl}/fcm/register'),
    headers: {
      'Authorization': 'Bearer $authToken',
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      'token': fcmToken,
      'platform': platform,
      if (deviceId != null) 'device_id': deviceId,
    }),
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Failed to register token: ${response.body}');
  }
}

// Usage:
try {
  final result = await registerFcmToken(
    'your_fcm_token',
    'your_auth_token',
    'android',
    'device_123',
  );
  print('Success: ${result['message']}');
} catch (e) {
  print('Error: $e');
}
```

#### Postman/HTTP Testing

**Request:**
```http
POST http://localhost:8000/api/fcm/register
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "token": "dK8xYz9aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abcdefghijklmnopqrstuvwxyz",
  "platform": "android",
  "device_id": "device_12345"
}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token registered successfully"
}
```

#### Status Codes Summary

| Code | Status | Meaning |
|------|--------|---------|
| 200 | OK | Token successfully registered |
| 400 | Bad Request | Invalid FCM token format |
| 401 | Unauthorized | Auth token expired/invalid |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server-side error |

#### Common Issues & Solutions

**Issue: 422 Error - "The token field is required"**
- **Cause:** Token null ya empty hai
- **Solution:** 
  ```dart
  // Check token before sending
  final token = await FcmService().getFcmToken();
  if (token == null || token.isEmpty) {
    print('❌ ERROR: FCM token is null or empty');
    return;
  }
  ```

**Issue: 401 Unauthorized**
- **Cause:** Auth token expired
- **Solution:** User ko dobara login karna padega
  ```dart
  // Flutter code automatically handles this
  if (response.statusCode == 401) {
    await FcmService().clearAuthToken();
    // Redirect to login
  }
  ```

**Issue: 400 Bad Request - Invalid token format**
- **Cause:** FCM token format invalid
- **Solution:** 
  - Verify Firebase configuration
  - Check `google-services.json` (Android) / `GoogleService-Info.plist` (iOS)
  - Run `flutterfire configure` again

---

### FCM Token Unregister API

#### Endpoint
```
POST {{APP_URL}}/api/fcm/unregister
```

#### Authentication
Bearer Token required.

#### Request Headers
```
Authorization: Bearer {auth_token}
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "token": "fcm_device_token_here"
}
```

#### Success Response (200)
```json
{
  "success": true,
  "message": "FCM token unregistered successfully"
}
```

#### Error Responses

**422 Unprocessable Entity:**
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "token": ["The token field is required."]
  }
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Failed to unregister FCM token: {error_message}"
}
```

**Note:** Ye automatically call hota hai `FcmService().clearAuthToken()` me (logout par).

---

### API Base URL Configuration

Flutter app me API URL configure karne ke liye:

```dart
// lib/config/app_config.dart
class AppConfig {
  // Development
  static const String devApiUrl = 'http://localhost:8000/api';
  
  // Production - Update with your actual API URL
  static const String prodApiUrl = 'https://api.yourapp.com/api';
  
  static String get apiBaseUrl {
    const envUrl = String.fromEnvironment('API_BASE_URL');
    if (envUrl.isNotEmpty) {
      return envUrl;
    }
    return devApiUrl; // Change to prodApiUrl for production
  }
}
```

**Important:** Production build ke liye `prodApiUrl` use karein.

---

### API Integration Checklist

- [ ] `AppConfig.apiBaseUrl` properly configured
- [ ] Auth token properly set after login: `await FcmService().setAuthToken(token)`
- [ ] FCM token received: `await FcmService().getFcmToken()`
- [ ] Token registration successful (check console logs)
- [ ] API response 200 status code
- [ ] Database me `fcm_tokens` table me entry created

---

*Note: Flutter implementation me ye APIs automatically handle ho rahi hain. Manual API calls ki zarurat nahi hai, lekin debugging/testing ke liye ye documentation helpful hai.*

---

## Best Practices

1. **Register token after login**: Don't register before user is authenticated
2. **Unregister on logout**: Always unregister token when user logs out
3. **Handle token refresh**: Listen to `onTokenRefresh` and update backend
4. **Error handling**: Implement retry logic for token registration
5. **Navigation**: Use deep linking for better UX
6. **Testing**: Test all app states (foreground, background, terminated)

---

## Complete Example

See the full implementation in `lib/services/fcm_service.dart` above.

---

*Last Updated*: 2025-11-22

