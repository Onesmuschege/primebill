<?php

namespace App\Http\Requests\Router;

use Illuminate\Foundation\Http\FormRequest;

class StoreRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'username'   => 'required|string',
            'password'   => 'required|string',
            'port'       => 'nullable|integer',
            'type'       => 'nullable|in:mikrotik,other',
            'location'   => 'nullable|string',
        ];
    }
}
