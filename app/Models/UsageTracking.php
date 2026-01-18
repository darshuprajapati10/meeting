<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'metric',
        'count',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'count' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * Get the organization that owns this usage tracking.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
