# Account Management APIs - Flutter Developer Guide

## Overview
This API allows users to manage their account settings including changing email address, changing password, and deleting their account. All operations require proper authentication and include security measures to protect user accounts.

**Base URL:** `http://192.168.29.91:8000/api/account`

---

## Table of Contents
1. [Authentication](#authentication)
2. [Change Email API](#1-change-email-api)
3. [Change Password API](#2-change-password-api)
4. [Delete Account API](#3-delete-account-api)
5. [Flutter Implementation](#flutter-implementation)
6. [Error Handling](#error-handling)
7. [Security Considerations](#security-considerations)
8. [Testing Guide](#testing-guide)

---

## Authentication

Most endpoints require authentication. Use the token received after login.

**Header Format:**
```dart
headers: {
  'Authorization': 'Bearer {your_token_here}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

**Note:** The email verification endpoint (`verify-email-change`) is public and does not require authentication.

---

## 1. Change Email API

### 1.1 Request Email Change

Request to change the authenticated user's email address. A verification email will be sent to the new email address.

#### Endpoint
```
POST /api/account/change-email
```

#### Request Headers
```dart
{
  'Authorization': 'Bearer {token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

#### Request Body
```json
{
  "email": "newemail@example.com"
}
```

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Email change request sent. Please check your new email for verification.",
  "data": null
}
```

#### Important Notes
- The email change is **not** immediate
- A verification email is sent to the new email address
- A notification email is sent to the old email address
- The verification link expires in 24 hours
- The email change will only be completed after verification

---

### 1.2 Verify Email Change

Verify and complete the email change process using the token from the verification email.

#### Endpoint
```
GET /api/account/verify-email-change?token={token}
```

#### Request Headers
```dart
{
  'Accept': 'application/json',
}
```

**Note:** No authentication required for this endpoint.

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `token` | `string` | Yes | Verification token from email |

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Email changed successfully",
  "data": {
    "email": "newemail@example.com"
  }
}
```

---

## 2. Change Password API

Change the authenticated user's password. All active sessions will be invalidated after password change.

### Endpoint
```
POST /api/account/change-password
```

### Request Headers
```dart
{
  'Authorization': 'Bearer {token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

### Request Body
```json
{
  "current_password": "currentPassword123",
  "new_password": "newPassword456!"
}
```

### Password Requirements

- Minimum 8 characters
- Maximum 128 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)
- At least one special character (!@#$%^&*)
- Must be different from current password

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Password changed successfully",
  "data": null
}
```

### Important Notes
- Current password must be correct
- New password must meet strength requirements
- New password must be different from current password
- All active sessions/tokens will be invalidated
- User will need to login again with new password
- A notification email will be sent to the user

---

## 3. Delete Account API

Permanently delete the authenticated user's account. This action is irreversible.

### Endpoint
```
POST /api/account/delete
```

### Request Headers
```dart
{
  'Authorization': 'Bearer {token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

### Request Body
```json
{
  "password": "userPassword123"
}
```

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Account deleted successfully",
  "data": null
}
```

### Important Notes
- Password confirmation is required
- This action is **permanent and irreversible**
- All user data will be deleted:
  - Profile information
  - Meetings
  - Contacts
  - Surveys
  - Notifications
  - All other associated data
- All active sessions/tokens will be invalidated
- A confirmation email will be sent
- User will be logged out immediately

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

### Step 2: Create API Service

```dart
// lib/services/account_management_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;

class AccountManagementService {
  final String baseUrl;
  final String? authToken;

  AccountManagementService({
    required this.baseUrl,
    this.authToken,
  });

  /// Request to change email address
  /// 
  /// [newEmail] - The new email address
  /// Throws [Exception] if request fails
  Future<void> requestEmailChange(String newEmail) async {
    final url = Uri.parse('$baseUrl/account/change-email');
    
    final response = await http.post(
      url,
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'email': newEmail,
      }),
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
    } else if (response.statusCode == 409) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Email is already in use');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to process email change request');
    }
  }

  /// Verify email change with token
  /// 
  /// [token] - Verification token from email
  /// Returns the new email address if successful
  /// Throws [Exception] if request fails
  Future<String> verifyEmailChange(String token) async {
    final url = Uri.parse('$baseUrl/account/verify-email-change?token=$token');
    
    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      if (jsonData['success'] == true && jsonData['data'] != null) {
        return jsonData['data']['email'];
      }
      throw Exception('Invalid response format');
    } else if (response.statusCode == 400) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Invalid or expired token');
    } else if (response.statusCode == 409) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Email is already in use');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to verify email change');
    }
  }

  /// Change user password
  /// 
  /// [currentPassword] - Current password
  /// [newPassword] - New password
  /// Throws [Exception] if request fails
  Future<void> changePassword(String currentPassword, String newPassword) async {
    final url = Uri.parse('$baseUrl/account/change-password');
    
    final response = await http.post(
      url,
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'current_password': currentPassword,
        'new_password': newPassword,
      }),
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
    } else if (response.statusCode == 403) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Current password is incorrect');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to change password');
    }
  }

  /// Delete user account
  /// 
  /// [password] - User's password for confirmation
  /// Throws [Exception] if request fails
  Future<void> deleteAccount(String password) async {
    final url = Uri.parse('$baseUrl/account/delete');
    
    final response = await http.post(
      url,
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      if (jsonData['success'] == true) {
        return;
      }
      throw Exception('Invalid response format');
    } else if (response.statusCode == 400) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Password is required');
    } else if (response.statusCode == 401) {
      throw Exception('Unauthorized. Please login.');
    } else if (response.statusCode == 403) {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Password is incorrect');
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'Failed to delete account');
    }
  }
}
```

### Step 3: Usage Examples

#### Example 1: Request Email Change
```dart
try {
  final service = AccountManagementService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: 'your_auth_token_here',
  );
  
  await service.requestEmailChange('newemail@example.com');
  print('Email change request sent successfully');
  // Show message: "Please check your new email for verification"
} catch (e) {
  print('Error: $e');
  // Handle error
}
```

#### Example 2: Verify Email Change
```dart
try {
  final service = AccountManagementService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: null, // Not required
  );
  
  final token = 'token_from_email_link';
  final newEmail = await service.verifyEmailChange(token);
  print('Email changed successfully to: $newEmail');
  // Update user's email in local storage
  // Redirect to login
} catch (e) {
  print('Error: $e');
  // Handle error
}
```

#### Example 3: Change Password
```dart
try {
  final service = AccountManagementService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: 'your_auth_token_here',
  );
  
  await service.changePassword('oldPassword123', 'newPassword456!');
  print('Password changed successfully');
  // Clear local token
  // Redirect to login
} catch (e) {
  print('Error: $e');
  // Handle error
}
```

#### Example 4: Delete Account
```dart
try {
  final service = AccountManagementService(
    baseUrl: 'http://192.168.29.91:8000/api',
    authToken: 'your_auth_token_here',
  );
  
  await service.deleteAccount('userPassword123');
  print('Account deleted successfully');
  // Clear all local data
  // Redirect to login/register
} catch (e) {
  print('Error: $e');
  // Handle error
}
```

### Step 4: Flutter UI Examples

#### Change Email Screen
```dart
// lib/screens/change_email_screen.dart

import 'package:flutter/material.dart';
import '../services/account_management_service.dart';

class ChangeEmailScreen extends StatefulWidget {
  final String authToken;
  
  const ChangeEmailScreen({
    Key? key,
    required this.authToken,
  }) : super(key: key);

  @override
  State<ChangeEmailScreen> createState() => _ChangeEmailScreenState();
}

class _ChangeEmailScreenState extends State<ChangeEmailScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  late AccountManagementService _service;
  bool _isLoading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = AccountManagementService(
      baseUrl: 'http://192.168.29.91:8000/api',
      authToken: widget.authToken,
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _requestEmailChange() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      await _service.requestEmailChange(_emailController.text.trim());
      
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Email Change Requested'),
            content: const Text(
              'A verification email has been sent to your new email address. '
              'Please check your inbox and click the verification link to complete the change.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('OK'),
              ),
            ],
          ),
        );
        _emailController.clear();
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $_error'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Change Email'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Enter your new email address',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'A verification email will be sent to your new email address.',
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 24),
              
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'New Email Address',
                  hintText: 'newemail@example.com',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.email),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter an email address';
                  }
                  if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
                    return 'Please enter a valid email address';
                  }
                  return null;
                },
              ),
              
              const SizedBox(height: 24),
              
              if (_error != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    border: Border.all(color: Colors.red),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _error!,
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
              
              const SizedBox(height: 24),
              
              ElevatedButton(
                onPressed: _isLoading ? null : _requestEmailChange,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Request Email Change'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

#### Change Password Screen
```dart
// lib/screens/change_password_screen.dart

import 'package:flutter/material.dart';
import '../services/account_management_service.dart';

class ChangePasswordScreen extends StatefulWidget {
  final String authToken;
  
  const ChangePasswordScreen({
    Key? key,
    required this.authToken,
  }) : super(key: key);

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  late AccountManagementService _service;
  bool _isLoading = false;
  bool _obscureCurrentPassword = true;
  bool _obscureNewPassword = true;
  bool _obscureConfirmPassword = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = AccountManagementService(
      baseUrl: 'http://192.168.29.91:8000/api',
      authToken: widget.authToken,
    );
  }

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    if (value.length < 8) {
      return 'Password must be at least 8 characters';
    }
    if (value.length > 128) {
      return 'Password must not exceed 128 characters';
    }
    if (!RegExp(r'[A-Z]').hasMatch(value)) {
      return 'Password must contain at least one uppercase letter';
    }
    if (!RegExp(r'[a-z]').hasMatch(value)) {
      return 'Password must contain at least one lowercase letter';
    }
    if (!RegExp(r'[0-9]').hasMatch(value)) {
      return 'Password must contain at least one number';
    }
    if (!RegExp(r'[!@#$%^&*]').hasMatch(value)) {
      return 'Password must contain at least one special character (!@#\$%^&*)';
    }
    return null;
  }

  Future<void> _changePassword() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_newPasswordController.text != _confirmPasswordController.text) {
      setState(() {
        _error = 'New password and confirm password do not match';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      await _service.changePassword(
        _currentPasswordController.text,
        _newPasswordController.text,
      );
      
      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: const Text('Password Changed'),
            content: const Text(
              'Your password has been changed successfully. '
              'You will be logged out and need to login again with your new password.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  // Clear token and redirect to login
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    '/login',
                    (route) => false,
                  );
                },
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $_error'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Change Password'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Change Your Password',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Enter your current password and choose a new strong password.',
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 24),
              
              TextFormField(
                controller: _currentPasswordController,
                obscureText: _obscureCurrentPassword,
                decoration: InputDecoration(
                  labelText: 'Current Password',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureCurrentPassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureCurrentPassword = !_obscureCurrentPassword;
                      });
                    },
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Current password is required';
                  }
                  return null;
                },
              ),
              
              const SizedBox(height: 16),
              
              TextFormField(
                controller: _newPasswordController,
                obscureText: _obscureNewPassword,
                decoration: InputDecoration(
                  labelText: 'New Password',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.lock_outline),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureNewPassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureNewPassword = !_obscureNewPassword;
                      });
                    },
                  ),
                ),
                validator: _validatePassword,
              ),
              
              const SizedBox(height: 16),
              
              TextFormField(
                controller: _confirmPasswordController,
                obscureText: _obscureConfirmPassword,
                decoration: InputDecoration(
                  labelText: 'Confirm New Password',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.lock_outline),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureConfirmPassword = !_obscureConfirmPassword;
                      });
                    },
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please confirm your new password';
                  }
                  if (value != _newPasswordController.text) {
                    return 'Passwords do not match';
                  }
                  return null;
                },
              ),
              
              const SizedBox(height: 16),
              
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Password Requirements:',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    SizedBox(height: 4),
                    Text('• At least 8 characters'),
                    Text('• At least one uppercase letter'),
                    Text('• At least one lowercase letter'),
                    Text('• At least one number'),
                    Text('• At least one special character (!@#\$%^&*)'),
                  ],
                ),
              ),
              
              const SizedBox(height: 24),
              
              if (_error != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    border: Border.all(color: Colors.red),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _error!,
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
              
              const SizedBox(height: 24),
              
              ElevatedButton(
                onPressed: _isLoading ? null : _changePassword,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Change Password'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

#### Delete Account Screen
```dart
// lib/screens/delete_account_screen.dart

import 'package:flutter/material.dart';
import '../services/account_management_service.dart';

class DeleteAccountScreen extends StatefulWidget {
  final String authToken;
  
  const DeleteAccountScreen({
    Key? key,
    required this.authToken,
  }) : super(key: key);

  @override
  State<DeleteAccountScreen> createState() => _DeleteAccountScreenState();
}

class _DeleteAccountScreenState extends State<DeleteAccountScreen> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  late AccountManagementService _service;
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _confirmDelete = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = AccountManagementService(
      baseUrl: 'http://192.168.29.91:8000/api',
      authToken: widget.authToken,
    );
  }

  @override
  void dispose() {
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _deleteAccount() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (!_confirmDelete) {
      setState(() {
        _error = 'Please confirm that you want to delete your account';
      });
      return;
    }

    // Show confirmation dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Account'),
        content: const Text(
          'Are you sure you want to delete your account? This action is permanent and cannot be undone. '
          'All your data will be permanently deleted.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true) {
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      await _service.deleteAccount(_passwordController.text);
      
      if (mounted) {
        // Clear all local data
        // Navigate to login/register
        Navigator.pushNamedAndRemoveUntil(
          context,
          '/login',
          (route) => false,
        );
        
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Account deleted successfully'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $_error'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Delete Account'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  border: Border.all(color: Colors.red),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Column(
                  children: [
                    Icon(Icons.warning, color: Colors.red, size: 48),
                    SizedBox(height: 8),
                    Text(
                      'Delete Account',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.red,
                      ),
                    ),
                    SizedBox(height: 8),
                    Text(
                      'This action is permanent and cannot be undone.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.red),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 24),
              
              const Text(
                'What will be deleted:',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              const Text('• Your profile information'),
              const Text('• All your meetings'),
              const Text('• All your contacts'),
              const Text('• All your surveys'),
              const Text('• All your notifications'),
              const Text('• All other associated data'),
              
              const SizedBox(height: 24),
              
              TextFormField(
                controller: _passwordController,
                obscureText: _obscurePassword,
                decoration: InputDecoration(
                  labelText: 'Enter Your Password',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscurePassword = !_obscurePassword;
                      });
                    },
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Password is required to delete your account';
                  }
                  return null;
                },
              ),
              
              const SizedBox(height: 16),
              
              CheckboxListTile(
                title: const Text(
                  'I understand that this action is permanent and cannot be undone',
                  style: TextStyle(fontSize: 14),
                ),
                value: _confirmDelete,
                onChanged: (value) {
                  setState(() {
                    _confirmDelete = value ?? false;
                  });
                },
                controlAffinity: ListTileControlAffinity.leading,
              ),
              
              const SizedBox(height: 24),
              
              if (_error != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    border: Border.all(color: Colors.red),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _error!,
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
              
              const SizedBox(height: 24),
              
              ElevatedButton(
                onPressed: _isLoading ? null : _deleteAccount,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  backgroundColor: Colors.red,
                  foregroundColor: Colors.white,
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Delete My Account'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

#### Email Verification Screen (Deep Link Handler)
```dart
// lib/screens/verify_email_change_screen.dart

import 'package:flutter/material.dart';
import '../services/account_management_service.dart';

class VerifyEmailChangeScreen extends StatefulWidget {
  final String token;
  
  const VerifyEmailChangeScreen({
    Key? key,
    required this.token,
  }) : super(key: key);

  @override
  State<VerifyEmailChangeScreen> createState() => _VerifyEmailChangeScreenState();
}

class _VerifyEmailChangeScreenState extends State<VerifyEmailChangeScreen> {
  late AccountManagementService _service;
  bool _isLoading = true;
  String? _error;
  String? _newEmail;

  @override
  void initState() {
    super.initState();
    _service = AccountManagementService(
      baseUrl: 'http://192.168.29.91:8000/api',
      authToken: null,
    );
    _verifyEmailChange();
  }

  Future<void> _verifyEmailChange() async {
    try {
      final newEmail = await _service.verifyEmailChange(widget.token);
      setState(() {
        _newEmail = newEmail;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Verify Email Change'),
      ),
      body: Center(
        child: _isLoading
            ? const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Verifying email change...'),
                ],
              )
            : _error != null
                ? Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 64,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Verification Failed',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _error!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Colors.grey),
                        ),
                        const SizedBox(height: 24),
                        ElevatedButton(
                          onPressed: () => Navigator.pop(context),
                          child: const Text('Go Back'),
                        ),
                      ],
                    ),
                  )
                : Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.check_circle,
                          size: 64,
                          color: Colors.green,
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Email Changed Successfully',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Your email has been changed to:',
                          style: TextStyle(color: Colors.grey),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _newEmail ?? '',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.blue,
                          ),
                        ),
                        const SizedBox(height: 24),
                        ElevatedButton(
                          onPressed: () {
                            Navigator.pushNamedAndRemoveUntil(
                              context,
                              '/login',
                              (route) => false,
                            );
                          },
                          child: const Text('Continue to Login'),
                        ),
                      ],
                    ),
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
| `403` | Forbidden (wrong password) | `{"success": false, "message": "Password is incorrect", "data": null}` |
| `409` | Conflict (email already in use) | `{"success": false, "message": "Email is already in use", "data": null}` |
| `500` | Internal Server Error | `{"success": false, "message": "Failed to process request", "data": null}` |

