<?php

namespace Tests\Feature;

use App\Console\Commands\SendMeetingReminders;
use App\Jobs\SendMeetingNotificationJob;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Organization;
use App\Models\MeetingNotification;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    
    // Create organization
    $this->organization = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-organization-' . uniqid(),
        'description' => 'Test Organization Description',
        'status' => 'active',
    ]);
    
    $this->user->organizations()->attach($this->organization->id, ['role' => 'admin']);
    
    // Create a test contact for attendees
    $this->contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'Test',
        'last_name' => 'Contact',
        'email' => 'testcontact@example.com',
        'phone' => '+1234567890',
        'created_by' => $this->user->id,
    ]);
});

test('command processes starting soon notifications for meetings starting in 5 minutes', function () {
    Queue::fake();
    
    $now = Carbon::now();
    // Set meeting time to 2 minutes from now to ensure it's within the 5-minute window
    $meetingTime = $now->copy()->addMinutes(2)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Attach attendee so notification can be sent
    $meeting->attendees()->attach($this->contact->id);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        $userId = $reflection->getProperty('userId');
        $userId->setAccessible(true);
        
        return $meetingId->getValue($job) === $meeting->id 
            && $notificationType->getValue($job) === 'starting'
            && $userId->getValue($job) === $this->user->id;
    });
    
    // Verify notification record was created
    expect(DB::table('meeting_fcm_notifications')
        ->where('meeting_id', $meeting->id)
        ->where('notification_type', 'starting')
        ->where('status', 'pending')
        ->exists())->toBeTrue();
});

test('command does not send starting soon notification if already sent', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addMinutes(3)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Mark notification as already sent
    DB::table('meeting_fcm_notifications')->insert([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'notification_type' => 'starting',
        'status' => 'sent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    // Should not dispatch another job
    Queue::assertNotPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        
        return $notificationType->getValue($job) === 'starting' 
            && $meetingId->getValue($job) === $meeting->id;
    });
});

test('command does not send starting soon notification for cancelled meetings', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addMinutes(3)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Cancelled Meeting',
        'status' => 'Cancelled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertNothingPushed();
});

test('command sends starting soon notification to creator even without attendees', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addMinutes(3)->format('H:i:s');
    
    // Create meeting without attendees
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Ensure no attendees are attached
    $meeting->attendees()->detach();
    
    Artisan::call('meetings:send-reminders');
    
    // Should still send notification to creator (created_by user)
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        $userId = $reflection->getProperty('userId');
        $userId->setAccessible(true);
        
        return $notificationType->getValue($job) === 'starting' 
            && $meetingId->getValue($job) === $meeting->id
            && $userId->getValue($job) === $this->user->id;
    });
});

test('command processes scheduled reminder notifications', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $scheduledAt = $now->copy()->subMinutes(1); // 1 minute ago
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Create scheduled reminder notification
    $reminderId = DB::table('meeting_fcm_notifications')->insertGetId([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'notification_type' => 'reminder',
        'status' => 'pending',
        'scheduled_at' => $scheduledAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        $userId = $reflection->getProperty('userId');
        $userId->setAccessible(true);
        
        return $meetingId->getValue($job) === $meeting->id 
            && $notificationType->getValue($job) === 'reminder'
            && $userId->getValue($job) === $this->user->id;
    });
    
    // Verify notification status was updated to sent
    $notification = DB::table('meeting_fcm_notifications')->find($reminderId);
    expect($notification->status)->toBe('sent');
});

test('command does not process reminder notifications that are not yet due', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $scheduledAt = $now->copy()->addMinutes(10); // 10 minutes in future
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Create scheduled reminder notification (not yet due)
    DB::table('meeting_fcm_notifications')->insert([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'notification_type' => 'reminder',
        'status' => 'pending',
        'scheduled_at' => $scheduledAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    // Should not dispatch job for future notifications
    Queue::assertNotPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        
        return $notificationType->getValue($job) === 'reminder' 
            && $meetingId->getValue($job) === $meeting->id;
    });
});

