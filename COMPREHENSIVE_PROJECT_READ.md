# MeetUI - Comprehensive Project Documentation

## 📋 Executive Summary

**MeetUI** is a full-featured meeting management and survey platform built with **Laravel 12** (PHP 8.2+). It provides a comprehensive API backend for managing meetings, contacts, surveys, and notifications with Firebase Cloud Messaging integration. The application follows a multi-tenant architecture with organization-based data isolation.

---

## 🏗️ Architecture Overview

### Technology Stack

**Backend Framework:**
- Laravel 12.0
- PHP 8.2+
- MySQL Database
- Laravel Sanctum (Token-based API Authentication)
- Laravel Queues (Background job processing)
- Pest PHP (Testing Framework)

**Key Dependencies:**
- `kreait/firebase-php` (^7.23) - Firebase Cloud Messaging
- `google/apiclient` - Google OAuth integration
- `laravel/sanctum` (^4.2) - API authentication
- `laravel/tinker` (^2.10.1) - REPL for Laravel

**Development Tools:**
- Laravel Pint (Code formatting)
- Laravel Pail (Log viewing)
- Pest PHP (Testing)

### Architecture Patterns

1. **Repository Pattern** - Used for complex data operations
2. **Service Layer** - Business logic separation (CalendarService, FcmService)
3. **API Resources** - Data transformation layer
4. **Form Requests** - Request validation
5. **Traits** - Reusable functionality (AppliesMeetingFilters)
6. **Queue Jobs** - Asynchronous processing
7. **Console Commands** - Scheduled tasks

---

## 📦 Core Modules Deep Dive

### 1. Authentication & User Management

**Controllers:**
- `LoginController` - Handles login, signup, password reset, Google OAuth
- `RegisterController` - User registration
- `ChangeEmailController` - Email change with verification
- `ChangePasswordController` - Password management
- `DeleteAccountController` - Account deletion
- `UserProfileController` - User profile CRUD

**Features:**
- Email/password authentication
- Google OAuth integration
- Password reset via email
- Email change with token verification
- Account deletion with soft delete support
- Auto-organization creation on signup
- Auto-user profile creation on login

**Models:**
- `User` - Main user model with organizations relationship
- `UserProfile` - Extended user profile information

**Key Methods:**
- `login()` - Authenticates user, creates token, auto-creates profile
- `signup()` - Creates user and organization
- `googleLogin()` - OAuth authentication
- `forgotPassword()` - Sends password reset email

---

### 2. Organization Management

**Controller:** `OrganizationController`

**Model:** `Organization`

**Features:**
- Multi-tenant architecture
- Organization CRUD operations
- User-organization relationships (Many-to-Many)
- Organization search
- Auto-organization creation for new users
- Soft deletes support

**Database:**
- `organizations` table
- `organization_users` pivot table (with `role` field)

**Key Relationships:**
```php
User::organizations() // BelongsToMany
Organization::users() // BelongsToMany
```

---

### 3. Contact Management

**Controller:** `ContactController`

**Models:**
- `Contact` - Main contact model
- `ContactFavourite` - Favorites tracking

**Features:**
- Contact CRUD operations
- Pagination support
- Search functionality
- Contact favorites toggle
- Contact groups (JSON array)
- Bulk import (CSV/Excel)
- Contact statistics
- Contact dropdown API
- Avatar color assignment
- Referrer tracking (self-referential)

**Database Fields:**
- Personal: first_name, last_name, email, phone
- Professional: company, job_title
- Additional: address, notes, groups (JSON), avatar_color
- Relationships: referrer_id, organization_id, created_by

**Key Methods:**
- `index()` - Paginated list with search
- `save()` - Create/update contact
- `toggleFavourite()` - Toggle favorite status
- `import()` - Bulk import from file
- `state()` - Get statistics

---

### 4. Meeting Management

**Controllers:**
- `MeetingController` - Meeting CRUD, notifications
- `CalendarController` - Calendar views with filters

**Model:** `Meeting`

**Features:**
- Meeting CRUD operations
- Meeting attendees (Many-to-Many with Contact)
- Meeting notifications (FCM)
- Calendar views (month, week, day)
- Server-side filtering (type, attendees, duration, status)
- Meeting statistics
- Survey integration
- Meeting notifications scheduling

