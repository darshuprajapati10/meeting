<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name ?? null,
            'last_name' => $this->last_name ?? null,
            'organization_id' => $this->organization_id ?? null,
            'financial_year_id' => $this->financial_year_id ?? null,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'is_email_verified' => $this->hasVerifiedEmail(),
            'email_verified_code' => $this->email_verified_code ?? null,
            '2fa_code' => $this->{'2fa_code'} ?? null,
            'is_platform_admin' => $this->is_platform_admin ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'mobile' => $this->mobile ?? null,
        ];
    }
}