### Common Error Scenarios

#### Change Email Errors

1. **Invalid Email Format:**
   ```json
   {
     "success": false,
     "message": "Invalid email format",
     "data": null
   }
   ```

2. **Email Already in Use:**
   ```json
   {
     "success": false,
     "message": "Email is already in use",
     "data": null
   }
   ```

3. **Same Email as Current:**
   ```json
   {
     "success": false,
     "message": "New email must be different from current email",
     "data": null
   }
   ```

4. **Invalid/Expired Token (Verification):**
   ```json
   {
     "success": false,
     "message": "Invalid or expired token",
     "data": null
   }
   ```

#### Change Password Errors

1. **Current Password Incorrect:**
   ```json
   {
     "success": false,
     "message": "Current password is incorrect",
     "data": null
   }
   ```

2. **Weak Password:**
   ```json
   {
     "success": false,
     "message": "Password must be at least 8 characters and contain uppercase, lowercase, number, and special character",
     "data": null
   }
   ```

3. **Same Password as Current:**
   ```json
   {
     "success": false,
     "message": "New password must be different from current password",
     "data": null
   }
   ```

#### Delete Account Errors

1. **Password Incorrect:**
   ```json
   {
     "success": false,
     "message": "Password is incorrect",
     "data": null
   }
   ```

