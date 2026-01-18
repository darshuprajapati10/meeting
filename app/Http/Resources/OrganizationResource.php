<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gst_status' => $this->gst_status,
            'gst_in' => $this->gst_in,
            'place_of_supply' => $this->place_of_supply,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_zip' => $this->shipping_zip,
            'shipping_phone' => $this->shipping_phone,
            'billing_address' => $this->billing_address,
            'billing_city' => $this->billing_city,
            'billing_zip' => $this->billing_zip,
            'billing_phone' => $this->billing_phone,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

