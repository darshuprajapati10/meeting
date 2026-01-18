<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // If "Same as shipping address" is checked, copy shipping to billing
        if ($this->has('same_as_shipping') && $this->input('same_as_shipping')) {
            $this->merge([
                'billing_address' => $this->input('shipping_address'),
                'billing_city' => $this->input('shipping_city'),
                'billing_zip' => $this->input('shipping_zip'),
                'billing_phone' => $this->input('shipping_phone'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:organizations,id'],
            'type' => ['required', 'in:business,individual'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'gst_status' => ['nullable', 'in:registered,unregistered'],
            'gst_in' => ['nullable', 'string', 'max:15'],
            'place_of_supply' => ['nullable', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_zip' => ['required', 'string', 'max:20'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'same_as_shipping' => ['nullable', 'boolean'],
            'billing_address' => ['required', 'string', 'max:500'],
            'billing_city' => ['required', 'string', 'max:255'],
            'billing_zip' => ['required', 'string', 'max:20'],
            'billing_phone' => ['required', 'string', 'max:20'],
        ];
    }
}

