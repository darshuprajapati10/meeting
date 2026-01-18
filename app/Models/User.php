<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'google_id',
        'phone',
        'address',
        'bio',
        'job_title',
        'department',
        'company',
        'profile_picture',
        'timezone',
        'email_change_new_email',
        'email_change_token',
        'email_change_token_expires_at',
        'email_change_requested_at',
        'password_changed_at',
        'account_deleted_at',
        'is_platform_admin',
        'email_verification_token',
        'email_verification_sent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_change_token_expires_at' => 'datetime',
            'email_change_requested_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'account_deleted_at' => 'datetime',
            'email_verification_sent_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    /**
     * Get the organizations that the user belongs to.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the first organization for this user (helper method).
     */
    public function organization()
    {
        return $this->organizations()->first();
    }

    /**
     * Check if user's email is verified
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Generate and store email verification token
     */
    public function generateEmailVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32)); // 64 character token
        
        $this->update([
            'email_verification_token' => \Illuminate\Support\Facades\Hash::make($token),
            'email_verification_sent_at' => now(),
        ]);

        return $token;
    }

    /**
     * Verify email using token
     */
    public function verifyEmail(string $token): bool
    {
        if ($this->hasVerifiedEmail()) {
            return true; // Already verified
        }

        if (!$this->email_verification_token) {
            return false; // No token stored
        }

        // Check if token is expired (within 24 hours)
        if ($this->email_verification_sent_at) {
            $expiresAt = $this->email_verification_sent_at->copy()->addHours(24);
            if ($expiresAt->isPast()) {
                return false; // Token expired
            }
        }

        // Verify the token
        if (!\Illuminate\Support\Facades\Hash::check($token, $this->email_verification_token)) {
            return false; // Invalid token
        }

        // Mark email as verified and clear token
        $this->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_sent_at' => null,
        ]);

        return true;
    }

    /**
     * Check if verification token is expired
     */
    public function isVerificationTokenExpired(): bool
    {
        if (!$this->email_verification_sent_at) {
            return true;
        }

        $expiresAt = $this->email_verification_sent_at->copy()->addHours(24);
        return $expiresAt->isPast();
    }
}
