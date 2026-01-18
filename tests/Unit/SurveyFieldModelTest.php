<?php

namespace Tests\Unit;

use App\Models\SurveyField;
use App\Models\Survey;
use App\Models\SurveyStep;
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

    $this->step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'order' => 1,
    ]);
});

test('survey field can be created', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'What is your name?',
        'type' => 'Short Answer',
        'is_required' => true,
        'order' => 1,
    ]);

    expect($field->name)->toBe('What is your name?')
        ->and($field->type)->toBe('Short Answer')
        ->and($field->is_required)->toBeTrue()
        ->and($field->id)->toBeInt();
});

test('survey field belongs to organization', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Question',
        'type' => 'Short Answer',
        'order' => 1,
    ]);

    expect($field->organization)->not->toBeNull()
        ->and($field->organization->id)->toBe($this->organization->id);
});

test('survey field belongs to survey', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Question',
        'type' => 'Short Answer',
        'order' => 1,
    ]);

    expect($field->survey)->not->toBeNull()
        ->and($field->survey->id)->toBe($this->survey->id);
});

test('survey field belongs to survey step', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Question',
        'type' => 'Short Answer',
        'order' => 1,
    ]);

    expect($field->surveyStep)->not->toBeNull()
        ->and($field->surveyStep->id)->toBe($this->step->id);
});

test('options is cast to array', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Select option',
        'type' => 'Multiple Choice',
        'options' => ['Option 1', 'Option 2', 'Option 3'],
        'order' => 1,
    ]);

    expect($field->options)->toBeArray()
        ->and($field->options)->toContain('Option 1')
        ->and($field->options)->toContain('Option 2')
        ->and($field->options)->toContain('Option 3');
});

test('is_required is cast to boolean', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Question',
        'type' => 'Short Answer',
        'is_required' => 1,
        'order' => 1,
    ]);

    expect($field->is_required)->toBeTrue();

    $field->update(['is_required' => 0]);
    expect($field->fresh()->is_required)->toBeFalse();
});

test('survey field can have optional description', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Question',
        'type' => 'Short Answer',
        'description' => 'Please provide your answer',
        'order' => 1,
    ]);

    expect($field->description)->toBe('Please provide your answer');
});

test('survey field can have null options', function () {
    $field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'Question',
        'type' => 'Short Answer',
        'options' => null,
        'order' => 1,
    ]);

    expect($field->options)->toBeNull();
});

