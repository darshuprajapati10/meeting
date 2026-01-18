<?php

namespace Tests\Unit;

use App\Models\ContactFavourite;
use App\Models\Contact;
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

    $this->contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'created_by' => $this->user->id,
    ]);
});

test('contact favourite can be created', function () {
    $favourite = ContactFavourite::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'contact_id' => $this->contact->id,
        'is_favourite' => true,
    ]);

    expect($favourite->is_favourite)->toBeTrue()
        ->and($favourite->id)->toBeInt();
});

test('contact favourite belongs to organization', function () {
    $favourite = ContactFavourite::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'contact_id' => $this->contact->id,
        'is_favourite' => true,
    ]);

    expect($favourite->organization)->not->toBeNull()
        ->and($favourite->organization->id)->toBe($this->organization->id);
});

test('contact favourite belongs to user', function () {
    $favourite = ContactFavourite::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'contact_id' => $this->contact->id,
        'is_favourite' => true,
    ]);

    expect($favourite->user)->not->toBeNull()
        ->and($favourite->user->id)->toBe($this->user->id);
});

test('contact favourite belongs to contact', function () {
    $favourite = ContactFavourite::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'contact_id' => $this->contact->id,
        'is_favourite' => true,
    ]);

    expect($favourite->contact)->not->toBeNull()
        ->and($favourite->contact->id)->toBe($this->contact->id);
});

test('is_favourite is cast to boolean', function () {
    $favourite = ContactFavourite::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'contact_id' => $this->contact->id,
        'is_favourite' => 1,
    ]);

    expect($favourite->is_favourite)->toBeTrue();

    $favourite->update(['is_favourite' => 0]);
    expect($favourite->is_favourite)->toBeFalse();
});

test('contact favourite can be toggled', function () {
    $favourite = ContactFavourite::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'contact_id' => $this->contact->id,
        'is_favourite' => true,
    ]);

    expect($favourite->is_favourite)->toBeTrue();

    $favourite->update(['is_favourite' => false]);
    expect($favourite->fresh()->is_favourite)->toBeFalse();
});

