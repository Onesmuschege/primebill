<?php

namespace App\Http\Requests\Router;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'sometimes|string|max:255',
            'ip_address' => 'sometimes|ip',
            'username'   => 'sometimes|string',
            'password'   => 'sometimes|string',
            'port'       => 'sometimes|nullable|integer',
            'type'       => 'sometimes|in:mikrotik,other',
            'location'   => 'sometimes|nullable|string',
        ];
    }
}
