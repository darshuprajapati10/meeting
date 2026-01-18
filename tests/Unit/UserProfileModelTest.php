<?php

namespace Tests\Unit;

use App\Models\UserProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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

    $this->user->organizations()->attach($this->organization->id, ['role' => 'admin']);
});

test('user profile can be created', function () {
    $profile = UserProfile::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_address' => 'john@example.com',
        'address' => '123 Main St',
        'company' => 'Tech Corp',
    ]);

    expect($profile->first_name)->toBe('John')
        ->and($profile->last_name)->toBe('Doe')
        ->and($profile->email_address)->toBe('john@example.com')
        ->and($profile->id)->toBeInt();
});

test('user profile belongs to organization', function () {
    $profile = UserProfile::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_address' => 'john@example.com',
        'address' => '123 Main St',
        'company' => 'Tech Corp',
    ]);

    expect($profile->organization)->not->toBeNull()
        ->and($profile->organization->id)->toBe($this->organization->id);
});

test('user profile belongs to user', function () {
    $profile = UserProfile::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_address' => 'john@example.com',
        'address' => '123 Main St',
        'company' => 'Tech Corp',
    ]);

    expect($profile->user)->not->toBeNull()
        ->and($profile->user->id)->toBe($this->user->id);
});

test('user profile can have all optional fields', function () {
    $profile = UserProfile::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'bio' => 'Software Developer',
        'email_address' => 'john@example.com',
        'address' => '123 Main St',
        'company' => 'Tech Corp',
        'company' => 'Tech Corp',
        'phone' => '+1234567890',
        'job_title' => 'Senior Developer',
        'department' => 'Engineering',
        'timezone' => 'America/New_York',
    ]);

    expect($profile->bio)->toBe('Software Developer')
        ->and($profile->email_address)->toBe('john@example.com')
        ->and($profile->address)->toBe('123 Main St')
        ->and($profile->company)->toBe('Tech Corp')
        ->and($profile->phone)->toBe('+1234567890')
        ->and($profile->job_title)->toBe('Senior Developer')
        ->and($profile->department)->toBe('Engineering')
        ->and($profile->timezone)->toBe('America/New_York');
});

test('user profile auto-sets user_id from authenticated user on create', function () {
    Auth::login($this->user);

    $profile = UserProfile::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_address' => 'john@example.com',
        'address' => '123 Main St',
        'company' => 'Tech Corp',
    ]);

    expect($profile->user_id)->toBe($this->user->id);

    Auth::logout();
});

test('user profile auto-sets organization_id from authenticated user on create', function () {
    Auth::login($this->user);

    $profile = UserProfile::create([
        'user_id' => $this->user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_address' => 'john@example.com',
        'address' => '123 Main St',
        'company' => 'Tech Corp',
    ]);

    expect($profile->organization_id)->toBe($this->organization->id);

    Auth::logout();
});

