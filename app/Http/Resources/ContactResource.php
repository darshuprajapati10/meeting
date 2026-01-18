<?php

namespace App\Http\Resources;

use App\Models\ContactFavourite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isFavourite = 0;
        
        // Check if contact is favourited by the authenticated user
        if ($request->user()) {
            $favourite = ContactFavourite::where('contact_id', $this->id)
                ->where('user_id', $request->user()->id)
                ->where('is_favourite', true)
                ->first();
            
            $isFavourite = $favourite ? 1 : 0;
        }

        return [
            'id'              => $this->id,
            'organization_id' => $this->organization_id,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'company'         => $this->company,
            'job_title'       => $this->job_title,
            'referrer_id'     => $this->referrer_id,
            'groups'          => $this->groups ?? [],
            'address'         => $this->address,
            'notes'           => $this->notes,
            'avatar_color'    => $this->avatar_color ?? 'bg-teal',
            'created_by'      => $this->created_by,
            'is_favourite'    => $isFavourite,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}


