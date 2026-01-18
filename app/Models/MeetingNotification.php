<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'minutes',
        'unit',
        'trigger',
        'is_enabled',
    ];

    protected $casts = [
        'minutes' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}

