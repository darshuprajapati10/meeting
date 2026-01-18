<?php

namespace Tests\Unit;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
});

test('notification preference can be created', function () {
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
        'push_notifications_enabled' => true,
        'email_notifications_enabled' => true,
    ]);

    expect($preference->user_id)->toBe($this->user->id)
        ->and($preference->push_notifications_enabled)->toBeTrue()
        ->and($preference->id)->toBeInt();
});

test('notification preference belongs to user', function () {
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
        'push_notifications_enabled' => true,
    ]);

    expect($preference->user)->not->toBeNull()
        ->and($preference->user->id)->toBe($this->user->id);
});

test('boolean fields are cast to boolean', function () {
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
        'push_notifications_enabled' => 1,
        'email_notifications_enabled' => 0,
        'email_meeting_reminders' => 1,
        'email_meeting_updates' => 0,
        'email_meeting_cancellations' => 1,
        'notification_sound' => 0,
        'notification_badge' => 1,
    ]);

    expect($preference->push_notifications_enabled)->toBeTrue()
        ->and($preference->email_notifications_enabled)->toBeFalse()
        ->and($preference->email_meeting_reminders)->toBeTrue()
        ->and($preference->email_meeting_updates)->toBeFalse()
        ->and($preference->email_meeting_cancellations)->toBeTrue()
        ->and($preference->notification_sound)->toBeFalse()
        ->and($preference->notification_badge)->toBeTrue();
});

test('meeting_reminders is cast to array', function () {
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
        'meeting_reminders' => [15, 30, 60],
    ]);

    expect($preference->meeting_reminders)->toBeArray()
        ->and($preference->meeting_reminders)->toContain(15)
        ->and($preference->meeting_reminders)->toContain(30)
        ->and($preference->meeting_reminders)->toContain(60);
});

test('getMeetingRemindersAttribute returns default when null', function () {
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
    ]);

    // Access the attribute to trigger the getter
    $reminders = $preference->meeting_reminders;
    
    expect($reminders)->toBeArray()
        ->and($reminders)->toContain(15);
});

test('getMeetingRemindersAttribute handles JSON string', function () {
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
    ]);

    // Manually set as JSON string (simulating database storage)
    $preference->setRawAttributes(array_merge($preference->getAttributes(), [
        'meeting_reminders' => '[15, 30]',
    ]));

    $reminders = $preference->meeting_reminders;
    
    expect($reminders)->toBeArray()
        ->and($reminders)->toContain(15)
        ->and($reminders)->toContain(30);
});

test('notification preference has default attributes', function () {
    $preference = new NotificationPreference();
    
    expect($preference->push_notifications_enabled)->toBeTrue()
        ->and($preference->email_notifications_enabled)->toBeTrue()
        ->and($preference->email_meeting_reminders)->toBeTrue()
        ->and($preference->email_meeting_updates)->toBeTrue()
        ->and($preference->email_meeting_cancellations)->toBeTrue()
        ->and($preference->notification_sound)->toBeTrue()
        ->and($preference->notification_badge)->toBeTrue();
});