test('command does not process reminder notifications for cancelled meetings', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $scheduledAt = $now->copy()->subMinutes(1);
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Cancelled Meeting',
        'status' => 'Cancelled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Create scheduled reminder notification
    DB::table('meeting_fcm_notifications')->insert([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'notification_type' => 'reminder',
        'status' => 'pending',
        'scheduled_at' => $scheduledAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertNothingPushed();
});

test('command does not process reminder notifications that are already sent', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $scheduledAt = $now->copy()->subMinutes(1);
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Create reminder notification already marked as sent
    DB::table('meeting_fcm_notifications')->insert([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'notification_type' => 'reminder',
        'status' => 'sent',
        'scheduled_at' => $scheduledAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertNothingPushed();
});

test('command handles multiple reminder notifications correctly', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $scheduledAt = $now->copy()->subMinutes(1);
    
    $meeting1 = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Meeting 1',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    $meeting2 = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Meeting 2',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Create scheduled reminder notifications for both meetings
    DB::table('meeting_fcm_notifications')->insert([
        [
            'meeting_id' => $meeting1->id,
            'user_id' => $this->user->id,
            'notification_type' => 'reminder',
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'meeting_id' => $meeting2->id,
            'user_id' => $this->user->id,
            'notification_type' => 'reminder',
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    // Should dispatch 2 jobs
    Queue::assertPushed(SendMeetingNotificationJob::class, 2);
    
    // Verify both notifications are marked as sent
    expect(DB::table('meeting_fcm_notifications')
        ->whereIn('meeting_id', [$meeting1->id, $meeting2->id])
        ->where('status', 'sent')
        ->count())->toBe(2);
});

test('command handles meetings starting exactly at current time', function () {
    Queue::fake();
    
    $now = Carbon::now();
    // Set meeting time to 1 minute from now (within 5 minute window, avoids timing precision issues)
    $meetingTime = $now->copy()->addMinutes(1)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Attach attendee so notification can be sent
    $meeting->attendees()->attach($this->contact->id);
    
    Artisan::call('meetings:send-reminders');
    
    // Should send starting soon notification
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        
        return $notificationType->getValue($job) === 'starting' 
            && $meetingId->getValue($job) === $meeting->id;
    });
});

test('command handles meetings starting in exactly 5 minutes', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addMinutes(5)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    // Attach attendee so notification can be sent
    $meeting->attendees()->attach($this->contact->id);
    
    Artisan::call('meetings:send-reminders');
    
    // Should send starting soon notification
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $meetingId = $reflection->getProperty('meetingId');
        $meetingId->setAccessible(true);
        $notificationType = $reflection->getProperty('notificationType');
        $notificationType->setAccessible(true);
        
        return $notificationType->getValue($job) === 'starting' 
            && $meetingId->getValue($job) === $meeting->id;
    });
});

test('command does not send starting soon notification for meetings starting after 5 minutes', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addMinutes(6)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertNothingPushed();
});

test('command handles reminder notification with correct payload', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $scheduledAt = $now->copy()->subMinutes(1);
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Important Meeting',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $now->format('H:i:s'),
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    DB::table('meeting_fcm_notifications')->insert([
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'notification_type' => 'reminder',
        'status' => 'pending',
        'scheduled_at' => $scheduledAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $title = $reflection->getProperty('title');
        $title->setAccessible(true);
        $body = $reflection->getProperty('body');
        $body->setAccessible(true);
        $data = $reflection->getProperty('data');
        $data->setAccessible(true);
        
        $jobData = $data->getValue($job);
        return $title->getValue($job) === 'Meeting Reminder'
            && $body->getValue($job) === "Reminder: Meeting 'Important Meeting' is scheduled"
            && $jobData['type'] === 'meeting_reminder'
            && $jobData['meeting_id'] === $meeting->id
            && $jobData['action'] === 'view_meeting';
    });
});

