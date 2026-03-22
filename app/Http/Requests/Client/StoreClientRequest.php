<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'nullable|email|unique:clients,email',
            'phone'      => 'required|string|unique:clients,phone',
            'id_number'  => 'nullable|string|unique:clients,id_number',
            'address'    => 'nullable|string',
            'county'     => 'nullable|string',
            'town'       => 'nullable|string',
            'gps_lat'    => 'nullable|numeric',
            'gps_lng'    => 'nullable|numeric',
            'status'     => 'nullable|in:active,inactive,suspended,disabled',
        ];
    }
}
