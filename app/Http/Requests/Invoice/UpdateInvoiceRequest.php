<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'   => 'sometimes|numeric|min:0',
            'tax'      => 'sometimes|nullable|numeric|min:0',
            'due_date' => 'sometimes|date',
            'notes'    => 'sometimes|nullable|string',
            'status'   => 'sometimes|in:draft,unpaid,paid,overdue,cancelled',
        ];
    }
}
