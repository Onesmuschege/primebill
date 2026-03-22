<?php

namespace App\Http\Requests\Expenditure;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenditureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'    => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric|min:1',
            'date'        => 'required|date',
        ];
    }
}