test('command handles starting soon notification with correct payload', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addMinutes(3)->format('H:i:s');
    
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => $now->format('Y-m-d'),
        'time' => $meetingTime,
        'duration' => 30,
        'created_by' => $this->user->id,
    ]);
    
    Artisan::call('meetings:send-reminders');
    
    Queue::assertPushed(SendMeetingNotificationJob::class, function ($job) use ($meeting) {
        $reflection = new \ReflectionClass($job);
        $title = $reflection->getProperty('title');
        $title->setAccessible(true);
        $body = $reflection->getProperty('body');
        $body->setAccessible(true);
        $data = $reflection->getProperty('data');
        $data->setAccessible(true);
        
        $jobData = $data->getValue($job);
        return $title->getValue($job) === 'Meeting Starting Soon'
            && $body->getValue($job) === "Meeting 'Team Standup' starts in 5 minutes"
            && $jobData['type'] === 'meeting_starting'
            && $jobData['meeting_id'] === $meeting->id
            && $jobData['action'] === 'join_meeting';
    });
});

test('meeting creation with simple notification format [10, 15] returns correct response format', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addHours(2)->format('H:i');
    $meetingDate = $now->format('Y-m-d');
    
    // Create token for authentication
    $token = $this->user->createToken('test-token')->plainTextToken;
    
    // Create meeting via API with simple format
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/meeting/save', [
            'meeting_title' => 'Test Meeting Simple Format',
            'status' => 'Scheduled',
            'date' => $meetingDate,
            'time' => $meetingTime,
            'duration' => 30,
            'meeting_type' => 'Video Call',
            'attendees' => [$this->contact->id],
            'notifications' => [10, 15], // Simple format
        ]);
    
    $response->assertStatus(201);
    
    // Verify response structure matches expected format
    $response->assertJsonStructure([
        'data' => [
            'id',
            'meeting_title',
            'status',
            'date',
            'time',
            'notifications' => [
                '*' => [
                    'id',
                    'minutes',
                    'unit',
                    'trigger',
                    'is_enabled'
                ]
            ]
        ],
        'message'
    ]);
    
    // Verify notifications in response have correct format
    $notifications = $response->json('data.notifications');
    
    expect($notifications)->toBeArray();
    expect(count($notifications))->toBe(2);
    
    // Verify first notification format
    expect($notifications[0])->toHaveKeys(['id', 'minutes', 'unit', 'trigger', 'is_enabled']);
    expect($notifications[0]['minutes'])->toBeIn([10, 15]);
    expect($notifications[0]['unit'])->toBe('minutes');
    expect($notifications[0]['trigger'])->toBe('before');
    expect($notifications[0]['is_enabled'])->toBe(true);
    
    // Verify second notification format
    expect($notifications[1])->toHaveKeys(['id', 'minutes', 'unit', 'trigger', 'is_enabled']);
    expect($notifications[1]['minutes'])->toBeIn([10, 15]);
    expect($notifications[1]['unit'])->toBe('minutes');
    expect($notifications[1]['trigger'])->toBe('before');
    expect($notifications[1]['is_enabled'])->toBe(true);
    
    // Verify both notifications have different IDs
    expect($notifications[0]['id'])->not->toBe($notifications[1]['id']);
});

