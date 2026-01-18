# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 meeting management API (Ongoing Meet API) with Firebase push notifications support. It serves as the backend for a Flutter mobile application.

## Development Commands

```bash
# Setup (install dependencies, generate key, run migrations, build assets)
composer setup

# Run development server with queue worker, logs, and vite
composer dev

# Run tests
composer test

# Run a single test file
php artisan test --filter=MeetingCreateTest

# Run Pest tests directly
./vendor/bin/pest tests/Feature/MeetingCreateTest.php

# Code formatting with Pint
./vendor/bin/pint

# Run migrations
php artisan migrate

# Run queue worker manually
php artisan queue:listen --tries=1

# Send meeting reminder notifications (runs every minute via scheduler)
php artisan meetings:send-reminders
```

## Architecture

### API Design Pattern
- All API routes use POST for data operations (including reads) except some GET endpoints
- Routes follow `/resource/action` pattern: `/contacts/index`, `/contacts/save`, `/contacts/show`
- Authentication via Laravel Sanctum tokens
- Resources organized under `App\Http\Controllers\Api\`

### Multi-tenancy via Organizations
- Users belong to organizations through `organization_users` pivot table
- If a user has no organization, one is automatically created on first resource creation
- All resources (contacts, meetings, surveys) are scoped to the user's organization

### Key Domain Models
- **Meeting**: Core entity with attendees, notifications, and optional surveys
- **Contact**: Organization members/attendees with favorites system
- **Survey**: Multi-step surveys with fields, attachments, and responses
- **Organization**: Multi-tenant container for all user data

### Services
- `FcmService`: Firebase Cloud Messaging for push notifications, handles token management and auto-cleanup of invalid tokens
- `CalendarService`: Static methods for building day/week/month calendar views with meeting data

### Notification System
- Firebase Cloud Messaging (FCM) via `kreait/firebase-php`
- `SendMeetingReminders` command runs via scheduler for reminder notifications
- `SendMeetingNotificationJob` queues FCM notifications asynchronously
- Notification records stored in `meeting_fcm_notifications` table
- FCM tokens stored in `fcm_tokens` table, auto-cleaned when invalid

### Repositories
Located in `App\Repositories\` for data access patterns:
- `OrganizationRepository`, `UserRepository`, `UserProfileRepository`, `SurveyStepRepository`

### Form Requests
Store requests are in `App\Http\Requests\` (e.g., `StoreMeetingRequest`, `StoreContactRequest`)

### API Resources
JSON transformations in `App\Http\Resources\` (e.g., `MeetingResource`, `ContactResource`)

### Traits
- `AppliesMeetingFilters`: Reusable meeting filtering logic

## Firebase Configuration

Firebase credentials can be provided via:
1. Environment variable `FIREBASE_CREDENTIALS_JSON` (JSON string)
2. File path in `FIREBASE_CREDENTIALS_PATH` (defaults to `storage/app/firebase/service-account.json`)

## Testing

Uses Pest for testing. Feature tests in `tests/Feature/` cover:
- Contact creation
- Meeting creation
- Meeting reminders
- Survey creation
