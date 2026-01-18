<?php

namespace Tests\Unit;

use App\Models\SurveyResponse;
use App\Models\Survey;
use App\Models\Organization;
use App\Models\User;
use App\Models\Contact;
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

test('survey response can be created', function () {
    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($response->response_data)->toBeArray()
        ->and($response->response_data)->toHaveKey('answer')
        ->and($response->id)->toBeInt();
});

test('survey response belongs to survey', function () {
    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($response->survey)->not->toBeNull()
        ->and($response->survey->id)->toBe($this->survey->id);
});

test('survey response belongs to user', function () {
    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($response->user)->not->toBeNull()
        ->and($response->user->id)->toBe($this->user->id);
});

test('survey response can belong to meeting', function () {
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

    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($response->meeting)->not->toBeNull()
        ->and($response->meeting->id)->toBe($meeting->id);
});

test('survey response can belong to contact', function () {
    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'created_by' => $this->user->id,
    ]);

    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'contact_id' => $contact->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($response->contact)->not->toBeNull()
        ->and($response->contact->id)->toBe($contact->id);
});

test('response_data is cast to array', function () {
    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->user->id,
        'response_data' => ['field1' => 'value1', 'field2' => 'value2'],
        'submitted_at' => now(),
    ]);

    expect($response->response_data)->toBeArray()
        ->and($response->response_data)->toHaveKey('field1')
        ->and($response->response_data)->toHaveKey('field2')
        ->and($response->response_data['field1'])->toBe('value1');
});

test('submitted_at is cast to datetime', function () {
    $now = now();
    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => $now,
    ]);

    expect($response->submitted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('survey response can have null meeting_id', function () {
    $response = SurveyResponse::create([
        'survey_id' => $this->survey->id,
        'user_id' => $this->user->id,
        'meeting_id' => null,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($response->meeting_id)->toBeNull();
});