**Meeting Types:**
- Video Call
- In-Person Meeting
- Phone Call
- Online Meeting

**Meeting Status:**
- Created
- Scheduled
- Completed
- Cancelled
- Pending
- Rescheduled

**Key Methods:**
- `save()` - Create/update meeting, sync attendees, schedule notifications
- `index()` - Paginated list with filters
- `delete()` - Delete meeting, send cancellation notification
- `currentMonth()` - Month calendar view with filters
- `currentWeek()` - Week calendar view with filters
- `currentDay()` - Day view with filters

**Notification Types:**
- `meeting_created` - When meeting is created
- `meeting_updated` - When meeting is updated
- `meeting_cancelled` - When meeting is cancelled
- `meeting_reminder` - Scheduled reminders (configurable)
- `meeting_starting` - 5 minutes before meeting

**Database:**
- `meetings` table
- `meeting_attendees` pivot table
- `meeting_notifications` table (notification settings)
- `meeting_fcm_notifications` table (scheduled notifications)

---

### 5. Survey System

**Controllers:**
- `SurveyController` - Survey CRUD, analytics, submission check
- `SurveyStepController` - Survey steps and field values
- `SurveyAttachmentController` - Survey attachments

**Models:**
- `Survey` - Main survey model
- `SurveyStep` - Survey steps/pages
- `SurveyField` - Survey questions/fields
- `SurveyFieldValue` - User responses to fields
- `SurveyResponse` - Survey response records
- `SurveySubmission` - Submission tracking (prevents duplicates)
- `SurveyAttachment` - Survey file attachments

**Survey Structure:**
```
Survey
  └── SurveySteps (ordered)
       └── SurveyFields (ordered)
            └── SurveyFieldValues (user responses)
```

**Field Types Supported:**
- Short Answer (text)
- Paragraph (textarea)
- Multiple Choice (radio)
- Checkboxes (multiple selection)
- Dropdown (select)
- Rating Scale
- Email
- Number
- Date
- File Upload

**Features:**
- Multi-step surveys
- Field validation (required/optional)
- Field options (for choice-based questions)
- Response tracking
- Submission prevention (duplicate check)
- Response count calculation
- Survey analytics
- File attachments
- Survey status (Draft, Published, Active, Archived)

**Key Endpoints:**
- `POST /api/survey/save` - Create/update survey
- `POST /api/survey-step/save` - Save survey step and field values
- `POST /api/survey/check-submission` - Check if survey submitted
- `POST /api/survey/analytics` - Get survey analytics

**Response Tracking:**
- `survey_responses` - Stores individual response records
- `survey_submissions` - Tracks unique submissions per user-meeting-survey
- `survey_field_values` - Stores actual field responses

**Analytics Features:**
- Total responses count
- Completion rate calculation
- Responses by date (last 7 days)
- Question-wise answer distribution
- Support for all question types

---

### 6. Calendar System

**Controller:** `CalendarController`

**Service:** `CalendarService`

**Features:**
- Month view (all days with meetings)
- Week view (Monday-Sunday)
- Day view (meetings for specific date)
- Server-side filtering support
- Calendar statistics
- Organization-scoped queries

**Filter Support:**
- Meeting type filter
- Attendees count filter (1-on-1, small, medium, large)
- Duration filter (15, 30, 60, 120+ minutes)
- Status filter (upcoming, completed, cancelled)

**Key Methods:**
- `buildMonth()` - Builds month calendar data
- `buildWeek()` - Builds week calendar data
- `buildDay()` - Builds day calendar data
- All methods support query modifiers for filtering

---

### 7. Notification System

**Service:** `FcmService`

**Job:** `SendMeetingNotificationJob`

**Command:** `SendMeetingReminders`

**Controllers:**
- `FcmTokenController` - Token management
- `NotificationPreferencesController` - User preferences

**Models:**
- `FcmToken` - Device tokens
- `NotificationPreference` - User preferences
- `MeetingNotification` - Notification settings

**Features:**
- Firebase Cloud Messaging integration
- Token registration/unregistration
- Multi-device support per user
- Scheduled reminders
- Meeting start notifications
- Invalid token cleanup
- Notification preferences
- Queue-based sending

