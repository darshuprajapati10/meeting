<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('organization can be created', function () {
    $org = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'description' => 'Test Description',
        'status' => 'active',
    ]);

    expect($org->name)->toBe('Test Organization')
        ->and($org->slug)->toBe('test-org')
        ->and($org->status)->toBe('active')
        ->and($org->id)->toBeInt();
});

test('organization can have multiple users', function () {
    $org = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'status' => 'active',
    ]);

    $user1 = User::create([
        'name' => 'User 1',
        'email' => 'user1@example.com',
        'password' => Hash::make('password'),
    ]);

    $user2 = User::create([
        'name' => 'User 2',
        'email' => 'user2@example.com',
        'password' => Hash::make('password'),
    ]);

    $org->users()->attach($user1->id, ['role' => 'admin']);
    $org->users()->attach($user2->id, ['role' => 'member']);

    expect($org->users)->toHaveCount(2)
        ->and($org->users->first()->name)->toBe('User 1')
        ->and($org->users->last()->name)->toBe('User 2');
});

test('organization supports soft deletes', function () {
    $org = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'status' => 'active',
    ]);

    $orgId = $org->id;
    $org->delete();

    expect(Organization::find($orgId))->toBeNull()
        ->and(Organization::withTrashed()->find($orgId))->not->toBeNull()
        ->and(Organization::withTrashed()->find($orgId)->trashed())->toBeTrue();
});

test('organization has all fillable attributes', function () {
    $org = new Organization();
    $fillable = [
        'name', 'slug', 'description', 'email', 'phone', 'address',
        'status', 'type', 'gst_status', 'gst_in', 'place_of_supply',
        'shipping_address', 'shipping_city', 'shipping_zip', 'shipping_phone',
        'billing_address', 'billing_city', 'billing_zip', 'billing_phone',
    ];

    expect($org->getFillable())->toBe($fillable);
});

test('organization can have optional fields', function () {
    $org = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'status' => 'active',
        'email' => 'contact@test.org',
        'phone' => '+1234567890',
        'address' => '123 Main St',
        'type' => 'business',
        'gst_status' => 'registered',
        'gst_in' => 'GST123456',
    ]);

    expect($org->email)->toBe('contact@test.org')
        ->and($org->phone)->toBe('+1234567890')
        ->and($org->address)->toBe('123 Main St')
        ->and($org->type)->toBe('business')
        ->and($org->gst_status)->toBe('registered')
        ->and($org->gst_in)->toBe('GST123456');
});

test('organization can be restored after soft delete', function () {
    $org = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'status' => 'active',
    ]);

    $org->delete();
    expect(Organization::find($org->id))->toBeNull();

    $org->restore();
    expect(Organization::find($org->id))->not->toBeNull();
});

