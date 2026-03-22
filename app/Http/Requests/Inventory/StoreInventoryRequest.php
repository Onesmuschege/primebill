<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'category'          => 'required|string|max:255',
            'quantity'          => 'required|integer|min:0',
            'unit_cost'         => 'required|numeric|min:0',
            'serial_number'     => 'nullable|string',
            'status'            => 'nullable|in:in_stock,assigned,faulty,lost',
            'low_stock_alert'   => 'nullable|integer|min:0',
        ];
    }
}
