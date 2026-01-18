<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\FcmService;
use App\Jobs\SendMeetingNotificationJob;
use App\Models\Meeting;
use Carbon\Carbon;

class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders';
    protected $description = 'Send reminder notifications for upcoming meetings';

    public function handle(FcmService $fcmService)
    {
        // Explicitly set timezone to avoid timezone mismatch issues
        $now = Carbon::now(config('app.timezone'));

        // Get meetings starting in the next 5 minutes (starting soon)
        // Use get() and filter in PHP for better database compatibility
        $fiveMinutesLater = $now->copy()->addMinutes(5);
        $today = $now->format('Y-m-d');
        $startingSoon = Meeting::whereDate('date', $today)
            ->where('status', '!=', 'Cancelled')
            ->get()
            ->filter(function ($meeting) use ($now, $fiveMinutesLater) {
                if (!$meeting->time || !$meeting->date) {
                    return false;
                }
                // Parse meeting datetime - handle date as Carbon instance or string
                $dateString = is_string($meeting->date) ? $meeting->date : $meeting->date->format('Y-m-d');
                $meetingDateTime = Carbon::parse("{$dateString} {$meeting->time}");
                // Check if meeting starts between now and 5 minutes from now
                return $meetingDateTime->isBetween($now, $fiveMinutesLater, true);
            });

        foreach ($startingSoon as $meeting) {
            $this->sendStartingSoonNotification($meeting);
        }

        // Get meetings with reminder notifications scheduled
        $sentCount = 0;
        $skippedCount = 0;
        
        if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
            // Add 1 minute buffer to catch notifications that are due
            // This handles cases where scheduler runs slightly before exact time
            // Get pending reminders that are due (with 1 minute buffer)
            // Use DISTINCT or group by to prevent duplicate reminders if any exist
            $reminders = DB::table('meeting_fcm_notifications')
                ->join('meetings', 'meeting_fcm_notifications.meeting_id', '=', 'meetings.id')
                ->where('notification_type', 'reminder')
                ->where('meeting_fcm_notifications.status', 'pending')
                ->where('scheduled_at', '<=', $now->copy()->addMinute())
                ->where('meetings.status', '!=', 'Cancelled')
                ->select('meeting_fcm_notifications.*', 'meetings.meeting_title', 'meetings.date', 'meetings.time')
                ->groupBy('meeting_fcm_notifications.id')  // Ensure no duplicate processing
                ->get();

            // Log found reminders for debugging
            if ($reminders->count() > 0) {
                // Group reminders by meeting_id for better logging
                $remindersByMeeting = $reminders->groupBy('meeting_id');
                
                \Log::info('Reminder scheduler found pending reminders', [
                    'total_count' => $reminders->count(),
                    'unique_meetings' => $remindersByMeeting->count(),
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'timezone' => config('app.timezone'),
                    'reminders_by_meeting' => $remindersByMeeting->map(function($meetingReminders, $meetingId) {
                        return [
                            'meeting_id' => $meetingId,
                            'reminder_count' => $meetingReminders->count(),
                            'scheduled_times' => $meetingReminders->pluck('scheduled_at')->toArray(),
                        ];
                    })->toArray(),
                    'all_reminders' => $reminders->map(function($r) {
                        return [
                            'id' => $r->id,
                            'meeting_id' => $r->meeting_id,
                            'user_id' => $r->user_id,
                            'scheduled_at' => $r->scheduled_at,
                        ];
                    })->toArray()
                ]);
            }

            foreach ($reminders as $reminder) {
                // Double check: Only send if scheduled_at has actually passed
                // This prevents sending notifications too early
                $scheduledAt = Carbon::parse($reminder->scheduled_at, config('app.timezone'));
                
                if ($scheduledAt->lte($now)) {
                    // Double-check status before sending (prevents race conditions)
                    // Reload reminder from database to get latest status
                    $currentStatus = DB::table('meeting_fcm_notifications')
                        ->where('id', $reminder->id)
                        ->value('status');
                    
                    if ($currentStatus !== 'pending') {
                        \Log::warning('Reminder already processed, skipping duplicate', [
                            'reminder_id' => $reminder->id,
                            'meeting_id' => $reminder->meeting_id,
                            'scheduled_at' => $reminder->scheduled_at,
                            'current_status' => $currentStatus,
                        ]);
                        $skippedCount++;
                        continue;
                    }
                    
                    \Log::info('Sending reminder notification', [
                        'reminder_id' => $reminder->id,
                        'meeting_id' => $reminder->meeting_id,
                        'user_id' => $reminder->user_id,
                        'scheduled_at' => $reminder->scheduled_at,
                        'current_time' => $now->format('Y-m-d H:i:s'),
                        'difference_minutes' => $now->diffInMinutes($scheduledAt, false)
                    ]);
                    
                    $this->sendReminderNotification($reminder);
                    $sentCount++;
                } else {
                    \Log::warning('Reminder not yet due, skipping', [
                        'reminder_id' => $reminder->id,
                        'meeting_id' => $reminder->meeting_id,
                        'scheduled_at' => $reminder->scheduled_at,
                        'current_time' => $now->format('Y-m-d H:i:s'),
                        'difference_minutes' => $now->diffInMinutes($scheduledAt, false)
                    ]);
                    $skippedCount++;
                }
            }
            
            // Log summary for multiple reminders scenario
            if ($reminders->count() > 1) {
                \Log::info('Multiple reminders processed in this run', [
                    'total_found' => $reminders->count(),
                    'sent' => $sentCount,
                    'skipped' => $skippedCount,
                    'unique_meetings' => $reminders->pluck('meeting_id')->unique()->count(),
                ]);
            }
        }

        // Log summary
        if ($sentCount > 0 || $skippedCount > 0) {
            $this->info("Reminder notifications processed: {$sentCount} sent, {$skippedCount} skipped");
        } else {
            $this->info('Reminder notifications processed');
        }
    }

    private function sendStartingSoonNotification($meeting)
    {
        if (!$meeting->created_by) {
            return;
        }

        // Check if notification already sent
        if (DB::getSchemaBuilder()->hasTable('meeting_fcm_notifications')) {
            $existing = DB::table('meeting_fcm_notifications')
                ->where('meeting_id', $meeting->id)
                ->where('user_id', $meeting->created_by)
                ->where('notification_type', 'starting')
                ->where('meeting_fcm_notifications.status', 'sent')
                ->exists();

            if ($existing) {
                return; // Already sent
            }

            // Create notification record
            DB::table('meeting_fcm_notifications')->insert([
                'meeting_id' => $meeting->id,
                'user_id' => $meeting->created_by,
                'notification_type' => 'starting',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Format meeting date and time for FCM payload
        $meetingDate = $this->formatMeetingDate($meeting->date);
        $meetingTime = $this->formatMeetingTime($meeting->time);

        SendMeetingNotificationJob::dispatch(
            $meeting->created_by,
            $meeting->id,
            'starting',
            'Meeting Starting Soon',
            "Meeting '{$meeting->meeting_title}' starts in 5 minutes",
            [
                'type' => 'meeting_starting',
                'meeting_id' => $meeting->id,
                'action' => 'join_meeting',
                'meeting_date' => $meetingDate,
                'meeting_time' => $meetingTime,
            ]
        );
    }

    private function sendReminderNotification($reminder)
    {
        // Update status to 'sent' IMMEDIATELY to prevent duplicate sends
        // This prevents race conditions when scheduler runs multiple times
        $updated = DB::table('meeting_fcm_notifications')
            ->where('id', $reminder->id)
            ->where('status', 'pending')  // Only update if still pending (prevents race condition)
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'updated_at' => now(),
            ]);

        // Only dispatch job if we successfully updated the status (prevent duplicates)
        if ($updated > 0) {
            // Format meeting date and time for FCM payload
            $meetingDate = $this->formatMeetingDate($reminder->date);
            $meetingTime = $this->formatMeetingTime($reminder->time);

            SendMeetingNotificationJob::dispatch(
                $reminder->user_id,
                $reminder->meeting_id,
                'reminder',
                'Meeting Reminder',
                "Reminder: Meeting '{$reminder->meeting_title}' is scheduled",
                [
                    'type' => 'meeting_reminder',
                    'meeting_id' => $reminder->meeting_id,
                    'action' => 'view_meeting',
                    'reminder_id' => $reminder->id,  // Pass reminder_id for job tracking
                    'meeting_date' => $meetingDate,
                    'meeting_time' => $meetingTime,
                ]
            );

            \Log::info('Reminder notification job dispatched', [
                'reminder_id' => $reminder->id,
                'meeting_id' => $reminder->meeting_id,
                'user_id' => $reminder->user_id,
                'meeting_date' => $meetingDate,
                'meeting_time' => $meetingTime,
            ]);
        } else {
            // Another scheduler instance already processed this reminder
            \Log::warning('Reminder already processed by another scheduler instance', [
                'reminder_id' => $reminder->id,
                'meeting_id' => $reminder->meeting_id,
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
                \Log::warning('Failed to parse meeting date', [
                    'date' => $date,
                    'error' => $e->getMessage()
                ]);
                // Return as-is if already in correct format, or return empty string
                return $date;
            }
        }
        
        \Log::warning('Invalid date format for meeting', ['date' => $date]);
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
                \Log::warning('Failed to parse meeting time', [
                    'time' => $time,
                    'error' => $e->getMessage()
                ]);
                // Return as-is if it might be valid
                return $time;
            }
        }
        
        \Log::warning('Invalid time format for meeting', ['time' => $time]);
        return '';
    }
}
