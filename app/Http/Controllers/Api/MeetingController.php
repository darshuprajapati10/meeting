<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingNotification;
use App\Models\NotificationPreference;
use App\Models\Organization;
use App\Traits\AppliesMeetingFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\CalendarService;
use App\Services\SubscriptionService;
use App\Jobs\SendMeetingNotificationJob;

class MeetingController extends Controller
{
    use AppliesMeetingFilters;

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function save(StoreMeetingRequest $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login again.',
            ], 401);
        }

        // Get or create organization - use helper method for consistency
        $organization = $user->organization();
        
        if (!$organization) {
            $organizationName = $user->name . "'s Organization";
            $slug = Str::slug($organizationName . '-' . $user->id);
            $organization = Organization::create([
                'name' => $organizationName,
                'slug' => $slug,
                'description' => 'Personal organization',
                'status' => 'active',
            ]);
            $user->organizations()->attach($organization->id, ['role' => 'admin']);
        }

        // Check meeting limit for new meetings
        if (!$request->id) {
                try {
                    $result = $this->subscriptionService->checkLimit($organization, 'create_meeting');

                    if (!$result['allowed']) {
                        return response()->json([
                            'success' => false,
                            'message' => $result['message'],
                            'data' => ['upgrade_required' => true]
                        ], 403);
                    }
            } catch (\Exception $e) {
                Log::warning('Error checking meeting limit', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue if limit check fails - don't block meeting creation
            }

            // Check attendees limit with proper error handling
            $attendeesCount = count($request->attendees ?? []);
            if ($attendeesCount > 0) {
                try {
                    $subscription = $this->subscriptionService->getCurrentSubscription($organization);
                    
                    // Safely access plan and limits
                    if (!$subscription->relationLoaded('plan')) {
                        $subscription->load('plan');
                    }
                    
                    $plan = $subscription->plan;
                    if (!$plan) {
                        Log::warning('Plan not found for subscription', [
                            'subscription_id' => $subscription->id,
                        ]);
                        // Continue without limit check if plan is missing
                    } else {
                        $limits = $plan->limits ?? [];
                        if (is_string($limits)) {
                            $decoded = json_decode($limits, true);
                            $limits = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                        }
                        if (!is_array($limits)) {
                            $limits = [];
                        }
                        
                        $attendeesLimit = $limits['attendees_per_meeting'] ?? -1;
                        
                        if ($attendeesLimit !== -1 && $attendeesCount > $attendeesLimit) {
                            return response()->json([
                                'success' => false,
                                'message' => "You can only have {$attendeesLimit} attendees per meeting on your current plan. Upgrade to Pro for unlimited attendees.",
                                'data' => ['upgrade_required' => true]
                            ], 403);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error checking attendees limit', [
                        'organization_id' => $organization->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue without limit check if it fails
                }
            }
        }

        DB::beginTransaction();
        try {
            // Create or update meeting
            if ($request->id) {
                $meeting = Meeting::where('id', $request->id)
                    ->where('organization_id', $organization->id)
                    ->firstOrFail();
                
                $meeting->update([
                    'meeting_title' => $request->meeting_title,
                    'status' => $request->status,
                    'date' => $request->date,
                    'time' => $request->time,
                    'duration' => $request->duration,
                    'meeting_type' => $request->meeting_type,
                    'custom_location' => $request->custom_location,
                    'survey_id' => $request->survey_id,
                    'agenda_notes' => $request->agenda_notes,
                ]);
            } else {
                $meeting = Meeting::create([
                    'organization_id' => $organization->id,
                    'meeting_title' => $request->meeting_title,
                    'status' => $request->status,
                    'date' => $request->date,
                    'time' => $request->time,
                    'duration' => $request->duration,
                    'meeting_type' => $request->meeting_type,
                    'custom_location' => $request->custom_location,
                    'survey_id' => $request->survey_id,
                    'agenda_notes' => $request->agenda_notes,
                    'created_by' => $user->id,
                ]);
            }

            // Sync attendees (required field, so always present)
            $meeting->attendees()->sync($request->attendees ?? []);

            // Delete existing notifications if updating
            if ($request->id) {
                $meeting->notifications()->delete();
                // Also delete existing scheduled reminder notifications
                if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
                    DB::table('meeting_fcm_notifications')
                        ->where('meeting_id', $meeting->id)
                        ->where('notification_type', 'reminder')
                        ->where('status', 'pending')
                        ->delete();
                }
            }

            // Create notifications
            if ($request->has('notifications') && is_array($request->notifications)) {
                foreach ($request->notifications as $notificationData) {
                    MeetingNotification::create([
                        'meeting_id' => $meeting->id,
                        'minutes' => $notificationData['minutes'],
                        'unit' => $notificationData['unit'] ?? 'minutes',
                        'trigger' => $notificationData['trigger'] ?? 'before',
                        'is_enabled' => $notificationData['is_enabled'] ?? true,
                    ]);
                }
            }

            DB::commit();

            // Send FCM notifications (wrap in try-catch to not fail the request)
            try {
                if ($request->id) {
                    // Meeting updated - reschedule reminder notifications
                    $this->rescheduleReminderNotifications($meeting->id, $meeting->date, $meeting->time);
                } else {
                    // Meeting created - schedule reminder notifications
                    $this->scheduleReminderNotifications($meeting->id, $meeting->date, $meeting->time);
                    
                    // Increment usage after successful creation
                    try {
                        $this->subscriptionService->incrementUsage($organization, 'meetings');
                    } catch (\Exception $e) {
                        Log::warning('Error incrementing meeting usage', [
                            'organization_id' => $organization->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending meeting notifications', [
                    'meeting_id' => $meeting->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the request if notification fails
            }

            // Reload meeting with all relationships from database
            $meeting = Meeting::with(['attendees', 'notifications', 'survey.surveySteps.surveyFields'])
                ->where('id', $meeting->id)
                ->first();

            if (!$meeting) {
                throw new \Exception('Meeting not found after creation');
            }

            $response = [
                'data' => new MeetingResource($meeting),
                'message' => $request->id ? 'Meeting updated successfully.' : 'Meeting created successfully.',
            ];

            // Include current-month data if meeting falls in the current month
            try {
                $meetingDate = Carbon::parse($meeting->date)->setTimezone(config('app.timezone'));
                $now = Carbon::now(config('app.timezone'));
                if ($meetingDate->year === $now->year && $meetingDate->month === $now->month) {
                    $response['month'] = CalendarService::buildMonth([$organization->id], $now);
                }
            } catch (\Exception $e) {
                Log::warning('Error building month data', ['error' => $e->getMessage()]);
                // Don't fail the request if month data fails
            }

            return response()->json($response, $request->id ? 200 : 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Meeting not found or you do not have permission to update it.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving meeting', [
                'user_id' => $user->id ?? null,
                'organization_id' => $organization->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the meeting. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get paginated list of meetings with search and filter
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'per_page' => 15,
                    'to' => null,
                    'total' => 0,
                ],
                'message' => 'No organization found. Please create a meeting first.',
            ]);
        }

        $organizationId = $organization->id;

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $perPage = (int) $perPage;
        $perPage = min(max(1, $perPage), 100);

        $page = (int) $page;
        $page = max(1, $page);

        // Validate filter parameters
        $request->validate([
            'filters' => 'nullable|array',
            'filters.meeting_type' => 'nullable|string|in:Video Call,In-Person Meeting,Phone Call,Online Meeting',
            'filters.attendees' => 'nullable|string|in:1-on-1,small,medium,large',
            'filters.duration' => 'nullable|string|in:15,30,60,120',
            'filters.status' => 'nullable|string|in:upcoming,completed,cancelled',
        ]);

        $query = Meeting::where('organization_id', $organizationId);

        // Search by title or agenda
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;  
            $query->where(function($q) use ($search) {
                $q->where('meeting_title', 'LIKE', "%{$search}%")
                  ->orWhere('agenda_notes', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status (legacy support - direct status parameter)
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Apply filters from filters array
        $filters = $request->input('filters', []);
        if (!empty($filters)) {
            $query = $this->applyMeetingFilters($query, $filters);
        }

        // Order by date and time
        $query->orderBy('date', 'asc')->orderBy('time', 'asc');

        // Load relationships
        $query->with(['attendees', 'notifications', 'survey.surveySteps.surveyFields']);

        $meetings = $query->paginate($perPage, ['*'], 'page', $page);

        $lastPage = $meetings->lastPage();
        if ($page > $lastPage && $lastPage > 0) {
            // Reload relationships when re-paginating
            $meetings = $query->with(['attendees', 'notifications', 'survey.surveySteps.surveyFields'])
                ->paginate($perPage, ['*'], 'page', $lastPage);
        }

        // Calculate statistics for dashboard
        // Use India timezone (Asia/Kolkata)
        $now = Carbon::now(config('app.timezone'));
        $startOfWeek = $now->copy()->startOfWeek(); // Monday
        $endOfWeek = $now->copy()->endOfWeek(); // Sunday
        $todayDate = $now->toDateString();

        // Total meetings count
        $totalMeetings = Meeting::where('organization_id', $organizationId)->count();

        // Meetings scheduled this week
        $thisWeek = Meeting::where('organization_id', $organizationId)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->count();

        // Meetings scheduled today
        $todayCount = Meeting::where('organization_id', $organizationId)
            ->whereDate('date', $todayDate)
            ->count();

        $response = [
            'data' => MeetingResource::collection($meetings->items()),
            'meta' => [
                'current_page' => $meetings->currentPage(),
                'from' => $meetings->firstItem(),
                'last_page' => $meetings->lastPage(),
                'per_page' => $meetings->perPage(),
                'to' => $meetings->lastItem(),
                'total' => $meetings->total(),
            ],
            'statistics' => [
                'total_meetings' => $totalMeetings,
                'this_week' => $thisWeek,
                'today' => $todayCount,
            ],
            'message' => 'Meetings retrieved successfully.',
        ];

        // Include filters_applied if filters were provided
        if (!empty($filters)) {
            $response['filters_applied'] = $filters;
        }

        return response()->json($response);
    }

    /**
     * Get single meeting by ID
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'id' => 'required|integer|exists:meetings,id',
        ]);

        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create a meeting first.',
            ], 404);
        }

        $organizationId = $organization->id;
        $meetingId = $request->id;

        $meeting = Meeting::where('id', $meetingId)
            ->where('organization_id', $organizationId)
            ->with(['attendees', 'notifications', 'survey.surveySteps.surveyFields'])
            ->firstOrFail();

        return response()->json([
            'data' => new MeetingResource($meeting),
            'message' => 'Meeting retrieved successfully.',
        ]);
    }

    /**
     * Delete a meeting by ID
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'id' => 'required|integer|exists:meetings,id',
        ]);

        $meetingId = $request->id;

        $meeting = Meeting::find($meetingId);
        
        if (!$meeting) {
            return response()->json([
                'message' => 'Meeting not found.',
            ], 404);
        }

        $organization = $user->organizations()->first();
        
        $hasPermission = false;
        
        if ($organization) {
            if ($meeting->organization_id == $organization->id) {
                $hasPermission = true;
            }
        }
        
        if ($meeting->created_by == $user->id) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'You do not have permission to delete this meeting.',
            ], 403);
        }

        $meeting->delete();

        return response()->json([
            'message' => 'Meeting deleted successfully.',
        ], 200);
    }

    /**
     * Send notification to meeting creator
     * 
     * Note: Currently sends FCM push notifications only.
     * If email notifications are added in the future, check user's notification preferences:
     * - For 'meeting_updated' type: Check email_meeting_updates (deprecated, always disabled)
     * - For 'meeting_cancelled' type: Check email_meeting_cancellations (deprecated, always disabled)
     * - For 'reminder' type: Check email_meeting_reminders (active)
     */
    private function sendMeetingNotification($meetingId, $notificationType, $title, $body, $data = [])
    {
        try {
            $meeting = Meeting::with('attendees')->find($meetingId);
            if (!$meeting || !$meeting->created_by) {
                return;
            }

            // Format meeting date and time for FCM payload
            $meetingDate = $this->formatMeetingDate($meeting->date);
            $meetingTime = $this->formatMeetingTime($meeting->time);

            // Add meeting_date and meeting_time to data payload
            $data['meeting_date'] = $meetingDate;
            $data['meeting_time'] = $meetingTime;

            // Get all user IDs to notify
            $userIdsToNotify = [$meeting->created_by]; // Always notify creator

            // Also notify attendees who are users (matched by email)
            if ($meeting->attendees && $meeting->attendees->isNotEmpty()) {
                $attendeeEmails = $meeting->attendees->pluck('email')->filter()->toArray();
                
                if (!empty($attendeeEmails)) {
                    $attendeeUserIds = DB::table('users')
                        ->whereIn('email', $attendeeEmails)
                        ->pluck('id')
                        ->toArray();
                    
                    $userIdsToNotify = array_unique(array_merge($userIdsToNotify, $attendeeUserIds));
                }
            }

            // Send notification to all users
            foreach ($userIdsToNotify as $userId) {
                // Create notification record
                if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
                    DB::table('meeting_fcm_notifications')->insert([
                        'meeting_id' => $meetingId,
                        'user_id' => $userId,
                        'notification_type' => $notificationType,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Queue notification job
                SendMeetingNotificationJob::dispatch($userId, $meetingId, $notificationType, $title, $body, $data);
            }

            \Log::info('Meeting notifications queued', [
                'meeting_id' => $meetingId,
                'notification_type' => $notificationType,
                'users_count' => count($userIdsToNotify),
                'user_ids' => $userIdsToNotify,
                'meeting_date' => $meetingDate,
                'meeting_time' => $meetingTime,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send meeting notification', [
                'meeting_id' => $meetingId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format meeting date to YYYY-MM-DD format for FCM payload
     * 
     * @param mixed $date Date value (Carbon, DateTime, or string)
     * @return string Formatted date (YYYY-MM-DD)
     */
    private function formatMeetingDate($date): string
    {
        if ($date instanceof Carbon || $date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }
        
        if (is_string($date)) {
            // Try to parse and format
            try {
                $parsed = Carbon::parse($date);
                return $parsed->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Failed to parse meeting date', [
                    'date' => $date,
                    'error' => $e->getMessage()
                ]);
                // Return as-is if already in correct format, or return empty string
                return $date;
            }
        }
        
        Log::warning('Invalid date format for meeting', ['date' => $date]);
        return '';
    }

    /**
     * Format meeting time to HH:MM:SS or HH:MM format for FCM payload
     * 
     * @param mixed $time Time value (Carbon, DateTime, or string)
     * @return string Formatted time (HH:MM:SS or HH:MM)
     */
    private function formatMeetingTime($time): string
    {
        if ($time instanceof Carbon || $time instanceof \DateTime) {
            return $time->format('H:i:s');
        }
        
        if (is_string($time)) {
            // If already in HH:MM:SS format, return as-is
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                return $time;
            }
            
            // If in HH:MM format, add seconds
            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                return $time . ':00';
            }
            
            // Try to parse and format
            try {
                $parsed = Carbon::parse($time);
                return $parsed->format('H:i:s');
            } catch (\Exception $e) {
                Log::warning('Failed to parse meeting time', [
                    'time' => $time,
                    'error' => $e->getMessage()
                ]);
                // Return as-is if it might be valid
                return $time;
            }
        }
        
        Log::warning('Invalid time format for meeting', ['time' => $time]);
        return '';
    }

    /**
     * Schedule reminder notifications based on user notification preferences
     * 
     * This method schedules default reminders for each attendee based on their
     * individual notification preferences (meeting_reminders array).
     * 
     * @param int $meetingId Meeting ID
     * @param string|Carbon $meetingDate Meeting date (Y-m-d format or Carbon instance)
     * @param string $meetingTime Meeting time (H:i or H:i:s format)
     */
    private function scheduleReminderNotifications($meetingId, $meetingDate, $meetingTime)
    {
        if (!DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
            Log::warning('meeting_fcm_notifications table does not exist', ['meeting_id' => $meetingId]);
            return;
        }

        try {
            $meeting = Meeting::with('attendees')->find($meetingId);
            if (!$meeting || !$meeting->created_by) {
                Log::warning('Meeting not found or missing creator', ['meeting_id' => $meetingId]);
                return;
            }

            // Parse meeting date and time - explicitly use India timezone (Asia/Kolkata)
            $dateOnly = is_string($meetingDate) ? $meetingDate : $meetingDate->format('Y-m-d');
            $meetingDateTime = Carbon::parse("{$dateOnly} {$meetingTime}", config('app.timezone'));
            $meetingDateTime->setTimezone(config('app.timezone'));

            // Get all user IDs to notify (creator + attendees matched by email)
            $userIdsToNotify = [$meeting->created_by];

            // Also get attendee user IDs (matched by email)
            if ($meeting->attendees && $meeting->attendees->isNotEmpty()) {
                $attendeeEmails = $meeting->attendees->pluck('email')->filter()->toArray();
                
                if (!empty($attendeeEmails)) {
                    $attendeeUserIds = DB::table('users')
                        ->whereIn('email', $attendeeEmails)
                        ->pluck('id')
                        ->toArray();
                    
                    $userIdsToNotify = array_unique(array_merge($userIdsToNotify, $attendeeUserIds));
                }
            }

            if (empty($userIdsToNotify)) {
                Log::warning('No users to notify for meeting', ['meeting_id' => $meetingId]);
                return;
            }

            // Check for meeting-specific notification settings first
            $meetingNotificationSettings = MeetingNotification::where('meeting_id', $meetingId)
                ->where('is_enabled', true)
                ->where('trigger', 'before')
                ->get();

            // If meeting has specific notification settings, use those instead of user preferences
            if ($meetingNotificationSettings->isNotEmpty()) {
                Log::info('Using meeting-specific notification settings', [
                    'meeting_id' => $meetingId,
                    'settings_count' => $meetingNotificationSettings->count()
                ]);

                // Validate and deduplicate reminder times
                $meetingNotificationSettings = $this->validateReminderTimes($meetingNotificationSettings, $meetingDateTime);
                $meetingNotificationSettings = $this->deduplicateReminderSettings($meetingNotificationSettings, $meetingDateTime);

                // Limit maximum reminders per meeting (max 10)
                if ($meetingNotificationSettings->count() > 10) {
                    Log::warning('Too many reminders configured, limiting to 10', [
                        'meeting_id' => $meetingId,
                        'total_reminders' => $meetingNotificationSettings->count(),
                    ]);
                    $meetingNotificationSettings = $meetingNotificationSettings->take(10);
                }

                // Schedule reminders for all users based on meeting settings
                foreach ($meetingNotificationSettings as $setting) {
                    $reminderTime = $meetingDateTime->copy();

                    // Calculate offset based on unit
                    if ($setting->unit === 'days') {
                        $reminderTime->subDays($setting->minutes);
                    } elseif ($setting->unit === 'hours') {
                        $reminderTime->subHours($setting->minutes);
                    } else {
                        $reminderTime->subMinutes($setting->minutes);
                    }

                    // Only schedule if reminder time is in the future
                    if ($reminderTime->isFuture()) {
                        foreach ($userIdsToNotify as $userId) {
                            // Check if user has notifications enabled
                            if (!$this->isPushNotificationsEnabled($userId)) {
                                Log::info('User has push notifications disabled, skipping reminder', [
                                    'user_id' => $userId,
                                    'meeting_id' => $meetingId
                                ]);
                                continue;
                            }

                            // Check if notification already exists (avoid duplicates)
                            $existing = DB::table('meeting_fcm_notifications')
                                ->where('meeting_id', $meetingId)
                                ->where('user_id', $userId)
                                ->where('scheduled_at', $reminderTime)
                                ->where('notification_type', 'reminder')
                                ->first();

                            if ($existing) {
                                Log::info('Reminder already scheduled, skipping', [
                                    'user_id' => $userId,
                                    'meeting_id' => $meetingId,
                                    'scheduled_at' => $reminderTime->format('Y-m-d H:i:s')
                                ]);
                                continue;
                            }

                            DB::table('meeting_fcm_notifications')->insert([
                                'meeting_id' => $meetingId,
                                'user_id' => $userId,
                                'notification_type' => 'reminder',
                                'scheduled_at' => $reminderTime,
                                'status' => 'pending',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        Log::info('Reminder notification scheduled (meeting-specific)', [
                            'meeting_id' => $meetingId,
                            'minutes' => $setting->minutes,
                            'unit' => $setting->unit,
                            'scheduled_at' => $reminderTime->format('Y-m-d H:i:s'),
                            'meeting_time' => $meetingDateTime->format('Y-m-d H:i:s'),
                            'user_ids' => $userIdsToNotify
                        ]);
                    } else {
                        Log::warning('Reminder time is in the past, skipping', [
                            'meeting_id' => $meetingId,
                            'minutes' => $setting->minutes,
                            'unit' => $setting->unit,
                            'reminder_time' => $reminderTime->format('Y-m-d H:i:s'),
                            'meeting_time' => $meetingDateTime->format('Y-m-d H:i:s')
                        ]);
                    }
                }
            } else {
                // No meeting-specific settings - use each user's default reminder preferences
                Log::info('No meeting-specific settings, using user preferences', ['meeting_id' => $meetingId]);
                $this->scheduleDefaultReminders($meetingId, $meetingDateTime, $userIdsToNotify);
            }

        } catch (\Exception $e) {
            Log::error('Failed to schedule reminder notifications', [
                'meeting_id' => $meetingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Schedule default reminder notifications based on each user's notification preferences
     * 
     * @param int $meetingId Meeting ID
     * @param Carbon $meetingDateTime Meeting date and time
     * @param array $userIds Array of user IDs to schedule reminders for
     */
    private function scheduleDefaultReminders($meetingId, $meetingDateTime, array $userIds)
    {
        foreach ($userIds as $userId) {
            try {
                // Check if user has push notifications enabled
                if (!$this->isPushNotificationsEnabled($userId)) {
                    Log::info('User has push notifications disabled, skipping reminders', [
                        'user_id' => $userId,
                        'meeting_id' => $meetingId
                    ]);
                    continue;
                }

                // Get user's reminder preferences
                $reminderMinutes = $this->getAttendeeReminderPreferences($userId);

                if (empty($reminderMinutes)) {
                    Log::info('User has no reminder preferences configured, using default', [
                        'user_id' => $userId,
                        'meeting_id' => $meetingId
                    ]);
                    $reminderMinutes = [15]; // Default to 15 minutes
                }

                // Schedule a reminder for each configured time
                foreach ($reminderMinutes as $minutes) {
                    // Validate minutes
                    if (!is_numeric($minutes) || $minutes <= 0) {
                        Log::warning('Invalid reminder minutes', [
                            'user_id' => $userId,
                            'minutes' => $minutes,
                            'meeting_id' => $meetingId
                        ]);
                        continue;
                    }

                    // Calculate scheduled time
                    $scheduledAt = $meetingDateTime->copy()->subMinutes($minutes);

                    // Skip if scheduled time is in the past
                    if ($scheduledAt->isPast()) {
                        Log::warning('Reminder scheduled in past, skipping', [
                            'user_id' => $userId,
                            'meeting_id' => $meetingId,
                            'minutes' => $minutes,
                            'scheduled_at' => $scheduledAt->toDateTimeString(),
                            'meeting_time' => $meetingDateTime->toDateTimeString()
                        ]);
                        continue;
                    }

                    // Check if notification already exists (to avoid duplicates)
                    $existing = DB::table('meeting_fcm_notifications')
                        ->where('meeting_id', $meetingId)
                        ->where('user_id', $userId)
                        ->where('scheduled_at', $scheduledAt)
                        ->where('notification_type', 'reminder')
                        ->first();

                    if ($existing) {
                        Log::info('Reminder already scheduled, skipping', [
                            'user_id' => $userId,
                            'meeting_id' => $meetingId,
                            'minutes' => $minutes,
                            'scheduled_at' => $scheduledAt->toDateTimeString()
                        ]);
                        continue;
                    }

                    // Create notification record
                    DB::table('meeting_fcm_notifications')->insert([
                        'meeting_id' => $meetingId,
                        'user_id' => $userId,
                        'notification_type' => 'reminder',
                        'scheduled_at' => $scheduledAt,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('Scheduled default reminder', [
                        'user_id' => $userId,
                        'meeting_id' => $meetingId,
                        'minutes' => $minutes,
                        'scheduled_at' => $scheduledAt->toDateTimeString(),
                        'meeting_time' => $meetingDateTime->toDateTimeString()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to schedule reminders for user', [
                    'user_id' => $userId,
                    'meeting_id' => $meetingId,
                    'error' => $e->getMessage()
                ]);
                // Continue with next user even if one fails
            }
        }
    }

    /**
     * Get attendee reminder preferences from notification settings
     * 
     * @param int $userId User ID
     * @return array Array of reminder minutes (e.g., [15, 30, 60])
     */
    private function getAttendeeReminderPreferences($userId)
    {
        try {
            $preferences = NotificationPreference::where('user_id', $userId)->first();

            if (!$preferences) {
                // No preferences found, return default
                Log::info('No notification preferences found for user, using default', ['user_id' => $userId]);
                return [15]; // Default to 15 minutes
            }

            // Get meeting_reminders array
            $reminders = $preferences->meeting_reminders;

            // Ensure it's an array
            if (!is_array($reminders)) {
                Log::warning('meeting_reminders is not an array, using default', [
                    'user_id' => $userId,
                    'reminders' => $reminders
                ]);
                return [15];
            }

            // Filter out invalid values and ensure positive integers
            $reminders = array_filter($reminders, function($value) {
                return is_numeric($value) && $value > 0;
            });

            // If empty after filtering, return default
            if (empty($reminders)) {
                Log::info('No valid reminder preferences, using default', ['user_id' => $userId]);
                return [15];
            }

            // Sort and return unique values
            $reminders = array_unique(array_map('intval', $reminders));
            sort($reminders);

            return array_values($reminders);
        } catch (\Exception $e) {
            Log::error('Failed to get attendee reminder preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            // Return default on error
            return [15];
        }
    }

    /**
     * Check if push notifications are enabled for a user
     * 
     * @param int $userId User ID
     * @return bool True if push notifications are enabled
     */
    private function isPushNotificationsEnabled($userId)
    {
        try {
            $preferences = NotificationPreference::where('user_id', $userId)->first();

            if (!$preferences) {
                // No preferences found, default to enabled
                return true;
            }

            return $preferences->push_notifications_enabled ?? true;
        } catch (\Exception $e) {
            Log::error('Failed to check push notifications enabled', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            // Default to enabled on error
            return true;
        }
    }

    /**
     * Check if email reminders are enabled for a user
     * 
     * @param int $userId User ID
     * @return bool True if email reminders are enabled
     */
    private function isEmailRemindersEnabled($userId)
    {
        try {
            $preferences = NotificationPreference::where('user_id', $userId)->first();

            if (!$preferences) {
                // No preferences found, default to enabled
                return true;
            }

            $emailNotificationsEnabled = $preferences->email_notifications_enabled ?? true;
            $emailMeetingReminders = $preferences->email_meeting_reminders ?? true;

            return $emailNotificationsEnabled && $emailMeetingReminders;
        } catch (\Exception $e) {
            Log::error('Failed to check email reminders enabled', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            // Default to enabled on error
            return true;
        }
    }

    /**
     * Reschedule reminder notifications (delete old, create new)
     * 
     * @param int $meetingId Meeting ID
     * @param string|Carbon $meetingDate Meeting date
     * @param string $meetingTime Meeting time
     */
    private function rescheduleReminderNotifications($meetingId, $meetingDate, $meetingTime)
    {
        // Delete existing pending reminders for this meeting
        if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
            DB::table('meeting_fcm_notifications')
                ->where('meeting_id', $meetingId)
                ->where('notification_type', 'reminder')
                ->where('status', 'pending')
                ->delete();
        }

        // Schedule new reminders
        $this->scheduleReminderNotifications($meetingId, $meetingDate, $meetingTime);
    }

    /**
     * Deduplicate reminder settings based on calculated reminder time
     * 
     * @param \Illuminate\Support\Collection $settings Collection of notification settings
     * @param Carbon $meetingDateTime Meeting date and time
     * @return \Illuminate\Support\Collection Deduplicated settings
     */
    private function deduplicateReminderSettings($settings, $meetingDateTime)
    {
        $seenTimes = [];
        $deduplicated = collect([]);

        foreach ($settings as $setting) {
            $reminderTime = $meetingDateTime->copy();

            // Calculate reminder time
            if ($setting->unit === 'days') {
                $reminderTime->subDays($setting->minutes);
            } elseif ($setting->unit === 'hours') {
                $reminderTime->subHours($setting->minutes);
            } else {
                $reminderTime->subMinutes($setting->minutes);
            }

            // Create a unique key based on the calculated reminder time
            $timeKey = $reminderTime->format('Y-m-d H:i:s');

            // Only add if we haven't seen this reminder time before
            if (!in_array($timeKey, $seenTimes)) {
                $seenTimes[] = $timeKey;
                $deduplicated->push($setting);
            } else {
                \Log::info('Duplicate reminder timing detected and removed', [
                    'minutes' => $setting->minutes,
                    'unit' => $setting->unit,
                    'calculated_time' => $timeKey,
                ]);
            }
        }

        return $deduplicated;
    }

    /**
     * Validate reminder times to ensure they are before meeting time
     * 
     * @param \Illuminate\Support\Collection $settings Collection of notification settings
     * @param Carbon $meetingDateTime Meeting date and time
     * @return \Illuminate\Support\Collection Valid settings (removed those after meeting time)
     */
    private function validateReminderTimes($settings, $meetingDateTime)
    {
        return $settings->filter(function ($setting) use ($meetingDateTime) {
            $reminderTime = $meetingDateTime->copy();

            // Calculate reminder time
            if ($setting->unit === 'days') {
                $reminderTime->subDays($setting->minutes);
            } elseif ($setting->unit === 'hours') {
                $reminderTime->subHours($setting->minutes);
            } else {
                $reminderTime->subMinutes($setting->minutes);
            }

            // Only allow reminders before meeting time
            if ($reminderTime->gte($meetingDateTime)) {
                \Log::warning('Reminder time is after meeting time, skipping', [
                    'minutes' => $setting->minutes,
                    'unit' => $setting->unit,
                    'reminder_time' => $reminderTime->format('Y-m-d H:i:s'),
                    'meeting_time' => $meetingDateTime->format('Y-m-d H:i:s'),
                ]);
                return false;
            }

            return true;
        });
    }

}
