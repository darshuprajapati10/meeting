<?php

namespace Tests\Unit;

use App\Models\SurveyAttachment;
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

test('survey attachment can be created', function () {
    $attachment = SurveyAttachment::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'name' => 'document.pdf',
        'path' => '/storage/attachments/document.pdf',
        'type' => 'application/pdf',
        'size' => 1024,
        'url' => 'https://example.com/document.pdf',
    ]);

    expect($attachment->name)->toBe('document.pdf')
        ->and($attachment->path)->toBe('/storage/attachments/document.pdf')
        ->and($attachment->type)->toBe('application/pdf')
        ->and($attachment->size)->toBe(1024)
        ->and($attachment->url)->toBe('https://example.com/document.pdf')
        ->and($attachment->id)->toBeInt();
});

test('survey attachment belongs to organization', function () {
    $attachment = SurveyAttachment::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'name' => 'document.pdf',
        'path' => '/storage/attachments/document.pdf',
        'type' => 'application/pdf',
        'size' => 1024,
        'url' => 'https://example.com/document.pdf',
    ]);

    expect($attachment->organization)->not->toBeNull()
        ->and($attachment->organization->id)->toBe($this->organization->id);
});

test('survey attachment belongs to user', function () {
    $attachment = SurveyAttachment::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'name' => 'document.pdf',
        'path' => '/storage/attachments/document.pdf',
        'type' => 'application/pdf',
        'size' => 1024,
        'url' => 'https://example.com/document.pdf',
    ]);

    expect($attachment->user)->not->toBeNull()
        ->and($attachment->user->id)->toBe($this->user->id);
});

test('size is cast to integer', function () {
    $attachment = SurveyAttachment::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'name' => 'document.pdf',
        'path' => '/storage/attachments/document.pdf',
        'type' => 'application/pdf',
        'size' => '2048',
        'url' => 'https://example.com/document.pdf',
    ]);

    expect($attachment->size)->toBeInt()
        ->and($attachment->size)->toBe(2048);
});

test('survey attachment can have url', function () {
    $attachment = SurveyAttachment::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
        'name' => 'document.pdf',
        'path' => '/storage/attachments/document.pdf',
        'type' => 'application/pdf',
        'size' => 1024,
        'url' => 'https://example.com/document.pdf',
    ]);

    expect($attachment->url)->toBe('https://example.com/document.pdf');

    $attachment->update(['url' => 'https://example.com/updated.pdf']);
    expect($attachment->url)->toBe('https://example.com/updated.pdf');
    
});

