<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:hotspot,pppoe,static',
            'speed_up'       => 'required|integer|min:1',
            'speed_down'     => 'required|integer|min:1',
            'burst_up'       => 'nullable|integer',
            'burst_down'     => 'nullable|integer',
            'fup_limit'      => 'nullable|integer',
            'fup_speed_up'   => 'nullable|integer',
            'fup_speed_down' => 'nullable|integer',
            'validity_days'  => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'router_id'      => 'nullable|exists:routers,id',
            'is_active'      => 'nullable|boolean',
        ];
    }
}
