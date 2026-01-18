<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactGroupController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\SurveyStepController;
use App\Http\Controllers\Api\SurveyAttachmentController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\NotificationPreferencesController;
use App\Http\Controllers\Api\ChangeEmailController;
use App\Http\Controllers\Api\ChangePasswordController;
use App\Http\Controllers\Api\DeleteAccountController;
use App\Http\Controllers\Api\PrivacySettingsController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ExportDataController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\TestEmailController;
use App\Http\Controllers\Auth\VerifyEmailController;


// Health check routes (unauthenticated for monitoring)
Route::get('/health', [HealthController::class, 'check']);
Route::get('/ping', [HealthController::class, 'ping']);

// Email verification routes (unauthenticated)
Route::get('/email/verify/{token}', [VerifyEmailController::class, 'verify']);
Route::post('/email/verify/resend', [VerifyEmailController::class, 'resend']);

// Test email routes (for development/testing only - remove in production)
if (config('app.debug') || app()->environment('local')) {
    Route::post('/test/email', [TestEmailController::class, 'testEmail']);
    Route::post('/test/verification-email', [TestEmailController::class, 'testVerificationEmail']);
}

// Authentication routes with rate limiting (5 attempts per minute)
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/signup', [LoginController::class, 'signup']);
    Route::post('/auth/forgot-password', [LoginController::class, 'forgotPassword']);
    Route::post('/auth/google', [LoginController::class, 'googleLogin']);
});

