<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string|max:255',
            'type'           => 'sometimes|in:hotspot,pppoe,static',
            'speed_up'       => 'sometimes|integer|min:1',
            'speed_down'     => 'sometimes|integer|min:1',
            'burst_up'       => 'sometimes|nullable|integer',
            'burst_down'     => 'sometimes|nullable|integer',
            'fup_limit'      => 'sometimes|nullable|integer',
            'fup_speed_up'   => 'sometimes|nullable|integer',
            'fup_speed_down' => 'sometimes|nullable|integer',
            'validity_days'  => 'sometimes|integer|min:1',
            'price'          => 'sometimes|numeric|min:0',
            'router_id'      => 'sometimes|nullable|exists:routers,id',
            'is_active'      => 'sometimes|boolean',
        ];
    }
}