**Notification Flow:**
1. Meeting created/updated → Notification scheduled
2. Reminder settings → Scheduled in `meeting_fcm_notifications`
3. Scheduler runs every minute → Finds due notifications
4. Job dispatched → Sends via FCM
5. Status updated → `sent` or `failed`

**Token Management:**
- Tokens stored in `fcm_tokens` table
- Platform tracking (ios, android, web)
- Device ID tracking
- Automatic cleanup of invalid tokens

---

### 8. Account Management

**Controllers:**
- `ChangeEmailController` - Email change with verification
- `ChangePasswordController` - Password change
- `DeleteAccountController` - Account deletion
- `ExportDataController` - GDPR data export
- `PrivacySettingsController` - Privacy settings

**Features:**
- Email change with token verification
- Password change with current password verification
- Account deletion (soft delete support)
- Data export (JSON format)
- Privacy settings management

---

### 9. Support System

**Controller:** `SupportController`

**Model:** `SupportMessage`

**Features:**
- Support contact form
- Message tracking
- Email notifications

---

## 🗄️ Database Schema

### Core Tables

**Users & Organizations:**
- `users` - User accounts
- `organizations` - Organizations
- `organization_users` - User-organization pivot (with role)
- `user_profiles` - Extended user profiles

**Contacts:**
- `contacts` - Contact information
- `contact_favourites` - Favorites tracking

**Meetings:**
- `meetings` - Meeting records
- `meeting_attendees` - Meeting-contact pivot
- `meeting_notifications` - Notification settings
- `meeting_fcm_notifications` - Scheduled notifications

**Surveys:**
- `surveys` - Survey definitions
- `survey_steps` - Survey steps
- `survey_fields` - Survey questions
- `survey_field_values` - User responses
- `survey_responses` - Response records
- `survey_submissions` - Submission tracking
- `survey_attachments` - File attachments

**Notifications:**
- `fcm_tokens` - FCM device tokens
- `notification_preferences` - User preferences
- `notifications` - General notifications

**Support:**
- `support_messages` - Support requests

### Key Relationships

```
User ←→ Organization (Many-to-Many via organization_users)
User → UserProfile (One-to-Many)
Organization → Contact (One-to-Many)
Organization → Meeting (One-to-Many)
Organization → Survey (One-to-Many)
Meeting ←→ Contact (Many-to-Many via meeting_attendees)
Meeting → Survey (Many-to-One)
Survey → SurveyStep (One-to-Many)
SurveyStep → SurveyField (One-to-Many)
SurveyField → SurveyFieldValue (One-to-Many)
User → SurveySubmission (One-to-Many)
User → SurveyResponse (One-to-Many)
User → FcmToken (One-to-Many)
```

---

## 🔌 API Endpoints Reference

### Authentication Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/register` | Register new user | ❌ |
| POST | `/api/login` | Login user | ❌ |
| POST | `/api/signup` | Signup with organization | ❌ |
| POST | `/api/auth/forgot-password` | Request password reset | ❌ |
| POST | `/api/auth/google` | Google OAuth login | ❌ |

### Organization Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/organizations` | List organizations (public) | ❌ |
| POST | `/api/organizations/search` | Search organization | ❌ |
| POST | `/api/organizations/index` | List user's organizations | ✅ |
| POST | `/api/organizations/show` | Get organization | ✅ |
| POST | `/api/organizations/save` | Create/update | ✅ |
| POST | `/api/organizations/delete` | Delete | ✅ |

### Contact Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/contacts/index` | List contacts (paginated) | ✅ |
| POST | `/api/contacts/show` | Get contact | ✅ |
| POST | `/api/contacts/save` | Create/update | ✅ |
| POST | `/api/contacts/delete` | Delete | ✅ |
| POST | `/api/contacts/dropdown` | Get dropdown list | ✅ |
| POST | `/api/contacts/favourite` | Toggle favorite | ✅ |
| POST | `/api/contacts/state` | Get statistics | ✅ |
| POST | `/api/contacts/import` | Bulk import | ✅ |

