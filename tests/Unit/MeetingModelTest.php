<?php

namespace Tests\Unit;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use App\Models\Contact;
use App\Models\Survey;
use App\Models\SurveySubmission;
use App\Models\MeetingNotification;
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
});

test('meeting can be created', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    expect($meeting->meeting_title)->toBe('Team Standup')
        ->and($meeting->status)->toBe('Scheduled')
        ->and($meeting->duration)->toBe(30)
        ->and($meeting->id)->toBeInt();
});

test('meeting belongs to organization', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    expect($meeting->organization)->not->toBeNull()
        ->and($meeting->organization->id)->toBe($this->organization->id);
});

test('meeting belongs to creator', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    expect($meeting->creator)->not->toBeNull()
        ->and($meeting->creator->id)->toBe($this->user->id);
});

test('meeting can belong to survey', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Meeting Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'survey_id' => $survey->id,
        'created_by' => $this->user->id,
    ]);

    expect($meeting->survey)->not->toBeNull()
        ->and($meeting->survey->id)->toBe($survey->id);
});

test('meeting can have multiple attendees', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $contact1 = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'created_by' => $this->user->id,
    ]);

    $contact2 = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'created_by' => $this->user->id,
    ]);

    $meeting->attendees()->attach([$contact1->id, $contact2->id]);

    expect($meeting->attendees)->toHaveCount(2)
        ->and($meeting->attendees->first()->id)->toBe($contact1->id)
        ->and($meeting->attendees->last()->id)->toBe($contact2->id);
});

test('meeting can have notifications', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    $notification = MeetingNotification::create([
        'meeting_id' => $meeting->id,
        'minutes' => 15,
        'unit' => 'before',
        'trigger' => 'reminder',
        'is_enabled' => true,
    ]);

    expect($meeting->notifications)->toHaveCount(1)
        ->and($meeting->notifications->first()->id)->toBe($notification->id);
});

test('meeting date is cast to date', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    expect($meeting->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($meeting->date->format('Y-m-d'))->toBe('2025-12-20');
});

test('meeting duration is cast to integer', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => '30',
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    expect($meeting->duration)->toBeInt()
        ->and($meeting->duration)->toBe(30);
});

test('hasUserSubmittedSurvey returns true when user submitted survey', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Meeting Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'survey_id' => $survey->id,
        'created_by' => $this->user->id,
    ]);

    SurveySubmission::create([
        'user_id' => $this->user->id,
        'meeting_id' => $meeting->id,
        'survey_id' => $survey->id,
        'submitted_at' => now(),
    ]);

    expect($meeting->hasUserSubmittedSurvey($this->user->id))->toBeTrue();
});

test('hasUserSubmittedSurvey returns false when user has not submitted survey', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Meeting Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'survey_id' => $survey->id,
        'created_by' => $this->user->id,
    ]);

    expect($meeting->hasUserSubmittedSurvey($this->user->id))->toBeFalse();
});

test('hasUserSubmittedSurvey returns false when meeting has no survey', function () {
    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'created_by' => $this->user->id,
    ]);

    expect($meeting->hasUserSubmittedSurvey($this->user->id))->toBeFalse();
});

test('meeting can have survey submissions', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Meeting Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    $meeting = Meeting::create([
        'organization_id' => $this->organization->id,
        'meeting_title' => 'Team Standup',
        'status' => 'Scheduled',
        'date' => '2025-12-20',
        'time' => '10:00:00',
        'duration' => 30,
        'meeting_type' => 'Video Call',
        'survey_id' => $survey->id,
        'created_by' => $this->user->id,
    ]);

    SurveySubmission::create([
        'user_id' => $this->user->id,
        'meeting_id' => $meeting->id,
        'survey_id' => $survey->id,
        'submitted_at' => now(),
    ]);

    expect($meeting->surveySubmissions)->toHaveCount(1);
});

