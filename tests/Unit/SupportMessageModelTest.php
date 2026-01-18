<?php

namespace Tests\Unit;

use App\Models\SupportMessage;
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

    $this->supportUser = User::create([
        'name' => 'Support User',
        'email' => 'support@example.com',
        'password' => Hash::make('password'),
    ]);
});

test('support message can be created', function () {
    $message = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Need Help',
        'message' => 'I need assistance with my account',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    expect($message->subject)->toBe('Need Help')
        ->and($message->message)->toBe('I need assistance with my account')
        ->and($message->status)->toBe('open')
        ->and($message->id)->toBeInt();
});

test('support message belongs to user', function () {
    $message = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Need Help',
        'message' => 'I need assistance',
        'status' => 'open',
    ]);

    expect($message->user)->not->toBeNull()
        ->and($message->user->id)->toBe($this->user->id)
        ->and($message->user->name)->toBe('Test User');
});

test('support message can be assigned to support user', function () {
    $message = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Need Help',
        'message' => 'I need assistance',
        'status' => 'open',
        'assigned_to' => $this->supportUser->id,
    ]);

    expect($message->assignedTo)->not->toBeNull()
        ->and($message->assignedTo->id)->toBe($this->supportUser->id)
        ->and($message->assignedTo->name)->toBe('Support User');
});

test('responded_at is cast to datetime', function () {
    $now = now();
    $message = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Need Help',
        'message' => 'I need assistance',
        'status' => 'open',
        'responded_at' => $now,
    ]);

    expect($message->responded_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('support message can have response', function () {
    $message = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Need Help',
        'message' => 'I need assistance',
        'status' => 'closed',
        'response' => 'Thank you for contacting us. We have resolved your issue.',
        'responded_at' => now(),
    ]);

    expect($message->response)->toBe('Thank you for contacting us. We have resolved your issue.')
        ->and($message->responded_at)->not->toBeNull();
});

test('support message can have different priorities', function () {
    $lowPriority = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Low Priority',
        'message' => 'Minor issue',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $highPriority = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'High Priority',
        'message' => 'Urgent issue',
        'status' => 'open',
        'priority' => 'high',
    ]);

    expect($lowPriority->priority)->toBe('low')
        ->and($highPriority->priority)->toBe('high');
});

test('support message can have different statuses', function () {
    $open = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Open',
        'message' => 'New message',
        'status' => 'open',
    ]);

    $closed = SupportMessage::create([
        'user_id' => $this->user->id,
        'email' => 'test@example.com',
        'subject' => 'Closed',
        'message' => 'Resolved message',
        'status' => 'closed',
    ]);

    expect($open->status)->toBe('open')
        ->and($closed->status)->toBe('closed');
});

