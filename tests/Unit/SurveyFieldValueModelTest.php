<?php

namespace Tests\Unit;

use App\Models\SurveyFieldValue;
use App\Models\Survey;
use App\Models\SurveyStep;
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

    $this->step = SurveyStep::create([
        'survey_id' => $this->survey->id,
        'step' => 'Step 1',
        'order' => 1,
    ]);

    $this->field = SurveyField::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'name' => 'What is your name?',
        'type' => 'Short Answer',
        'order' => 1,
    ]);
});

test('survey field value can be created', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 'John Doe',
    ]);

    expect($fieldValue->value)->toBe('John Doe')
        ->and($fieldValue->id)->toBeInt();
});

test('survey field value belongs to organization', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 'John Doe',
    ]);

    expect($fieldValue->organization)->not->toBeNull()
        ->and($fieldValue->organization->id)->toBe($this->organization->id);
});

test('survey field value belongs to survey', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 'John Doe',
    ]);

    expect($fieldValue->survey)->not->toBeNull()
        ->and($fieldValue->survey->id)->toBe($this->survey->id);
});

test('survey field value belongs to survey step', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 'John Doe',
    ]);

    expect($fieldValue->surveyStep)->not->toBeNull()
        ->and($fieldValue->surveyStep->id)->toBe($this->step->id);
});

test('survey field value belongs to survey field', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 'John Doe',
    ]);

    expect($fieldValue->surveyField)->not->toBeNull()
        ->and($fieldValue->surveyField->id)->toBe($this->field->id);
});

test('survey field value belongs to user', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 'John Doe',
    ]);

    expect($fieldValue->user)->not->toBeNull()
        ->and($fieldValue->user->id)->toBe($this->user->id);
});

test('value is cast to string', function () {
    $fieldValue = SurveyFieldValue::create([
        'organization_id' => $this->organization->id,
        'survey_id' => $this->survey->id,
        'survey_step_id' => $this->step->id,
        'survey_field_id' => $this->field->id,
        'user_id' => $this->user->id,
        'value' => 12345,
    ]);

    expect($fieldValue->value)->toBeString()
        ->and($fieldValue->value)->toBe('12345');
});

