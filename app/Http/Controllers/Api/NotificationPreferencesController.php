<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NotificationPreferencesController extends Controller
{
    /**
     * Get the current authenticated user's notification preferences.
     * GET /api/notifications/preferences
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'data' => null
                ], 401);
            }

            $preferences = NotificationPreference::where('user_id', $user->id)->first();

            // If no preferences exist, return defaults
            if (!$preferences) {
                $defaultReminders = [15];
                return response()->json([
                    'success' => true,
                    'message' => 'Notification preferences retrieved successfully',
                    'data' => [
                        'push_notifications_enabled' => true,
                        'email_notifications_enabled' => true,
                        'email_meeting_reminders' => true,
                        // Deprecated fields - always return false for backward compatibility
                        'email_meeting_updates' => false,
                        'email_meeting_cancellations' => false,
                        'meeting_reminders' => $defaultReminders,
                        'reminder_15min' => in_array(15, $defaultReminders),
                        'reminder_30min' => in_array(30, $defaultReminders),
                        'reminder_1hour' => in_array(60, $defaultReminders),
                        'notification_sound' => true,
                        'notification_badge' => true,
                    ]
                ], 200);
            }

            // Convert meeting_reminders array to individual fields
            $remindersArray = $preferences->meeting_reminders ?? [15];
            if (!is_array($remindersArray)) {
                $remindersArray = [15];
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification preferences retrieved successfully',
                'data' => [
                    'push_notifications_enabled' => $preferences->push_notifications_enabled,
                    'email_notifications_enabled' => $preferences->email_notifications_enabled,
                    'email_meeting_reminders' => $preferences->email_meeting_reminders,
                    // Deprecated fields - always return false for backward compatibility
                    // These fields are kept in response for older app versions but are not used
                    'email_meeting_updates' => false,
                    'email_meeting_cancellations' => false,
                    'meeting_reminders' => $remindersArray,
                    'reminder_15min' => in_array(15, $remindersArray),
                    'reminder_30min' => in_array(30, $remindersArray),
                    'reminder_1hour' => in_array(60, $remindersArray),
                    'notification_sound' => $preferences->notification_sound,
                    'notification_badge' => $preferences->notification_badge,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve notification preferences', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notification preferences',
                'data' => null
            ], 500);
        }
    }

    /**
     * Update the current authenticated user's notification preferences.
     * POST /api/notifications/preferences
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'data' => null
                ], 401);
            }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'push_notifications_enabled' => 'sometimes|boolean',
                'email_notifications_enabled' => 'sometimes|boolean',
                'email_meeting_reminders' => 'sometimes|boolean',
                'email_meeting_updates' => 'sometimes|boolean',
                'email_meeting_cancellations' => 'sometimes|boolean',
                'meeting_reminders' => 'sometimes|array',
                'meeting_reminders.*' => 'integer|in:15,30,60',
                'notification_sound' => 'sometimes|boolean',
                'notification_badge' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessage = 'Invalid request data';
                
                // Check for specific meeting_reminders validation errors
                if ($errors->has('meeting_reminders.*')) {
                    $invalidValues = [];
                    foreach ($request->input('meeting_reminders', []) as $value) {
                        if (!in_array($value, [15, 30, 60])) {
                            $invalidValues[] = $value;
                        }
                    }
                    if (!empty($invalidValues)) {
                        $errorMessage = 'Invalid reminder value: ' . implode(', ', $invalidValues) . '. Valid values are: 15, 30, 60';
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => null
                ], 400);
            }

            // Validate meeting_reminders array for duplicates
            if ($request->has('meeting_reminders')) {
                $reminders = $request->input('meeting_reminders');
                if (count($reminders) !== count(array_unique($reminders))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'meeting_reminders contains duplicate values',
                        'data' => null
                    ], 400);
                }
            }

            // Get or create preferences
            $preferences = NotificationPreference::firstOrNew(['user_id' => $user->id]);

            // Default values
            $defaults = [
                'push_notifications_enabled' => true,
                'email_notifications_enabled' => true,
                'email_meeting_reminders' => true,
                // Deprecated fields - always default to false
                // These fields are accepted for backward compatibility but are set to false
                'email_meeting_updates' => false,
                'email_meeting_cancellations' => false,
                'meeting_reminders' => [15],
                'notification_sound' => true,
                'notification_badge' => true,
            ];

            // Update only provided fields (partial update)
            $updateData = [];
            foreach ($defaults as $key => $defaultValue) {
                // For deprecated email notification fields, always set to false regardless of input
                if ($key === 'email_meeting_updates' || $key === 'email_meeting_cancellations') {
                    // Accept field if sent (for backward compatibility) but always set to false
                    if ($request->has($key) || !$preferences->exists) {
                        $updateData[$key] = false;
                    }
                } elseif ($request->has($key)) {
                    $updateData[$key] = $request->input($key);
                } elseif (!$preferences->exists) {
                    // If creating new record, use defaults for missing fields
                    $updateData[$key] = $defaultValue;
                }
            }

            // If no fields provided, use defaults for new record
            if (empty($updateData) && !$preferences->exists) {
                $updateData = $defaults;
            }

            // Update or create preferences
            if ($preferences->exists) {
                // Update existing record
                if (!empty($updateData)) {
                    $preferences->update($updateData);
                }
            } else {
                // Create new record
                $preferences->fill($updateData);
                $preferences->save();
            }

            Log::info('Notification preferences updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update notification preferences', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification preferences',
                'data' => null
            ], 500);
        }
    }
}


