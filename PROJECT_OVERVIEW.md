# MeetUI - Project Overview

## 📋 Project Summary

**MeetUI** is a comprehensive meeting management and survey platform built with **Laravel 12** (PHP 8.2+) as the backend API. It provides features for managing meetings, contacts, surveys, and notifications with Firebase Cloud Messaging (FCM) integration.

---

## 🏗️ Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **PHP Version**: 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum (Token-based)
- **Queue System**: Laravel Queues
- **Testing**: Pest PHP

### Key Dependencies
- `kreait/firebase-php` (^7.23) - Firebase Cloud Messaging
- `google/apiclient` - Google API integration
- `laravel/sanctum` (^4.2) - API authentication

---

## 📦 Core Modules

### 1. **Authentication & User Management**
- User registration/login
- Google OAuth login
- Password reset
- Email change with verification
- Account deletion
- User profiles management

**Controllers:**
- `LoginController` - Authentication
- `RegisterController` - User registration
- `ChangeEmailController` - Email management
- `ChangePasswordController` - Password management
- `DeleteAccountController` - Account deletion
- `UserProfileController` - Profile CRUD

### 2. **Organization Management**
- Multi-tenant organization support
- Organization CRUD operations
- User-organization relationships
- Organization search

**Controller:** `OrganizationController`

**Models:**
- `Organization`
- `User` (with organizations relationship)

### 3. **Contact Management**
- Contact CRUD operations
- Contact favorites
- Contact groups
- Bulk contact import
- Contact statistics
- Contact search and filtering

**Controller:** `ContactController`

**Models:**
- `Contact`
- `ContactFavourite`

### 4. **Meeting Management**
- Meeting CRUD operations
- Meeting attendees management
- Meeting notifications (FCM)
- Calendar views (month, week, day)
- Meeting filters (type, attendees, duration, status)
- Meeting statistics

**Controllers:**
- `MeetingController` - Meeting CRUD
- `CalendarController` - Calendar views

**Models:**
- `Meeting`
- `MeetingNotification`
- `Contact` (attendees)

**Features:**
- Meeting types: Video Call, In-Person Meeting, Phone Call, Online Meeting
- Status: Created, Scheduled, Completed, Cancelled, Pending, Rescheduled
- Meeting reminders and notifications
- Survey integration

### 5. **Survey System**
- Survey CRUD operations
- Multi-step surveys
- Survey fields (Multiple Choice, Checkboxes, Dropdown, Rating Scale, Text, etc.)
- Survey responses tracking
- Survey submissions (prevents duplicate submissions)
- Survey analytics
- Survey attachments
- Response count tracking

**Controllers:**
- `SurveyController` - Survey CRUD, Analytics, Submission check
- `SurveyStepController` - Survey steps management
- `SurveyAttachmentController` - Survey attachments

**Models:**
- `Survey`
- `SurveyStep`
- `SurveyField`
- `SurveyFieldValue`
- `SurveyResponse`
- `SurveySubmission`
- `SurveyAttachment`

**Key Features:**
- Survey response count (unique submissions per meeting)
- Survey submission status check API
- Survey analytics (completion rate, responses by date, question-wise analytics)
- Prevents duplicate submissions per user-meeting-survey

### 6. **Calendar System**
- Month view
- Week view
- Day view
- Calendar statistics
- Meeting filters (server-side)

**Controller:** `CalendarController`

**Service:** `CalendarService`

**Features:**
- Server-side filtering (meeting type, attendees, duration, status)
- Date-based queries
- Organization-scoped data

### 7. **Notification System**
- Firebase Cloud Messaging (FCM) integration
- Push notifications for meetings
- Notification preferences
- FCM token management
- Scheduled meeting reminders
- Meeting start notifications

**Controllers:**
- `FcmTokenController` - Token registration/unregistration
- `NotificationPreferencesController` - User preferences

**Services:**
- `FcmService` - FCM message sending