test('simple notification format [10, 5] schedules reminders at correct times for 4:40 PM meeting', function () {
    Queue::fake();
    
    $now = Carbon::now();
    // Set meeting time to 4:40 PM (16:40)
    $meetingTime = '16:40';
    $meetingDate = $now->format('Y-m-d');
    
    // Create token for authentication
    $token = $this->user->createToken('test-token')->plainTextToken;
    
    // Create meeting with simple format [10, 5]
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/meeting/save', [
            'meeting_title' => 'Meeting at 4:40 PM',
            'status' => 'Scheduled',
            'date' => $meetingDate,
            'time' => $meetingTime,
            'duration' => 30,
            'meeting_type' => 'Video Call',
            'attendees' => [$this->contact->id],
            'notifications' => [10, 5], // Simple format
        ]);
    
    $response->assertStatus(201);
    
    $meeting = Meeting::where('meeting_title', 'Meeting at 4:40 PM')->first();
    expect($meeting)->not->toBeNull();
    
    // Calculate expected scheduled times
    $meetingDateTime = Carbon::parse("{$meetingDate} {$meetingTime}");
    $expected10MinTime = $meetingDateTime->copy()->subMinutes(10); // 16:30
    $expected5MinTime = $meetingDateTime->copy()->subMinutes(5);   // 16:35
    
    // Verify scheduled notifications in database
    $scheduledNotifications = DB::table('meeting_fcm_notifications')
        ->where('meeting_id', $meeting->id)
        ->where('notification_type', 'reminder')
        ->where('status', 'pending')
        ->orderBy('scheduled_at')
        ->get();
    
    expect($scheduledNotifications->count())->toBe(2);
    
    // Verify scheduled times - order may vary, so check both
    $scheduledTimes = $scheduledNotifications->map(function ($n) {
        return Carbon::parse($n->scheduled_at)->format('H:i');
    })->toArray();
    
    expect($scheduledTimes)->toContain('16:30'); // 10 min before
    expect($scheduledTimes)->toContain('16:35'); // 5 min before
    
    // Verify both are on the same date
    $firstScheduled = Carbon::parse($scheduledNotifications[0]->scheduled_at);
    $secondScheduled = Carbon::parse($scheduledNotifications[1]->scheduled_at);
    expect($firstScheduled->format('Y-m-d'))->toBe($meetingDate);
    expect($secondScheduled->format('Y-m-d'))->toBe($meetingDate);
    
    // Verify both are before meeting time
    expect($firstScheduled->lt($meetingDateTime))->toBeTrue();
    expect($secondScheduled->lt($meetingDateTime))->toBeTrue();
    
    // Verify user_id is correct
    expect($scheduledNotifications[0]->user_id)->toBe($this->user->id);
    expect($scheduledNotifications[1]->user_id)->toBe($this->user->id);
});

test('simple notification format converts to full format in database correctly', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addHours(2)->format('H:i');
    $meetingDate = $now->format('Y-m-d');
    
    // Create token for authentication
    $token = $this->user->createToken('test-token')->plainTextToken;
    
    // Create meeting with simple format [10, 15, 30]
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/meeting/save', [
            'meeting_title' => 'Meeting Multiple Notifications',
            'status' => 'Scheduled',
            'date' => $meetingDate,
            'time' => $meetingTime,
            'duration' => 30,
            'meeting_type' => 'Video Call',
            'attendees' => [$this->contact->id],
            'notifications' => [10, 15, 30], // Simple format with multiple values
        ]);
    
    $response->assertStatus(201);
    
    $meeting = Meeting::where('meeting_title', 'Meeting Multiple Notifications')->first();
    
    // Verify notifications in meeting_notifications table
    $notifications = DB::table('meeting_notifications')
        ->where('meeting_id', $meeting->id)
        ->orderBy('minutes')
        ->get();
    
    expect($notifications->count())->toBe(3);
    
    // Verify all have correct structure
    foreach ($notifications as $notification) {
        expect($notification->unit)->toBe('minutes');
        expect($notification->trigger)->toBe('before');
        expect($notification->is_enabled)->toBe(1);
        expect($notification->minutes)->toBeIn([10, 15, 30]);
    }
    
    // Verify specific minutes values
    $minutes = $notifications->pluck('minutes')->toArray();
    expect($minutes)->toContain(10);
    expect($minutes)->toContain(15);
    expect($minutes)->toContain(30);
});

