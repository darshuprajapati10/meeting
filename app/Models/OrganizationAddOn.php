<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationAddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'subscription_add_on_id',
        'quantity',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the organization that owns this add-on.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the subscription add-on definition.
     */
    public function addOn(): BelongsTo
    {
        return $this->belongsTo(SubscriptionAddOn::class, 'subscription_add_on_id');
    }
}