**Jobs:**
- `SendMeetingNotificationJob` - Queued notification sending

**Commands:**
- `SendMeetingReminders` - Scheduled command for reminders

**Models:**
- `FcmToken`
- `NotificationPreference`
- `MeetingNotification`

**Notification Types:**
- `meeting_created`
- `meeting_updated`
- `meeting_cancelled`
- `meeting_reminder` (configurable minutes before)
- `meeting_starting` (5 minutes before)

### 8. **Account Management**
- Email change with verification
- Password change
- Account deletion
- Data export (GDPR compliance)
- Privacy settings

**Controllers:**
- `ChangeEmailController`
- `ChangePasswordController`
- `DeleteAccountController`
- `ExportDataController`
- `PrivacySettingsController`

### 9. **Support System**
- Support contact form
- Support message tracking

**Controller:** `SupportController`

**Model:** `SupportMessage`

---

## 🗄️ Database Structure

### Core Tables
- `users` - User accounts
- `organizations` - Organizations
- `organization_users` - User-organization pivot
- `contacts` - Contact information
- `meetings` - Meeting records
- `meeting_attendees` - Meeting-contact pivot
- `surveys` - Survey definitions
- `survey_steps` - Survey steps
- `survey_fields` - Survey field definitions
- `survey_field_values` - Survey responses
- `survey_responses` - Survey response records
- `survey_submissions` - Survey submission tracking
- `fcm_tokens` - FCM device tokens
- `meeting_fcm_notifications` - Scheduled notifications
- `notification_preferences` - User notification settings

### Key Relationships
- Users ↔ Organizations (Many-to-Many)
- Meetings ↔ Contacts (Many-to-Many via attendees)
- Meetings ↔ Surveys (One-to-Many)
- Surveys ↔ SurveySteps (One-to-Many)
- SurveySteps ↔ SurveyFields (One-to-Many)
- Users ↔ SurveySubmissions (One-to-Many)

---

## 🔌 API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/signup` - Signup
- `POST /api/auth/forgot-password` - Password reset
- `POST /api/auth/google` - Google OAuth

### Organizations
- `POST /api/organizations/index` - List organizations
- `POST /api/organizations/show` - Get organization
- `POST /api/organizations/save` - Create/update
- `POST /api/organizations/delete` - Delete

### Contacts
- `POST /api/contacts/index` - List contacts (paginated)
- `POST /api/contacts/show` - Get contact
- `POST /api/contacts/save` - Create/update
- `POST /api/contacts/delete` - Delete
- `POST /api/contacts/favourite` - Toggle favorite
- `POST /api/contacts/dropdown` - Get dropdown list
- `POST /api/contacts/state` - Get statistics
- `POST /api/contacts/import` - Bulk import

### Meetings
- `POST /api/meeting/index` - List meetings (with filters)
- `POST /api/meeting/show` - Get meeting
- `POST /api/meeting/save` - Create/update
- `POST /api/meeting/delete` - Delete
- `POST /api/meeting/current-month` - Month view (with filters)
- `POST /api/meeting/current-week` - Week view (with filters)
- `POST /api/meeting/current-day` - Day view (with filters)

### Surveys
- `POST /api/survey/index` - List surveys
- `POST /api/survey/show` - Get survey
- `POST /api/survey/save` - Create/update
- `POST /api/survey/delete` - Delete
- `POST /api/survey/dropdown` - Get dropdown
- `POST /api/survey/state` - Get statistics
- `POST /api/survey/analytics` - Get analytics
- `POST /api/survey/check-submission` - Check submission status

### Survey Steps
- `POST /api/survey-step/index` - List steps
- `POST /api/survey-step/show` - Get step
- `POST /api/survey-step/save` - Create/update
- `POST /api/survey-step/delete` - Delete

### Calendar
- `POST /api/calendar/state` - Calendar statistics

### FCM Tokens
- `POST /api/fcm/register` - Register token
- `POST /api/fcm/unregister` - Unregister token

