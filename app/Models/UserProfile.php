<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class UserProfile extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'first_name',
        'last_name',
        'bio',
        'email_address',
        'address',
        'company',
        'phone',
        'job_title',
        'department',
        'timezone',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($userProfile) {
            // Set user_id from authenticated user if not provided
            if (empty($userProfile->user_id) && Auth::check()) {
                $userProfile->user_id = Auth::id();
            }

            // Set organization_id from authenticated user's organization if not provided
            if (empty($userProfile->organization_id) && Auth::check()) {
                $user = Auth::user();
                $organization = $user->organizations()->first();
                if ($organization) {
                    $userProfile->organization_id = $organization->id;
                }
            }
        });

        static::updating(function ($userProfile) {
            // During update, only set if the field is being changed and is empty
            // Don't override existing values
            if ($userProfile->isDirty('user_id') && empty($userProfile->user_id) && Auth::check()) {
                $userProfile->user_id = Auth::id();
            }

            if ($userProfile->isDirty('organization_id') && empty($userProfile->organization_id) && Auth::check()) {
                $user = Auth::user();
                $organization = $user->organizations()->first();
                if ($organization) {
                    $userProfile->organization_id = $organization->id;
                }
            }
        });
    }

    /**
     * Get the organization that owns the user profile.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
