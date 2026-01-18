<?php

namespace Tests\Unit;

use App\Models\SurveySubmission;
use App\Models\Survey;
use App\Models\Organization;
use App\Models\User;
use App\Models\Meeting;
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

    $this->survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);
});

test('survey submission can be created', function () {
    $meeting = \App\Models\Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $submission = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    expect($submission->survey_id)->toBe($this->survey->id)
        ->and($submission->user_id)->toBe($this->user->id)
        ->and($submission->id)->toBeInt();
});

test('survey submission belongs to survey', function () {
    $meeting = \App\Models\Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $submission = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    expect($submission->survey)->not->toBeNull()
        ->and($submission->survey->id)->toBe($this->survey->id);
});

test('survey submission belongs to user', function () {
    $meeting = \App\Models\Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $submission = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    expect($submission->user)->not->toBeNull()
        ->and($submission->user->id)->toBe($this->user->id);
});

test('survey submission can belong to meeting', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $submission = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    expect($submission->meeting)->not->toBeNull()
        ->and($submission->meeting->id)->toBe($meeting->id);
});

test('submitted_at is cast to datetime', function () {
    $meeting = \App\Models\Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $now = now();
    $submission = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => $now,
    ]);

    expect($submission->submitted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('survey submission belongs to meeting', function () {
    $meeting = \App\Models\Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $submission = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    expect($submission->meeting_id)->toBe($meeting->id)
        ->and($submission->meeting)->not->toBeNull();
});

test('multiple users can submit same survey', function () {
    $meeting = \App\Models\Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Test Meeting',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $user2 = User::create([
        'name' => 'User 2',
        'email' => 'user2@example.com',
        'password' => Hash::make('password'),
    ]);

    $submission1 = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    $submission2 = SurveySubmission::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $user2->id,
        'submitted_at' => now(),
    ]);

    expect($submission1->user_id)->toBe($this->user->id)
        ->and($submission2->user_id)->toBe($user2->id)
        ->and($submission1->id)->not->toBe($submission2->id);
});