### Meeting Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/meeting/index` | List meetings (with filters) | ✅ |
| POST | `/api/meeting/show` | Get meeting | ✅ |
| POST | `/api/meeting/save` | Create/update | ✅ |
| POST | `/api/meeting/delete` | Delete | ✅ |
| POST | `/api/meeting/current-month` | Month view (with filters) | ✅ |
| POST | `/api/meeting/current-week` | Week view (with filters) | ✅ |
| POST | `/api/meeting/current-day` | Day view (with filters) | ✅ |

### Survey Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/survey/index` | List surveys | ✅ |
| POST | `/api/survey/show` | Get survey | ✅ |
| POST | `/api/survey/save` | Create/update | ✅ |
| POST | `/api/survey/delete` | Delete | ✅ |
| POST | `/api/survey/dropdown` | Get dropdown | ✅ |
| POST | `/api/survey/state` | Get statistics | ✅ |
| POST | `/api/survey/analytics` | Get analytics | ✅ |
| POST | `/api/survey/check-submission` | Check submission status | ✅ |

### Survey Step Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/survey-step/index` | Get survey with steps | ✅ |
| POST | `/api/survey-step/show` | Get step by ID | ✅ |
| POST | `/api/survey-step/save` | Save step and responses | ✅ |
| POST | `/api/survey-step/delete` | Delete step | ✅ |

### Calendar Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/calendar/state` | Get statistics | ✅ |

### FCM Token Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/fcm/register` | Register device token | ✅ |
| POST | `/api/fcm/unregister` | Unregister token | ✅ |

### Notification Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/notifications/preferences` | Get preferences | ✅ |
| POST | `/api/notifications/preferences` | Update preferences | ✅ |

### Account Management Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/account/change-email` | Request email change | ✅ |
| GET | `/api/account/verify-email-change` | Verify email change | ✅ |
| POST | `/api/account/change-password` | Change password | ✅ |
| POST | `/api/account/delete` | Delete account | ✅ |
| GET | `/api/account/export-data` | Export user data | ✅ |
| GET | `/api/account/export-data/download/{filename}` | Download export | ✅ |

### Privacy & Support Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/privacy/settings` | Get privacy settings | ✅ |
| POST | `/api/privacy/settings` | Update privacy settings | ✅ |
| POST | `/api/support/contact` | Submit support request | ✅ |

---

## 🔧 Services & Business Logic

### CalendarService

**Purpose:** Builds calendar data structures for month/week/day views

**Methods:**
- `buildMonth()` - Builds month array with meetings
- `buildWeek()` - Builds week array (Mon-Sun)
- `buildDay()` - Builds day array with meetings

**Features:**
- Supports query modifiers for filtering
- Returns only days with meetings
- Organization-scoped queries
- Date range calculations

### FcmService

**Purpose:** Handles Firebase Cloud Messaging operations

**Methods:**
- `sendNotification()` - Send to single device
- `sendToMultiple()` - Send to multiple devices
- `sendToUser()` - Send to all user devices
- `validateToken()` - Validate FCM token format

**Features:**
- Supports environment variable or file-based credentials
- Invalid token cleanup
- Error handling and logging
- Data payload conversion (all values to strings)

---

## 🎯 Key Features Implementation

### 1. Meeting Filters (Server-Side)

**Trait:** `AppliesMeetingFilters`

**Implementation:**
- Meeting type: Exact match
- Attendees: Subquery count (1-on-1, small: 2-5, medium: 6-15, large: 16+)
- Duration: Exact match or >= 120 for 2+ hours
- Status: Maps frontend values to database values

**Usage:**
- Applied in `CalendarController` (month/week/day views)
- Applied in `MeetingController` (index endpoint)

### 2. Survey Response Tracking

**Two-Table System:**
- `survey_responses` - Individual response records
- `survey_submissions` - Unique submission tracking

**Response Count Logic:**
- Counts unique submissions from `survey_submissions` (per meeting)
- Falls back to `survey_responses` for backward compatibility
- Handles NULL meeting_id cases

**Submission Prevention:**
- Unique constraint on `[user_id, meeting_id, survey_id]`
- Check API: `POST /api/survey/check-submission`
- Returns submission status with timestamp

### 3. Survey Analytics

**Endpoint:** `POST /api/survey/analytics`

**Calculates:**
- Total responses (unique submissions)
- Completion rate (based on required fields)
- Responses by date (last 7 days)
- Question-wise answer distribution

