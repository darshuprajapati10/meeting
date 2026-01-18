<?php

namespace Tests\Unit;

use App\Models\MeetingNotification;
use App\Models\Meeting;
use App\Models\Organization;
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

    $this->organization = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'status' => 'active',
    ]);

    $this->meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);
});

test('meeting notification can be created', function () {
    $notification = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => 15,
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => true,
    ]);

    expect($notification->minutes)->toBe(15)
        ->and($notification->unit)->toBe('before')
        ->and($notification->trigger)->toBe('reminder')
        ->and($notification->is_enabled)->toBeTrue()
        ->and($notification->id)->toBeInt();
});

test('meeting notification belongs to meeting', function () {
    $notification = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => 15,
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => true,
    ]);

    expect($notification->meeting)->not->toBeNull()
        ->and($notification->meeting->id)->toBe($this->meeting->id)
        ->and($notification->meeting->meeting_title)->toBe('Team Standup');
});

test('minutes is cast to integer', function () {
    $notification = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => '15',
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => true,
    ]);

    expect($notification->minutes)->toBeInt()
        ->and($notification->minutes)->toBe(15);
});

test('is_enabled is cast to boolean', function () {
    $notification = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => 15,
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => 1,
    ]);

    expect($notification->is_enabled)->toBeTrue();

    $notification->update(['is_enabled' => 0]);
    expect($notification->fresh()->is_enabled)->toBeFalse();
});

test('meeting notification can be disabled', function () {
    $notification = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => 15,
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => true,
    ]);

    expect($notification->is_enabled)->toBeTrue();

    $notification->update(['is_enabled' => false]);
    expect($notification->fresh()->is_enabled)->toBeFalse();
});

test('meeting can have multiple notifications', function () {
    $notification1 = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => 15,
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => true,
    ]);

    $notification2 = MeetingNotification::create([
        'meeting_id' => $this->meeting->id,
        'minutes' => 5,
        'unit' => 'before',
        'trigger' => 'starting',
        'is_enabled' => true,
    ]);

    expect($this->meeting->notifications)->toHaveCount(2)
        ->and($this->meeting->notifications->first()->minutes)->toBe(15)
        ->and($this->meeting->notifications->last()->minutes)->toBe(5);
});