### Notifications
- `GET /api/notifications/preferences` - Get preferences
- `POST /api/notifications/preferences` - Update preferences

### Account Management
- `POST /api/account/change-email` - Request email change
- `GET /api/account/verify-email-change` - Verify email change
- `POST /api/account/change-password` - Change password
- `POST /api/account/delete` - Delete account
- `GET /api/account/export-data` - Export user data

### Privacy & Support
- `GET /api/privacy/settings` - Get privacy settings
- `POST /api/privacy/settings` - Update privacy settings
- `POST /api/support/contact` - Submit support request

---

## 🎯 Key Features

### 1. Meeting Filters (Server-Side)
- Filter by meeting type
- Filter by attendee count (1-on-1, small, medium, large)
- Filter by duration (15, 30, 60, 120+ minutes)
- Filter by status (upcoming, completed, cancelled)
- Multiple filters can be combined

### 2. Survey Response Tracking
- Unique submission tracking per user-meeting-survey
- Response count calculation
- Prevents duplicate submissions
- Submission status check API

### 3. Survey Analytics
- Total responses
- Completion rate
- Responses by date (last 7 days)
- Question-wise answer distribution
- Support for all question types

### 4. Notification System
- Firebase Cloud Messaging integration
- Scheduled reminders
- Multiple notification types
- Token management
- User preferences

### 5. Multi-Tenant Architecture
- Organization-based data isolation
- User-organization relationships
- Organization-scoped queries

---

## 📁 Project Structure

```
app/
├── Console/Commands/        # Artisan commands
├── Http/
│   ├── Controllers/
│   │   ├── Api/             # API controllers
│   │   └── Auth/             # Authentication controllers
│   ├── Requests/             # Form request validation
│   └── Resources/            # API resources (transformers)
├── Jobs/                     # Queue jobs
├── Mail/                     # Email classes
├── Models/                   # Eloquent models
├── Providers/                # Service providers
├── Repositories/             # Repository pattern
├── Services/                 # Business logic services
└── Traits/                   # Reusable traits

database/
├── migrations/              # Database migrations
├── factories/               # Model factories
└── seeders/                 # Database seeders

routes/
└── api.php                  # API routes

tests/
├── Feature/                 # Feature tests
└── Unit/                    # Unit tests
```

---

## 🔐 Security Features

- Laravel Sanctum authentication
- Password hashing
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- Input validation (Form Requests)
- Organization-based access control
- Email verification for email changes
- Account deletion with soft delete support

---

## 📊 Recent Implementations

1. **Meeting Filters** - Server-side filtering for calendar views
2. **Survey Submission Tracking** - Prevents duplicate submissions
3. **Survey Analytics** - Comprehensive analytics endpoint
4. **Survey Response Count** - Accurate counting of unique submissions
5. **FCM Token Migration Fix** - Fixed TEXT column issue for unique indexes

---

## 🧪 Testing

- Pest PHP testing framework
- Feature tests for all modules
- Unit tests
- Test coverage for major functionality

---

## 📝 Documentation Files

- `MEETING_FILTERS_IMPLEMENTATION.md` - Meeting filters documentation
- `SURVEY_SUBMISSION_CHECK_API.md` - Survey submission API docs
- `SURVEY_SUBMISSION_TRACKING.md` - Survey tracking documentation
- Various Firebase/notification setup guides

---

## 🚀 Deployment Notes

- Requires PHP 8.2+
- MySQL database
- Queue worker must be running
- Scheduler must be configured (cron)
- Firebase service account file needed for notifications
- Environment variables configured in `.env`

---

## 📈 Statistics & Analytics

The application provides statistics endpoints for:
- Contacts (total, favorites)
- Meetings (total, this week, today)
- Surveys (total, active, responses, drafts)
- Calendar (meetings by period)

---

This is a comprehensive meeting management platform with robust survey capabilities, notification system, and multi-tenant support.