test('response format matches exact structure as user example', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addHours(2)->format('H:i');
    $meetingDate = $now->format('Y-m-d');
    
    // Create token for authentication
    $token = $this->user->createToken('test-token')->plainTextToken;
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/meeting/save', [
            'meeting_title' => 'Response Format Test',
            'status' => 'Created',
            'date' => $meetingDate,
            'time' => $meetingTime,
            'duration' => 30,
            'meeting_type' => 'Video Call',
            'attendees' => [$this->contact->id],
            'notifications' => [10, 5, 21], // Simple format
        ]);
    
    $response->assertStatus(201);
    
    $data = $response->json('data');
    
    // Verify exact response structure matches user's example
    expect($data)->toHaveKeys([
        'id',
        'organization_id',
        'meeting_title',
        'status',
        'date',
        'time',
        'duration',
        'meeting_type',
        'notifications',
        'created_at',
        'updated_at'
    ]);
    
    // Verify notifications array structure exactly matches
    $notifications = $data['notifications'];
    expect($notifications)->toBeArray();
    expect(count($notifications))->toBe(3);
    
    // Verify each notification has exact structure
    foreach ($notifications as $notification) {
        expect($notification)->toHaveKeys([
            'id',
            'minutes',
            'unit',
            'trigger',
            'is_enabled'
        ]);
        
        // Verify data types
        expect($notification['id'])->toBeInt();
        expect($notification['minutes'])->toBeInt();
        expect($notification['unit'])->toBeString();
        expect($notification['trigger'])->toBeString();
        expect($notification['is_enabled'])->toBeBool();
        
        // Verify values
        expect($notification['unit'])->toBe('minutes');
        expect($notification['trigger'])->toBe('before');
        expect($notification['is_enabled'])->toBe(true);
        expect($notification['minutes'])->toBeIn([10, 5, 21]);
    }
});

test('simple format and full format both produce same response structure', function () {
    Queue::fake();
    
    $now = Carbon::now();
    $meetingTime = $now->copy()->addHours(2)->format('H:i');
    $meetingDate = $now->format('Y-m-d');
    
    // Create token for authentication
    $token = $this->user->createToken('test-token')->plainTextToken;
    
    // Test with simple format
    $responseSimple = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/meeting/save', [
            'meeting_title' => 'Simple Format Test',
            'status' => 'Scheduled',
            'date' => $meetingDate,
            'time' => $meetingTime,
            'duration' => 30,
            'meeting_type' => 'Video Call',
            'attendees' => [$this->contact->id],
            'notifications' => [10, 15], // Simple format
        ]);
    
    // Test with full format
    $responseFull = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/meeting/save', [
            'meeting_title' => 'Full Format Test',
            'status' => 'Scheduled',
            'date' => $meetingDate,
            'time' => $meetingTime,
            'duration' => 30,
            'meeting_type' => 'Video Call',
            'attendees' => [$this->contact->id],
            'notifications' => [ // Full format
                ['minutes' => 10, 'unit' => 'minutes', 'trigger' => 'before', 'is_enabled' => true],
                ['minutes' => 15, 'unit' => 'minutes', 'trigger' => 'before', 'is_enabled' => true],
            ],
        ]);
    
    $responseSimple->assertStatus(201);
    $responseFull->assertStatus(201);
    
    // Both should have same response structure
    $simpleNotifications = $responseSimple->json('data.notifications');
    $fullNotifications = $responseFull->json('data.notifications');
    
    // Verify structure is identical
    expect(count($simpleNotifications))->toBe(2);
    expect(count($fullNotifications))->toBe(2);
    
    // Verify both have same keys
    expect(array_keys($simpleNotifications[0]))->toBe(array_keys($fullNotifications[0]));
    expect(array_keys($simpleNotifications[1]))->toBe(array_keys($fullNotifications[1]));
    
    // Verify both have same values (except id)
    expect($simpleNotifications[0]['minutes'])->toBe($fullNotifications[0]['minutes']);
    expect($simpleNotifications[0]['unit'])->toBe($fullNotifications[0]['unit']);
    expect($simpleNotifications[0]['trigger'])->toBe($fullNotifications[0]['trigger']);
    expect($simpleNotifications[0]['is_enabled'])->toBe($fullNotifications[0]['is_enabled']);
});

