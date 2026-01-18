# Notification Preferences API - Flutter Developer Guide

## Overview
This API allows users to manage their notification preferences. Users can customize their notification settings including push notifications, email notifications, meeting reminders, and more.

**Base URL:** `http://192.168.29.91:8000/api/notifications/preferences`

---

## Table of Contents
1. [Authentication](#authentication)
2. [Get Notification Preferences](#1-get-notification-preferences)
3. [Update Notification Preferences](#2-update-notification-preferences)
4. [Flutter Implementation](#flutter-implementation)
5. [Error Handling](#error-handling)
6. [Data Models](#data-models)
7. [Testing Guide](#testing-guide)

---

## Authentication

All endpoints require authentication. Use the token received after login.

**Header Format:**
```dart
headers: {
  'Authorization': 'Bearer {your_token_here}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

---

## 1. Get Notification Preferences

Retrieves the current authenticated user's notification preferences.

### Endpoint
```
GET /api/notifications/preferences
```

### Request Headers
```dart
{
  'Authorization': 'Bearer {token}',
  'Accept': 'application/json',
}
```

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Notification preferences retrieved successfully",
  "data": {
    "push_notifications_enabled": true,
    "email_notifications_enabled": true,
    "email_meeting_reminders": true,
    "email_meeting_updates": true,
    "email_meeting_cancellations": true,
    "meeting_reminders": [15, 30, 60],
    "reminder_15min": true,
    "reminder_30min": true,
    "reminder_1hour": true,
    "notification_sound": true,
    "notification_badge": true
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `push_notifications_enabled` | `bool` | Enable/disable push notifications |
| `email_notifications_enabled` | `bool` | Enable/disable email notifications |
| `email_meeting_reminders` | `bool` | Receive email reminders for meetings |
| `email_meeting_updates` | `bool` | Receive emails when meetings are updated |
| `email_meeting_cancellations` | `bool` | Receive emails when meetings are cancelled |
| `meeting_reminders` | `List<int>` | Array of reminder minutes: [15, 30, 60] |
| `reminder_15min` | `bool` | 15 minutes before reminder enabled |
| `reminder_30min` | `bool` | 30 minutes before reminder enabled |
| `reminder_1hour` | `bool` | 1 hour before reminder enabled |
| `notification_sound` | `bool` | Play sound for notifications |
| `notification_badge` | `bool` | Show unread badge count |

**Note:** If the user doesn't have a preference record, default values will be returned (all `true`, `meeting_reminders: [15]`).

---

## 2. Update Notification Preferences

Updates the current authenticated user's notification preferences. Supports partial updates (you can send only the fields you want to update).

### Endpoint
```
POST /api/notifications/preferences
```

### Request Headers
```dart
{
  'Authorization': 'Bearer {token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

### Request Body (All fields optional)
```json
{
  "push_notifications_enabled": true,
  "email_notifications_enabled": true,
  "email_meeting_reminders": true,
  "email_meeting_updates": true,
  "email_meeting_cancellations": true,
  "meeting_reminders": [15, 30, 60],
  "notification_sound": true,
  "notification_badge": true
}
```

### Meeting Reminders Field

The `meeting_reminders` field is an array of integers representing minutes before a meeting:
- `15` = 15 minutes before meeting
- `30` = 30 minutes before meeting
- `60` = 1 hour before meeting

**Valid values:** `15`, `30`, `60` (only these three values are allowed)
**Examples:** `[15, 30, 60]` or `[15]` or `[]` (empty array = no reminders)

**Important:** 
- No duplicate values allowed
- Only integers 15, 30, or 60 are valid
- Array can be empty `[]` to disable all reminders

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Notification preferences updated successfully",
  "data": null
}
```

---

## Flutter Implementation

### Step 1: Add HTTP Package

Add the `http` package to your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
```

Then run:
```bash
flutter pub get
```

### Step 2: Create Data Model

```dart
// lib/models/notification_preferences.dart

class NotificationPreferences {
  final bool pushNotificationsEnabled;
  final bool emailNotificationsEnabled;
  final bool emailMeetingReminders;
  final bool emailMeetingUpdates;
  final bool emailMeetingCancellations;
  final List<int> meetingReminders;
  final bool reminder15min;
  final bool reminder30min;
  final bool reminder1hour;
  final bool notificationSound;
  final bool notificationBadge;

  NotificationPreferences({
    required this.pushNotificationsEnabled,
    required this.emailNotificationsEnabled,
    required this.emailMeetingReminders,
    required this.emailMeetingUpdates,
    required this.emailMeetingCancellations,
    required this.meetingReminders,
    required this.reminder15min,
    required this.reminder30min,
    required this.reminder1hour,
    required this.notificationSound,
    required this.notificationBadge,
  });

  factory NotificationPreferences.fromJson(Map<String, dynamic> json) {
    final reminders = List<int>.from(json['meeting_reminders'] ?? [15]);
    
    return NotificationPreferences(
      pushNotificationsEnabled: json['push_notifications_enabled'] ?? true,
      emailNotificationsEnabled: json['email_notifications_enabled'] ?? true,
      emailMeetingReminders: json['email_meeting_reminders'] ?? true,
      emailMeetingUpdates: json['email_meeting_updates'] ?? true,
      emailMeetingCancellations: json['email_meeting_cancellations'] ?? true,
      meetingReminders: reminders,
      reminder15min: json['reminder_15min'] ?? reminders.contains(15),
      reminder30min: json['reminder_30min'] ?? reminders.contains(30),
      reminder1hour: json['reminder_1hour'] ?? reminders.contains(60),
      notificationSound: json['notification_sound'] ?? true,
      notificationBadge: json['notification_badge'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'push_notifications_enabled': pushNotificationsEnabled,
      'email_notifications_enabled': emailNotificationsEnabled,
      'email_meeting_reminders': emailMeetingReminders,
      'email_meeting_updates': emailMeetingUpdates,
      'email_meeting_cancellations': emailMeetingCancellations,
      'meeting_reminders': meetingReminders,
      'notification_sound': notificationSound,
      'notification_badge': notificationBadge,
    };
  }

  NotificationPreferences copyWith({
    bool? pushNotificationsEnabled,
    bool? emailNotificationsEnabled,
    bool? emailMeetingReminders,
    bool? emailMeetingUpdates,
    bool? emailMeetingCancellations,
    List<int>? meetingReminders,
    bool? notificationSound,
    bool? notificationBadge,
  }) {
    final reminders = meetingReminders ?? this.meetingReminders;
    
    return NotificationPreferences(
      pushNotificationsEnabled: pushNotificationsEnabled ?? this.pushNotificationsEnabled,
      emailNotificationsEnabled: emailNotificationsEnabled ?? this.emailNotificationsEnabled,
      emailMeetingReminders: emailMeetingReminders ?? this.emailMeetingReminders,
      emailMeetingUpdates: emailMeetingUpdates ?? this.emailMeetingUpdates,
      emailMeetingCancellations: emailMeetingCancellations ?? this.emailMeetingCancellations,
      meetingReminders: reminders,
      reminder15min: reminders.contains(15),
      reminder30min: reminders.contains(30),
      reminder1hour: reminders.contains(60),
      notificationSound: notificationSound ?? this.notificationSound,
      notificationBadge: notificationBadge ?? this.notificationBadge,
    );
  }

  // Helper method to check if a specific reminder is enabled
  bool isReminderEnabled(int minutes) {
    return meetingReminders.contains(minutes);
  }

  // Helper method to toggle a reminder
  NotificationPreferences toggleReminder(int minutes) {
    final reminders = List<int>.from(meetingReminders);
    if (reminders.contains(minutes)) {
      reminders.remove(minutes);
    } else {
      reminders.add(minutes);
      reminders.sort();
    }
    return copyWith(meetingReminders: reminders);
  }
}
```

### Step 3: Create API Service

```dart
// lib/services/notification_preferences_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/notification_preferences.dart';

class NotificationPreferencesService {
  final String baseUrl;
  final String? authToken;

  NotificationPreferencesService({
    required this.baseUrl,
    this.authToken,
  });

  /// Get notification preferences for the authenticated user
  /// 
  /// Returns [NotificationPreferences] if successful
  /// Throws [Exception] if request fails
  Future<NotificationPreferences> getPreferences() async {
    final url = Uri.parse('$baseUrl/notifications/preferences');
    
    final response = await http.get(
      url,
      headers: {
        'Authorization': 'Bearer $authToken',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      if (jsonData['success'] == true && jsonData['data'] != null) {
        return NotificationPreferences.fromJson(jsonData['data']);
      }
      throw Exception('Invalid response format');
    } else if (response.statusCode == 401) {
      throw Exception('Unauthorized. Please login.');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to retrieve preferences');
    }
  }

  /// Update notification preferences
  /// 
  /// [preferences] - The preferences object to update
  /// Throws [Exception] if request fails
  Future<void> updatePreferences(NotificationPreferences preferences) async {
    final url = Uri.parse('$baseUrl/notifications/preferences');
    
    final body = preferences.toJson();
    
    final response = await http.post(
      url,
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode(body),
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      if (jsonData['success'] == true) {
        return;
      }
      throw Exception('Invalid response format');
    } else if (response.statusCode == 400) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Invalid request data');
    } else if (response.statusCode == 401) {
      throw Exception('Unauthorized. Please login.');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to update preferences');
    }
  }

  /// Update specific fields (partial update)
  /// 
  /// [updates] - Map of fields to update (only include fields you want to change)
  /// Throws [Exception] if request fails
  Future<void> updatePartialPreferences(Map<String, dynamic> updates) async {
    final url = Uri.parse('$baseUrl/notifications/preferences');
    
    final response = await http.post(
      url,
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode(updates),
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      if (jsonData['success'] == true) {
        return;
      }
      throw Exception('Invalid response format');
    } else if (response.statusCode == 400) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Invalid request data');
    } else if (response.statusCode == 401) {
      throw Exception('Unauthorized. Please login.');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to update preferences');
    }
  }
}
```

### Step 4: Usage Examples

#### Example 1: Get Preferences
```dart
try {
  final service = NotificationPreferencesService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: 'your_auth_token_here',
  );
  
  final preferences = await service.getPreferences();
  print('Push notifications: ${preferences.pushNotificationsEnabled}');
  print('Meeting reminders: ${preferences.meetingReminders}');
  print('15min reminder: ${preferences.reminder15min}');
} catch (e) {
  print('Error: $e');
}
```

#### Example 2: Update All Preferences
```dart
try {
  final service = NotificationPreferencesService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: 'your_auth_token_here',
  );
  
  final updatedPreferences = NotificationPreferences(
    pushNotificationsEnabled: true,
    emailNotificationsEnabled: true,
    emailMeetingReminders: true,
    emailMeetingUpdates: true,
    emailMeetingCancellations: true,
    meetingReminders: [15, 30, 60],
    reminder15min: true,
    reminder30min: true,
    reminder1hour: true,
    notificationSound: true,
    notificationBadge: true,
  );
  
  await service.updatePreferences(updatedPreferences);
  print('Preferences updated successfully');
} catch (e) {
  print('Error: $e');
}
```

#### Example 3: Partial Update (Update Single Field)
```dart
try {
  final service = NotificationPreferencesService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: 'your_auth_token_here',
  );
  
  // Disable push notifications only
  await service.updatePartialPreferences({
    'push_notifications_enabled': false,
  });
  
  // Update meeting reminders only
  await service.updatePartialPreferences({
    'meeting_reminders': [15, 30],
  });
  
  print('Preferences updated successfully');
} catch (e) {
  print('Error: $e');
}
```

#### Example 4: Using with Provider/State Management
```dart
// lib/providers/notification_preferences_provider.dart

import 'package:flutter/foundation.dart';
import '../models/notification_preferences.dart';
import '../services/notification_preferences_service.dart';

class NotificationPreferencesProvider with ChangeNotifier {
  NotificationPreferences? _preferences;
  bool _isLoading = false;
  String? _error;

  NotificationPreferences? get preferences => _preferences;
  bool get isLoading => _isLoading;
  String? get error => _error;

  final NotificationPreferencesService _service;

  NotificationPreferencesProvider(this._service);

  Future<void> loadPreferences() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _preferences = await _service.getPreferences();
      _error = null;
    } catch (e) {
      _error = e.toString();
      _preferences = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> updatePreferences(NotificationPreferences preferences) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _service.updatePreferences(preferences);
      _preferences = preferences;
      _error = null;
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> togglePushNotifications(bool value) async {
    if (_preferences == null) return;
    
    final updated = _preferences!.copyWith(
      pushNotificationsEnabled: value,
    );
    await updatePreferences(updated);
  }

  Future<void> toggleReminder(int minutes) async {
    if (_preferences == null) return;
    
    final updated = _preferences!.toggleReminder(minutes);
    await updatePreferences(updated);
  }
}
```

### Step 5: Flutter UI Example

```dart
// lib/screens/notification_settings_screen.dart

import 'package:flutter/material.dart';
import '../models/notification_preferences.dart';
import '../services/notification_preferences_service.dart';

class NotificationSettingsScreen extends StatefulWidget {
  final String authToken;
  
  const NotificationSettingsScreen({
    Key? key,
    required this.authToken,
  }) : super(key: key);

  @override
  State<NotificationSettingsScreen> createState() => _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState extends State<NotificationSettingsScreen> {
  late NotificationPreferencesService _service;
  NotificationPreferences? _preferences;
  bool _isLoading = true;
  bool _isSaving = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = NotificationPreferencesService(
      baseUrl: 'http://192.168.29.91:8000/api',
      authToken: widget.authToken,
    );
    _loadPreferences();
  }

  Future<void> _loadPreferences() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    
    try {
      final preferences = await _service.getPreferences();
      setState(() {
        _preferences = preferences;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _savePreferences() async {
    if (_preferences == null) return;
    
    setState(() => _isSaving = true);
    
    try {
      await _service.updatePreferences(_preferences!);
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Preferences saved successfully'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() => _isSaving = false);
    }
  }

  void _updateMeetingReminders(List<int> reminders) {
    setState(() {
      _preferences = _preferences!.copyWith(meetingReminders: reminders);
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Notification Settings')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null && _preferences == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Notification Settings')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Error: $_error'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadPreferences,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    if (_preferences == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Notification Settings')),
        body: const Center(child: Text('Failed to load preferences')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notification Settings'),
        actions: [
          if (_isSaving)
            const Padding(
              padding: EdgeInsets.all(16.0),
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            )
          else
            TextButton(
              onPressed: _savePreferences,
              child: const Text(
                'Save',
                style: TextStyle(color: Colors.white),
              ),
            ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Push Notifications Section
          _buildSectionTitle('Push Notifications'),
          SwitchListTile(
            title: const Text('Enable Push Notifications'),
            subtitle: const Text('Receive push notifications on your device'),
            value: _preferences!.pushNotificationsEnabled,
            onChanged: (value) {
              setState(() {
                _preferences = _preferences!.copyWith(
                  pushNotificationsEnabled: value,
                );
              });
            },
          ),
          
          const Divider(),
          
          // Email Notifications Section
          _buildSectionTitle('Email Notifications'),
          SwitchListTile(
            title: const Text('Enable Email Notifications'),
            subtitle: const Text('Receive notifications via email'),
            value: _preferences!.emailNotificationsEnabled,
            onChanged: (value) {
              setState(() {
                _preferences = _preferences!.copyWith(
                  emailNotificationsEnabled: value,
                );
              });
            },
          ),
          
          if (_preferences!.emailNotificationsEnabled) ...[
            SwitchListTile(
              title: const Text('Email Meeting Reminders'),
              subtitle: const Text('Receive email reminders for meetings'),
              value: _preferences!.emailMeetingReminders,
              onChanged: (value) {
                setState(() {
                  _preferences = _preferences!.copyWith(
                    emailMeetingReminders: value,
                  );
                });
              },
            ),
            
            SwitchListTile(
              title: const Text('Email Meeting Updates'),
              subtitle: const Text('Receive emails when meetings are updated'),
              value: _preferences!.emailMeetingUpdates,
              onChanged: (value) {
                setState(() {
                  _preferences = _preferences!.copyWith(
                    emailMeetingUpdates: value,
                  );
                });
              },
            ),
            
            SwitchListTile(
              title: const Text('Email Meeting Cancellations'),
              subtitle: const Text('Receive emails when meetings are cancelled'),
              value: _preferences!.emailMeetingCancellations,
              onChanged: (value) {
                setState(() {
                  _preferences = _preferences!.copyWith(
                    emailMeetingCancellations: value,
                  );
                });
              },
            ),
          ],
          
          const Divider(),
          
          // Meeting Reminders Section
          _buildSectionTitle('Meeting Reminders'),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Text(
              'Select when you want to be reminded before a meeting',
              style: TextStyle(color: Colors.grey),
            ),
          ),
          
          CheckboxListTile(
            title: const Text('15 minutes before'),
            value: _preferences!.meetingReminders.contains(15),
            onChanged: (value) {
              final reminders = List<int>.from(_preferences!.meetingReminders);
              if (value == true) {
                if (!reminders.contains(15)) reminders.add(15);
              } else {
                reminders.remove(15);
              }
              reminders.sort();
              _updateMeetingReminders(reminders);
            },
          ),
          
          CheckboxListTile(
            title: const Text('30 minutes before'),
            value: _preferences!.meetingReminders.contains(30),
            onChanged: (value) {
              final reminders = List<int>.from(_preferences!.meetingReminders);
              if (value == true) {
                if (!reminders.contains(30)) reminders.add(30);
              } else {
                reminders.remove(30);
              }
              reminders.sort();
              _updateMeetingReminders(reminders);
            },
          ),
          
          CheckboxListTile(
            title: const Text('1 hour before'),
            value: _preferences!.meetingReminders.contains(60),
            onChanged: (value) {
              final reminders = List<int>.from(_preferences!.meetingReminders);
              if (value == true) {
                if (!reminders.contains(60)) reminders.add(60);
              } else {
                reminders.remove(60);
              }
              reminders.sort();
              _updateMeetingReminders(reminders);
            },
          ),
          
          const Divider(),
          
          // Notification Settings Section
          _buildSectionTitle('Notification Settings'),
          SwitchListTile(
            title: const Text('Notification Sound'),
            subtitle: const Text('Play sound when receiving notifications'),
            value: _preferences!.notificationSound,
            onChanged: (value) {
              setState(() {
                _preferences = _preferences!.copyWith(
                  notificationSound: value,
                );
              });
            },
          ),
          
          SwitchListTile(
            title: const Text('Notification Badge'),
            subtitle: const Text('Show unread notification badge count'),
            value: _preferences!.notificationBadge,
            onChanged: (value) {
              setState(() {
                _preferences = _preferences!.copyWith(
                  notificationBadge: value,
                );
              });
            },
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(top: 16, bottom: 8),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Status Code | Scenario | Response Body |
|-------------|----------|---------------|
| `200` | Success | `{"success": true, "message": "...", "data": {...}}` |
| `400` | Bad Request (validation error) | `{"success": false, "message": "Invalid request data", "data": null}` |
| `401` | Unauthorized (invalid/missing token) | `{"success": false, "message": "Unauthorized. Please login.", "data": null}` |
| `500` | Internal Server Error | `{"success": false, "message": "Failed to update notification preferences", "data": null}` |

### Common Error Scenarios

#### 1. Missing Authorization Token
```json
{
  "success": false,
  "message": "Unauthorized. Please login.",
  "data": null
}
```

**Solution:** Ensure you're sending a valid Bearer token in the Authorization header.

#### 2. Invalid meeting_reminders Value
```json
{
  "success": false,
  "message": "Invalid reminder value: 45. Valid values are: 15, 30, 60",
  "data": null
}
```

**Solution:** Only use values 15, 30, or 60 in the meeting_reminders array.

#### 3. Duplicate Values in meeting_reminders
```json
{
  "success": false,
  "message": "meeting_reminders contains duplicate values",
  "data": null
}
```

**Solution:** Remove duplicate values from the array before sending.

#### 4. Invalid Boolean Value
```json
{
  "success": false,
  "message": "Invalid request data",
  "data": null
}
```

**Solution:** Ensure all boolean fields are sent as `true` or `false`, not strings like `"yes"` or `"no"`.

### Error Handling Example

```dart
try {
  final preferences = await service.getPreferences();
  // Handle success
} on Exception catch (e) {
  if (e.toString().contains('Unauthorized')) {
    // Redirect to login
    Navigator.pushReplacementNamed(context, '/login');
  } else {
    // Show error message
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Error'),
        content: Text(e.toString()),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }
}
```

---

## Data Models

### NotificationPreferences Model

```dart
class NotificationPreferences {
  final bool pushNotificationsEnabled;
  final bool emailNotificationsEnabled;
  final bool emailMeetingReminders;
  final bool emailMeetingUpdates;
  final bool emailMeetingCancellations;
  final List<int> meetingReminders;  // [15, 30, 60]
  final bool reminder15min;
  final bool reminder30min;
  final bool reminder1hour;
  final bool notificationSound;
  final bool notificationBadge;
}
```

### Default Values

If the user doesn't have a preference record, these default values will be used:
- `push_notifications_enabled`: `true`
- `email_notifications_enabled`: `true`
- `email_meeting_reminders`: `true`
- `email_meeting_updates`: `true`
- `email_meeting_cancellations`: `true`
- `meeting_reminders`: `[15]`
- `notification_sound`: `true`
- `notification_badge`: `true`

---

## Testing Guide

### Test Cases

1. **Get preferences for new user** - Should return default values
2. **Get preferences for existing user** - Should return saved preferences
3. **Update single field** - Partial update should work
4. **Update meeting reminders** - Should accept [15, 30, 60]
5. **Invalid reminder value** - Should return 400 error
6. **Without token** - Should return 401 error
7. **Duplicate reminder values** - Should return 400 error

### Example cURL Commands

#### Get Preferences
```bash
curl -X GET "http://192.168.29.91:8000/api/notifications/preferences" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Update Preferences (Full Update)
```bash
curl -X POST "http://192.168.29.91:8000/api/notifications/preferences" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "push_notifications_enabled": true,
    "email_notifications_enabled": true,
    "email_meeting_reminders": true,
    "email_meeting_updates": true,
    "email_meeting_cancellations": true,
    "meeting_reminders": [15, 30, 60],
    "notification_sound": true,
    "notification_badge": true
  }'
```

#### Update Preferences (Partial Update)
```bash
curl -X POST "http://192.168.29.91:8000/api/notifications/preferences" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "push_notifications_enabled": false,
    "meeting_reminders": [15, 30]
  }'
```

### Flutter Unit Test Example

```dart
// test/services/notification_preferences_service_test.dart

import 'package:flutter_test/flutter_test.dart';
import 'package:your_app/services/notification_preferences_service.dart';
import 'package:your_app/models/notification_preferences.dart';

void main() {
  group('NotificationPreferencesService', () {
    late NotificationPreferencesService service;

    setUp(() {
      service = NotificationPreferencesService(
        baseUrl: 'http://192.168.29.91:8000/api',
        authToken: 'test_token',
      );
    });

    test('getPreferences returns NotificationPreferences', () async {
      // Mock HTTP response
      // ... your mock setup
      
      final preferences = await service.getPreferences();
      
      expect(preferences, isA<NotificationPreferences>());
      expect(preferences.pushNotificationsEnabled, isA<bool>());
    });

    test('updatePreferences updates successfully', () async {
      // Mock HTTP response
      // ... your mock setup
      
      final preferences = NotificationPreferences(
        pushNotificationsEnabled: true,
        emailNotificationsEnabled: true,
        emailMeetingReminders: true,
        emailMeetingUpdates: true,
        emailMeetingCancellations: true,
        meetingReminders: [15, 30],
        reminder15min: true,
        reminder30min: true,
        reminder1hour: false,
        notificationSound: true,
        notificationBadge: true,
      );
      
      await expectLater(
        service.updatePreferences(preferences),
        completes,
      );
    });
  });
}
```

---

## Important Notes

1. **Authentication Required:** All endpoints require a Bearer token in the Authorization header
2. **Partial Updates:** The POST endpoint supports partial updates - you can send only the fields you want to update
3. **Meeting Reminders:** Only values `15`, `30`, and `60` are allowed in the `meeting_reminders` array
4. **Default Values:** If a user doesn't have a preference record, the GET endpoint will return default values
5. **Auto-Create:** The POST endpoint automatically creates a record if one doesn't exist
6. **No Duplicates:** The `meeting_reminders` array cannot contain duplicate values
7. **Empty Array:** An empty `meeting_reminders` array `[]` means no reminders will be sent

---

## Integration Checklist

- [ ] Add `http` package to `pubspec.yaml`
- [ ] Create `NotificationPreferences` model
- [ ] Create `NotificationPreferencesService` service
- [ ] Implement GET preferences functionality
- [ ] Implement POST/UPDATE preferences functionality
- [ ] Add error handling for all scenarios
- [ ] Create UI screen for notification settings
- [ ] Test with valid token
- [ ] Test with invalid/missing token
- [ ] Test partial updates
- [ ] Test meeting reminders validation
- [ ] Handle loading states
- [ ] Handle error states

---

**Documentation Version:** 1.0  
**Last Updated:** December 2025  
**For Flutter Developers**