2. **Missing Password:**
   ```json
   {
     "success": false,
     "message": "Password is required for account deletion",
     "data": null
   }
   ```

### Error Handling Example

```dart
try {
  await service.changePassword(currentPassword, newPassword);
  // Handle success
} on Exception catch (e) {
  String errorMessage = 'An error occurred';
  
  if (e.toString().contains('Unauthorized')) {
    // Redirect to login
    Navigator.pushReplacementNamed(context, '/login');
  } else if (e.toString().contains('incorrect')) {
    errorMessage = 'Current password is incorrect';
  } else if (e.toString().contains('different')) {
    errorMessage = 'New password must be different from current password';
  } else if (e.toString().contains('at least')) {
    errorMessage = 'Password does not meet strength requirements';
  } else {
    errorMessage = e.toString();
  }
  
  // Show error dialog
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('Error'),
      content: Text(errorMessage),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('OK'),
        ),
      ],
    ),
  );
}
```

---

## Security Considerations

### 1. Password Security
- Passwords are hashed using bcrypt
- Never store or log plain text passwords
- Enforce strong password requirements
- Validate password strength on both client and server

### 2. Email Change Security
- Email changes require verification
- Verification tokens expire after 24 hours
- Both old and new emails receive notifications
- Prevents unauthorized email changes

