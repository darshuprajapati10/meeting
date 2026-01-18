<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'job_title',
        'referrer_id',
        'groups',
        'address',
        'notes',
        'avatar_color',
        'created_by',
    ];

    protected $casts = [
        'groups' => 'array',
    ];

    // Allowed avatar colors
    const AVATAR_COLORS = [
        'bg-teal',
        'bg-lavender',
        'bg-navy',
        'bg-purple',
        'bg-green',
        'bg-orange',
        'bg-pink',
        'bg-blue',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function referrer()
    {
        return $this->belongsTo(Contact::class, 'referrer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


