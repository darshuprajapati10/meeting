<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get user data from users table (login data)
        // Safely get user - check if relationship is loaded
        $user = null;
        if ($this->relationLoaded('user')) {
            $user = $this->getRelation('user');
        }
        
        // Only access user properties if user exists and is not MissingValue
        $userName = null;
        $userEmail = null;
        $userCreatedAt = null;
        
        if ($user && !($user instanceof \Illuminate\Http\Resources\MissingValue)) {
            $userName = $user->name ?? null;
            $userEmail = $user->email ?? null;
            $userCreatedAt = $user->created_at ?? null;
        }
        
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'user_id' => (int) $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'bio' => $this->bio,
            'email_address' => $this->email_address,
            'address' => $this->address,
            'company' => $this->company,
            'phone' => $this->phone,
            'job_title' => $this->job_title,
            'department' => $this->department,
            'timezone' => $this->timezone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Login data from users table
            'name' => $userName,                    // Name from login
            'email' => $userEmail,                  // Email from login
            'login_created_at' => $userCreatedAt,  // Created date from login
        ];
    }
}