// Organization routes (public)
Route::get('/organizations', [OrganizationController::class, 'index']);
Route::post('/organizations/search', [OrganizationController::class, 'getByName']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    });

    // Token refresh endpoint
    Route::post('/auth/refresh-token', [LoginController::class, 'refreshToken']);

    // Organization routes
    Route::post('/organizations/index', [OrganizationController::class, 'indexPost']);
    Route::post('/organizations/show', [OrganizationController::class, 'show']);
    Route::post('/organizations/save', [OrganizationController::class, 'save']);
    Route::post('/organizations/delete', [OrganizationController::class, 'delete']);
    // Legacy route for backward compatibility
    Route::post('/organizations', [OrganizationController::class, 'store']);

    // Contacts listing with pagination
    Route::post('/contacts/index', [ContactController::class, 'index']);
    
    // Get single contact by ID
    Route::post('/contacts/show', [ContactController::class, 'show']);
    
    // Save contact (create/update)
    Route::post('/contacts/save', [ContactController::class, 'save']);
    
    // Delete contact
    Route::post('/contacts/delete', [ContactController::class, 'delete']);
    
    // Get contacts dropdown (id and name only)
    Route::post('/contacts/dropdown', [ContactController::class, 'dropdown']);
    
    // Toggle contact favourite status
    Route::post('/contacts/favourite', [ContactController::class, 'toggleFavourite']);
    
    // Contact statistics
    Route::post('/contacts/state', [ContactController::class, 'state']);
    
    // Bulk import contacts
    Route::post('/contacts/import', [ContactController::class, 'import']);
 
    // Get contact groups list (POST)
    Route::post('/contact-groups', [ContactGroupController::class, 'index']);

    // Survey routes
    Route::post('/survey/index', [SurveyController::class, 'index']);
    Route::post('/survey/show', [SurveyController::class, 'show']);
    Route::post('/survey/save', [SurveyController::class, 'save']);
    Route::post('/survey/delete', [SurveyController::class, 'delete']);
    Route::post('/survey/dropdown', [SurveyController::class, 'dropdown']);
    Route::post('/survey/state', [SurveyController::class, 'state']);
    Route::post('/survey/analytics', [SurveyController::class, 'analytics']);
    Route::post('/survey/check-submission', [SurveyController::class, 'checkSubmission']);
    
    // Survey Step routes
    Route::post('/survey-step/index', [SurveyStepController::class, 'index']);
    Route::post('/survey-step/show', [SurveyStepController::class, 'show']);
    Route::post('/survey-step/save', [SurveyStepController::class, 'save']);
    Route::post('/survey-step/delete', [SurveyStepController::class, 'delete']);
    
    // Survey Attachment routes
    Route::post('/survey/attachment/index', [SurveyAttachmentController::class, 'index']);
    Route::post('/survey/attachment/save', [SurveyAttachmentController::class, 'save']);
    Route::post('/survey/attachment/show', [SurveyAttachmentController::class, 'show']);
    Route::post('/survey/attachment/delete', [SurveyAttachmentController::class, 'delete']);
    
    // Meeting routes
    Route::post('/meeting/index', [MeetingController::class, 'index']);
    Route::post('/meeting/show', [MeetingController::class, 'show']);
    Route::post('/meeting/save', [MeetingController::class, 'save']);
    Route::post('/meeting/delete', [MeetingController::class, 'delete']);
    
    // current month
    Route::post('/meeting/current-month', [CalendarController::class, 'currentMonth']);
    // current week
    Route::post('/meeting/current-week', [CalendarController::class, 'currentWeek']);
    // current day
    Route::post('/meeting/current-day', [CalendarController::class, 'currentDay']);
    // calendar statistics
    Route::post('/calendar/state', [CalendarController::class, 'statistics']);
    
    // User Profile CRUD routes (for user_profiles table)
    Route::post('/user-profiles/index', [UserProfileController::class, 'index']);
    Route::post('/user-profiles/show', [UserProfileController::class, 'show']);
    Route::post('/user-profiles/save', [UserProfileController::class, 'save']);
    Route::post('/user-profiles/delete', [UserProfileController::class, 'delete']);
    Route::post('/user-profiles/state', [UserProfileController::class, 'state']);
    Route::post('/user-profiles/activity', [UserProfileController::class, 'activity']);
    
    // FCM Token Management
    Route::post('/fcm/register', [FcmTokenController::class, 'register']);
    Route::post('/fcm/unregister', [FcmTokenController::class, 'unregister']);
    
    // Notification Preferences
    Route::get('/notifications/preferences', [NotificationPreferencesController::class, 'index']);
    Route::post('/notifications/preferences', [NotificationPreferencesController::class, 'store']);
    
    // Account Management
    Route::post('/account/change-email', [ChangeEmailController::class, 'changeEmail']);
    Route::get('/account/verify-email-change', [ChangeEmailController::class, 'verifyEmailChange']);
    Route::post('/account/change-password', [ChangePasswordController::class, 'changePassword']);
    Route::post('/account/delete', [DeleteAccountController::class, 'deleteAccount']);
    Route::get('/account/export-data', [ExportDataController::class, 'exportData']);
    Route::get('/account/export-data/download/{filename}', [ExportDataController::class, 'download']);
    
    // Privacy Settings (DEPRECATED - Frontend no longer uses these endpoints)
    // These endpoints return 410 Gone status
    // TODO: Monitor for 30 days, then remove if no usage detected
    // Deprecation date: 2025-01-XX
    // @deprecated Privacy features have been removed from the frontend
    Route::get('/privacy/settings', [PrivacySettingsController::class, 'index']);
    Route::post('/privacy/settings', [PrivacySettingsController::class, 'store']);
    
    // Support
    Route::post('/support/contact', [SupportController::class, 'contact']);

    // Subscription routes
    Route::prefix('subscription')->group(function () {
        // GET endpoints for Flutter frontend (as per documentation)
        Route::get('/plans', [SubscriptionController::class, 'plans']);
        Route::get('/current', [SubscriptionController::class, 'current']);
        Route::get('/usage', [SubscriptionController::class, 'usage']);
        
        // POST endpoints for subscription management
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/verify-payment', [SubscriptionController::class, 'verifyPayment']);
        Route::post('/upgrade', [SubscriptionController::class, 'upgrade']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('/resume', [SubscriptionController::class, 'resume']);
        Route::post('/change-billing', [SubscriptionController::class, 'changeBilling']);
        Route::post('/limits', [SubscriptionController::class, 'limits']);
        Route::post('/check-limit', [SubscriptionController::class, 'checkLimit']);

        // Add-ons
        Route::post('/addons', [SubscriptionController::class, 'addons']);
        Route::post('/addons/purchase', [SubscriptionController::class, 'purchaseAddon']);
        Route::post('/addons/cancel', [SubscriptionController::class, 'cancelAddon']);
        Route::post('/addons/active', [SubscriptionController::class, 'activeAddons']);

        // Invoices
        Route::post('/invoices', [SubscriptionController::class, 'invoices']);
        Route::get('/invoices/download/{id}', [SubscriptionController::class, 'downloadInvoice']);
    });
});

// Razorpay webhook with rate limiting (no auth but limited to prevent abuse)
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/webhooks/razorpay', [WebhookController::class, 'razorpay']);
});