**Question Type Handling:**
- Choice-based: Counts per option
- Checkboxes: Handles JSON arrays
- Text-based: Response vs no-response counts
- Rating Scale: Generates options dynamically

### 4. Notification Scheduling

**Flow:**
1. Meeting created → Notifications configured
2. Reminder times calculated → Stored in `meeting_fcm_notifications`
3. Scheduler runs every minute → Finds due notifications
4. Job dispatched → Sends via FCM
5. Status updated → Prevents duplicates

**Command:** `meetings:send-reminders`
- Runs every minute (via cron)
- Finds pending notifications where `scheduled_at <= now()`
- Dispatches jobs for each notification
- Handles "starting soon" notifications (5 minutes before)

---

## 📊 Data Flow Examples

### Meeting Creation Flow

```
1. POST /api/meeting/save
   ↓
2. Validate request (StoreMeetingRequest)
   ↓
3. Create/update meeting
   ↓
4. Sync attendees (Many-to-Many)
   ↓
5. Create notification settings
   ↓
6. Schedule reminder notifications
   ↓
7. Send "meeting_created" notification
   ↓
8. Return meeting resource with relationships
```

### Survey Submission Flow

```
1. POST /api/survey-step/save (with field_values)
   ↓
2. Validate request (StoreSurveyStepRequest)
   ↓
3. Create/update survey step
   ↓
4. Create/update survey fields
   ↓
5. Save field values (SurveyFieldValue)
   ↓
6. Create/update survey response (SurveyResponse)
   ↓
7. Record submission (SurveySubmission) - if meeting_id exists
   ↓
8. Return survey step with fields
```

### Notification Sending Flow

```
1. Scheduler runs (every minute)
   ↓
2. Finds pending notifications (scheduled_at <= now())
   ↓
3. Dispatches SendMeetingNotificationJob for each
   ↓
4. Job executes:
   - Gets user's FCM tokens
   - Sends via FcmService
   - Updates notification status
   ↓
5. Invalid tokens automatically deleted
```

---

## 🔐 Security Features

1. **Authentication:**
   - Laravel Sanctum token-based auth
   - Password hashing (bcrypt)
   - Rate limiting on login

2. **Authorization:**
   - Organization-based access control
   - User can only access their organization's data
   - Creator-based permissions

3. **Input Validation:**
   - Form Request classes for all endpoints
   - Type validation
   - Existence checks (foreign keys)

4. **Data Protection:**
   - SQL injection prevention (Eloquent ORM)
   - XSS prevention (API responses)
   - CSRF protection (web routes)

5. **Account Security:**
   - Email verification for email changes
   - Current password required for password change
   - Account deletion confirmation

---

## 🧪 Testing

**Framework:** Pest PHP

**Test Structure:**
- Feature tests for all modules
- Unit tests
- Test factories for all models

**Coverage:**
- Authentication flows
- CRUD operations
- Validation testing
- Business logic testing
- API endpoint testing

---

## 📝 Recent Implementations

1. **Meeting Filters** (Server-Side)
   - Filter by type, attendees, duration, status
   - Applied to all calendar views
   - Efficient database queries

2. **Survey Submission Tracking**
   - Prevents duplicate submissions
   - Submission status check API
   - Response count calculation

3. **Survey Analytics**
   - Comprehensive analytics endpoint
   - Question-wise distributions
   - Completion rate calculation

4. **FCM Token Migration Fix**
   - Changed TEXT to VARCHAR(500)
   - Idempotent migration
   - Handles existing tables

---

## 🚀 Deployment Requirements

1. **Server Requirements:**
   - PHP 8.2+
   - MySQL 5.7+ or MariaDB 10.3+
   - Composer
   - Node.js & NPM (for assets)

2. **Services Required:**
   - Queue worker (for notifications)
   - Scheduler (cron job)
   - Firebase service account file

3. **Environment Variables:**
   - Database configuration
   - Firebase credentials
   - App key, URL, timezone
   - Queue connection

4. **Cron Jobs:**
   ```bash
   * * * * * php artisan schedule:run
   ```

5. **Queue Worker:**
   ```bash
   php artisan queue:work
   ```

---

## 📈 Statistics & Analytics

