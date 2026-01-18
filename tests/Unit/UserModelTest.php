<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can be created', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    expect($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->id)->toBeInt();
});

test('user password is hashed', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'plain-password',
    ]);

    expect($user->password)->not->toBe('plain-password')
        ->and(Hash::check('plain-password', $user->password))->toBeTrue();
});

test('user can belong to multiple organizations', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    $org1 = Organization::create([
        'name' => 'Org 1',
        'slug' => 'org-1',
        'status' => 'active',
    ]);

    $org2 = Organization::create([
        'name' => 'Org 2',
        'slug' => 'org-2',
        'status' => 'active',
    ]);

    $user->organizations()->attach($org1->id, ['role' => 'admin']);
    $user->organizations()->attach($org2->id, ['role' => 'member']);

    expect($user->organizations)->toHaveCount(2)
        ->and($user->organizations->first()->pivot->role)->toBe('admin')
        ->and($user->organizations->last()->pivot->role)->toBe('member');
});

test('user has fillable attributes', function () {
    $user = new User();
    $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password',
        'google_id', 'phone', 'address', 'bio', 'job_title',
        'department', 'company', 'profile_picture', 'timezone',
        'email_change_new_email', 'email_change_token',
        'email_change_token_expires_at', 'email_change_requested_at',
        'password_changed_at', 'account_deleted_at',
    ];

    expect($user->getFillable())->toBe($fillable);
});

test('user has hidden attributes', function () {
    $user = new User();
    $hidden = ['password', 'remember_token'];

    expect($user->getHidden())->toBe($hidden);
});

test('user has correct date casts', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    $user->email_verified_at = now();
    $user->save();
    $user->refresh();

    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('user can have optional profile fields', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+1234567890',
        'bio' => 'Software Developer',
        'job_title' => 'Senior Developer',
        'company' => 'Tech Corp',
    ]);

    expect($user->first_name)->toBe('John')
        ->and($user->last_name)->toBe('Doe')
        ->and($user->phone)->toBe('+1234567890')
        ->and($user->bio)->toBe('Software Developer')
        ->and($user->job_title)->toBe('Senior Developer')
        ->and($user->company)->toBe('Tech Corp');
});

