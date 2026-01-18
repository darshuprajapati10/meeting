<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'address',
        'status',
        'type',
        'gst_status',
        'gst_in',
        'place_of_supply',
        'shipping_address',
        'shipping_city',
        'shipping_zip',
        'shipping_phone',
        'billing_address',
        'billing_city',
        'billing_zip',
        'billing_phone',
        'razorpay_customer_id',
    ];

    /**
     * Get the users that belong to this organization.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the subscription for this organization.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trial'])
            ->latest();
    }

    /**
     * Get all subscriptions for this organization.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active add-ons for this organization.
     */
    public function addOns()
    {
        return $this->hasMany(OrganizationAddOn::class)->where('status', 'active');
    }

    /**
     * Get all add-ons for this organization.
     */
    public function organizationAddOns()
    {
        return $this->hasMany(OrganizationAddOn::class);
    }

    /**
     * Get the usage tracking records for this organization.
     */
    public function usageTracking()
    {
        return $this->hasMany(UsageTracking::class);
    }

    /**
     * Get the transactions for this organization.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the survey attachments for this organization.
     */
    public function surveyAttachments()
    {
        return $this->hasMany(SurveyAttachment::class);
    }

    /**
     * Get the meetings for this organization.
     */
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Get the contacts for this organization.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the surveys for this organization.
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }
}
