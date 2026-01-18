<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionAddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'price_monthly',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the organization add-ons for this add-on.
     */
    public function organizationAddOns()
    {
        return $this->hasMany(OrganizationAddOn::class);
    }
}
