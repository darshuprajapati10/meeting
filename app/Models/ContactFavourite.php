<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactFavourite extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'contact_id',
        'is_favourite',
    ];

    protected $casts = [
        'is_favourite' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}

