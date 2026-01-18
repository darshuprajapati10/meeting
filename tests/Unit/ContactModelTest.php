<?php

namespace Tests\Unit;

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
});

test('contact can be created', function () {
    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'created_by' => $this->user->id,
    ]);

    expect($contact->first_name)->toBe('John')
        ->and($contact->last_name)->toBe('Doe')
        ->and($contact->email)->toBe('john@example.com')
        ->and($contact->id)->toBeInt();
});

test('contact belongs to organization', function () {
    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'created_by' => $this->user->id,
    ]);

    expect($contact->organization)->not->toBeNull()
        ->and($contact->organization->id)->toBe($this->organization->id)
        ->and($contact->organization->name)->toBe('Test Organization');
});

test('contact belongs to creator user', function () {
    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'created_by' => $this->user->id,
    ]);

    expect($contact->creator)->not->toBeNull()
        ->and($contact->creator->id)->toBe($this->user->id)
        ->and($contact->creator->name)->toBe('Test User');
});

test('contact can have referrer', function () {
    $referrer = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'created_by' => $this->user->id,
    ]);

    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'referrer_id' => $referrer->id,
        'created_by' => $this->user->id,
    ]);

    expect($contact->referrer)->not->toBeNull()
        ->and($contact->referrer->id)->toBe($referrer->id)
        ->and($contact->referrer->first_name)->toBe('Jane');
});

test('contact groups are cast to array', function () {
    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'groups' => ['group1', 'group2'],
        'created_by' => $this->user->id,
    ]);

    expect($contact->groups)->toBeArray()
        ->and($contact->groups)->toContain('group1')
        ->and($contact->groups)->toContain('group2');
});

test('contact has valid avatar colors', function () {
    $validColors = Contact::AVATAR_COLORS;
    
    expect($validColors)->toBeArray()
        ->and($validColors)->toContain('bg-teal')
        ->and($validColors)->toContain('bg-lavender')
        ->and($validColors)->toContain('bg-navy')
        ->and($validColors)->toContain('bg-purple')
        ->and($validColors)->toContain('bg-green')
        ->and($validColors)->toContain('bg-orange')
        ->and($validColors)->toContain('bg-pink')
        ->and($validColors)->toContain('bg-blue');
});

test('contact can have optional fields', function () {
    $contact = Contact::create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'company' => 'Tech Corp',
        'job_title' => 'Developer',
        'address' => '123 Main St',
        'notes' => 'Important contact',
        'avatar_color' => 'bg-blue',
        'created_by' => $this->user->id,
    ]);

    expect($contact->phone)->toBe('+1234567890')
        ->and($contact->company)->toBe('Tech Corp')
        ->and($contact->job_title)->toBe('Developer')
        ->and($contact->address)->toBe('123 Main St')
        ->and($contact->notes)->toBe('Important contact')
        ->and($contact->avatar_color)->toBe('bg-blue');
});