### 3. Account Deletion Security
- Password confirmation required
- Soft delete allows data recovery (if needed)
- All sessions invalidated immediately
- Confirmation email sent

### 4. Token Management
- All tokens invalidated after password change
- All tokens invalidated after account deletion
- User must re-login after sensitive operations

### 5. Best Practices
- Always validate input on client side
- Show clear error messages
- Handle expired tokens gracefully
- Clear local data after account deletion
- Redirect to login after password change

---

## Testing Guide

### Test Cases

#### Change Email
1. Request email change with valid email
2. Request email change with invalid format
3. Request email change with existing email
4. Request email change with same email
5. Verify email change with valid token
6. Verify email change with expired token
7. Request without token

#### Change Password
1. Change password with correct current password
2. Change password with incorrect current password
3. Change password with weak new password
4. Change password with same password
5. Verify token invalidation after password change

#### Delete Account
1. Delete account with correct password
2. Delete account with incorrect password
3. Delete account without password
4. Verify token invalidation after deletion
5. Verify data deletion

### Example cURL Commands

#### Request Email Change
```bash
curl -X POST "http://192.168.29.91:8000/api/account/change-email" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "newemail@example.com"}'
```

#### Verify Email Change
```bash
curl -X GET "http://192.168.29.91:8000/api/account/verify-email-change?token=YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Change Password
```bash
curl -X POST "http://192.168.29.91:8000/api/account/change-password" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "current_password": "oldPassword123",
    "new_password": "newPassword456!"
  }'
