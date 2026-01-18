<?php

namespace Tests\Unit;

use App\Models\Survey;
use App\Models\Organization;
use App\Models\User;
use App\Models\SurveyStep;
use App\Models\SurveyResponse;
use App\Models\SurveySubmission;
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

test('survey can be created', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'description' => 'Feedback survey',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    expect($survey->survey_name)->toBe('Customer Feedback')
        ->and($survey->description)->toBe('Feedback survey')
        ->and($survey->status)->toBe('Active')
        ->and($survey->id)->toBeInt();
});

test('survey belongs to organization', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    expect($survey->organization)->not->toBeNull()
        ->and($survey->organization->id)->toBe($this->organization->id);
});

test('survey belongs to creator', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    expect($survey->creator)->not->toBeNull()
        ->and($survey->creator->id)->toBe($this->user->id);
});

test('survey can have multiple steps', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    $step1 = SurveyStep::create([
        'survey_id' => $survey->id,
        'step' => 'Step 1',
        'tagline' => 'Introduction',
        'order' => 1,
    ]);

    $step2 = SurveyStep::create([
        'survey_id' => $survey->id,
        'step' => 'Step 2',
        'tagline' => 'Questions',
        'order' => 2,
    ]);

    expect($survey->surveySteps)->toHaveCount(2)
        ->and($survey->surveySteps->first()->step)->toBe('Step 1')
        ->and($survey->surveySteps->last()->step)->toBe('Step 2');
});

test('survey steps are ordered by order field', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    SurveyStep::create([
        'survey_id' => $survey->id,
        'step' => 'Step 3',
        'order' => 3,
    ]);

    SurveyStep::create([
        'survey_id' => $survey->id,
        'step' => 'Step 1',
        'order' => 1,
    ]);

    SurveyStep::create([
        'survey_id' => $survey->id,
        'step' => 'Step 2',
        'order' => 2,
    ]);

    $steps = $survey->surveySteps;
    expect($steps->first()->order)->toBe(1)
        ->and($steps->get(1)->order)->toBe(2)
        ->and($steps->last()->order)->toBe(3);
});

test('survey can have survey responses', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    SurveyResponse::create([
        'survey_id' => $survey->id,
        'user_id' => $this->user->id,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($survey->surveyResponses)->toHaveCount(1);
});

test('survey can have survey submissions', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

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

    SurveySubmission::create([
        'survey_id' => $survey->id,
        'meeting_id' => $meeting->id,
        'user_id' => $this->user->id,
        'submitted_at' => now(),
    ]);

    expect($survey->surveySubmissions)->toHaveCount(1);
});

test('getResponseCount returns correct count with meeting submissions', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

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

    SurveySubmission::create([
        'survey_id' => $survey->id,
        'user_id' => $this->user->id,
        'meeting_id' => $meeting->id,
        'submitted_at' => now(),
    ]);

    expect($survey->getResponseCount())->toBe(1);
});

test('getResponseCount includes responses without meeting_id', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    SurveyResponse::create([
        'survey_id' => $survey->id,
        'user_id' => $this->user->id,
        'meeting_id' => null,
        'response_data' => ['answer' => 'test'],
        'submitted_at' => now(),
    ]);

    expect($survey->getResponseCount())->toBe(1);
});

test('getResponseCount returns zero when no responses', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    expect($survey->getResponseCount())->toBe(0);
});

test('survey status is cast to string', function () {
    $survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);

    expect($survey->status)->toBeString()
        ->and($survey->status)->toBe('Active');
});

