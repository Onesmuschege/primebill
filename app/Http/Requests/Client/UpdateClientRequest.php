<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client');

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'email'      => 'sometimes|nullable|email|unique:clients,email,' . $clientId,
            'phone'      => 'sometimes|string|unique:clients,phone,' . $clientId,
            'id_number'  => 'sometimes|nullable|string|unique:clients,id_number,' . $clientId,
            'address'    => 'sometimes|nullable|string',
            'county'     => 'sometimes|nullable|string',
            'town'       => 'sometimes|nullable|string',
            'gps_lat'    => 'sometimes|nullable|numeric',
            'gps_lng'    => 'sometimes|nullable|numeric',
            'status'     => 'sometimes|in:active,inactive,suspended,disabled',
        ];
    }
}