```

#### Delete Account
```bash
curl -X POST "http://192.168.29.91:8000/api/account/delete" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"password": "userPassword123"}'
```

---

## Important Notes

1. **Authentication Required:** Most endpoints require a Bearer token
2. **Email Verification:** Email changes require verification via email link
3. **Password Strength:** Enforce strong passwords (8+ chars, uppercase, lowercase, number, special char)
4. **Token Invalidation:** All tokens are invalidated after password change or account deletion
5. **Account Deletion:** Permanent and irreversible - all data is deleted
6. **Deep Links:** Configure deep links to handle email verification from email
7. **Error Handling:** Always handle expired tokens and validation errors gracefully
8. **User Experience:** Show clear messages and redirect appropriately after operations

---

## Integration Checklist

- [ ] Add `http` package to `pubspec.yaml`
- [ ] Create `AccountManagementService` service
- [ ] Implement change email functionality
- [ ] Implement verify email change functionality
- [ ] Implement change password functionality
- [ ] Implement delete account functionality
- [ ] Add error handling for all scenarios
- [ ] Create change email UI screen
- [ ] Create change password UI screen
- [ ] Create delete account UI screen
- [ ] Create email verification UI screen
- [ ] Configure deep links (Android & iOS)
- [ ] Test with valid inputs
- [ ] Test with invalid inputs
- [ ] Test with expired tokens
- [ ] Test token invalidation
- [ ] Handle loading states
- [ ] Handle error states
- [ ] Clear local data after account deletion
- [ ] Redirect to login after password change

---

**Documentation Version:** 1.0  
**Last Updated:** December 2025  
**For Flutter Developers**


