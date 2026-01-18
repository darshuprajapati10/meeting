<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'push_notifications_enabled',
        'email_notifications_enabled',
        'email_meeting_reminders',
        'email_meeting_updates',
        'email_meeting_cancellations',
        'meeting_reminders',
        'notification_sound',
        'notification_badge',
    ];

    protected $casts = [
        'push_notifications_enabled' => 'boolean',
        'email_notifications_enabled' => 'boolean',
        'email_meeting_reminders' => 'boolean',
        'email_meeting_updates' => 'boolean',
        'email_meeting_cancellations' => 'boolean',
        'meeting_reminders' => 'array',
        'notification_sound' => 'boolean',
        'notification_badge' => 'boolean',
    ];

    protected $attributes = [
        'push_notifications_enabled' => true,
        'email_notifications_enabled' => true,
        'email_meeting_reminders' => true,
        // Deprecated fields - default to false
        // These fields are kept for backward compatibility but should not be used
        // Frontend no longer sends these fields, but we maintain them in the database
        'email_meeting_updates' => false,
        'email_meeting_cancellations' => false,
        'meeting_reminders' => '[15]',
        'notification_sound' => true,
        'notification_badge' => true,
    ];

    /**
     * Get the meeting reminders attribute, ensuring it returns an array with default [15] if null.
     */
    public function getMeetingRemindersAttribute($value)
    {
        if ($value === null) {
            return [15];
        }
        
        // If it's already an array (from cast), return it
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a JSON string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : [15];
        }
        
        return [15];
    }

    /**
     * Get the user that owns the notification preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
