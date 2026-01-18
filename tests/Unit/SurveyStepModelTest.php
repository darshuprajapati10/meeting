<?php

namespace Tests\Unit;

use App\Models\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyField;
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

    $this->survey = Survey::create([
        'organization_id' => $this->organization->id,
        'survey_name' => 'Customer Feedback',
        'status' => 'Active',
        'created_by' => $this->user->id,
    ]);
});

test('survey step can be created', function () {
    $step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'tagline' => 'Introduction',
        'order' => 1,
    ]);

    expect($step->step)->toBe('Step 1')
        ->and($step->tagline)->toBe('Introduction')
        ->and($step->order)->toBe(1)
        ->and($step->id)->toBeInt();
});

test('survey step belongs to survey', function () {
    $step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'tagline' => 'Introduction',
        'order' => 1,
    ]);

    expect($step->survey)->not->toBeNull()
        ->and($step->survey->id)->toBe($this->survey->id)
        ->and($step->survey->survey_name)->toBe('Customer Feedback');
});

test('survey step can have multiple fields', function () {
    $step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'order' => 1,
    ]);

    $field1 = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $step->id,
        'name' => 'Question 1',
        'type' => 'Short Answer',
        'order' => 1,
    ]);

    $field2 = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $step->id,
        'name' => 'Question 2',
        'type' => 'Multiple Choice',
        'order' => 2,
    ]);

    expect($step->surveyFields)->toHaveCount(2)
        ->and($step->surveyFields->first()->name)->toBe('Question 1')
        ->and($step->surveyFields->last()->name)->toBe('Question 2');
});

test('survey fields are ordered by order field', function () {
    $step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'order' => 1,
    ]);

    SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $step->id,
        'name' => 'Question 3',
        'type' => 'Short Answer',
        'order' => 3,
    ]);

    SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $step->id,
        'name' => 'Question 1',
        'type' => 'Short Answer',
        'order' => 1,
    ]);

    SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $step->id,
        'name' => 'Question 2',
        'type' => 'Short Answer',
        'order' => 2,
    ]);

    $fields = $step->surveyFields;
    expect($fields->first()->order)->toBe(1)
        ->and($fields->get(1)->order)->toBe(2)
        ->and($fields->last()->order)->toBe(3);
});

test('survey step can have optional tagline', function () {
    $step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'order' => 1,
    ]);

    expect($step->tagline)->toBeNull();

    $step->update(['tagline' => 'Introduction']);
    expect($step->tagline)->toBe('Introduction');
});