The application provides statistics endpoints for:
- **Contacts:** Total, favorites count
- **Meetings:** Total, this week, today
- **Surveys:** Total, active, responses, drafts
- **Calendar:** Meetings by period
- **User Profiles:** Activity tracking

---

## 🔄 Data Relationships Summary

```
User
  ├── Organizations (Many-to-Many)
  ├── UserProfile (One-to-Many)
  ├── Contacts (created_by)
  ├── Meetings (created_by)
  ├── Surveys (created_by)
  ├── SurveySubmissions (One-to-Many)
  ├── SurveyResponses (One-to-Many)
  └── FcmTokens (One-to-Many)

Organization
  ├── Users (Many-to-Many)
  ├── Contacts (One-to-Many)
  ├── Meetings (One-to-Many)
  └── Surveys (One-to-Many)

Meeting
  ├── Organization (Many-to-One)
  ├── Creator (Many-to-One)
  ├── Survey (Many-to-One)
  ├── Attendees (Many-to-Many via Contact)
  ├── Notifications (One-to-Many)
  └── SurveySubmissions (One-to-Many)

Survey
  ├── Organization (Many-to-One)
  ├── Creator (Many-to-One)
  ├── Steps (One-to-Many)
  ├── Responses (One-to-Many)
  └── Submissions (One-to-Many)

SurveyStep
  ├── Survey (Many-to-One)
  └── Fields (One-to-Many)

SurveyField
  ├── Survey (Many-to-One)
  ├── Step (Many-to-One)
  ├── Organization (Many-to-One)
  └── Values (One-to-Many)
```

---

## 🎨 Code Quality & Patterns

1. **Repository Pattern** - Used for complex operations
2. **Service Layer** - Business logic separation
3. **API Resources** - Consistent response formatting
4. **Form Requests** - Centralized validation
5. **Traits** - Reusable functionality
6. **Queue Jobs** - Async processing
7. **Console Commands** - Scheduled tasks
8. **Database Transactions** - Data integrity
9. **Logging** - Comprehensive error tracking
10. **Error Handling** - Try-catch blocks with proper responses

---

## 📚 Documentation Files

- `PROJECT_OVERVIEW.md` - This file
- `MEETING_FILTERS_IMPLEMENTATION.md` - Meeting filters docs
- `SURVEY_SUBMISSION_CHECK_API.md` - Survey submission API
- `SURVEY_SUBMISSION_TRACKING.md` - Submission tracking docs
- Various Firebase setup guides

---

## 🔍 Key Implementation Details

### Survey Response Count Logic

```php
// In Survey model
public function getResponseCount(): int
{
    // Count unique submissions per meeting
    $submissionCount = $this->surveySubmissions()
        ->distinct('meeting_id')
        ->whereNotNull('meeting_id')
        ->count('meeting_id');
    
    // Count responses without meeting_id (backward compatibility)
    $responseCountWithoutMeeting = $this->surveyResponses()
        ->whereNull('meeting_id')
        ->distinct('user_id')
        ->count('user_id');
    
    return $submissionCount + $responseCountWithoutMeeting;
}
```

### Meeting Filter Implementation

```php
// In AppliesMeetingFilters trait
protected function applyMeetingFilters(Builder $query, array $filters): Builder
{
    // Meeting type: exact match
    // Attendees: subquery count
    // Duration: exact or >= 120
    // Status: map frontend to database values
}
```

### Notification Scheduling

```php
// In MeetingController
private function scheduleReminderNotifications($meetingId, $meetingDate, $meetingTime)
{
    // Calculate reminder times based on notification settings
    // Store in meeting_fcm_notifications table
    // Scheduler picks them up when due
}
```

---

## 🎯 Project Strengths

1. **Well-Structured** - Clear separation of concerns
2. **Scalable** - Multi-tenant architecture
3. **Feature-Rich** - Comprehensive functionality
4. **Well-Documented** - Extensive documentation
5. **Type-Safe** - Strong typing with PHP 8.2+
6. **Testable** - Test coverage in place
7. **Maintainable** - Clean code patterns
8. **Secure** - Proper authentication and authorization
9. **Performant** - Efficient database queries
10. **Extensible** - Easy to add new features

---

This is a production-ready, enterprise-level meeting management and survey platform with comprehensive features, robust architecture, and excellent code quality.











