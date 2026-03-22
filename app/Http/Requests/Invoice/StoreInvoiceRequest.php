<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'amount'    => 'required|numeric|min:0',
            'tax'       => 'nullable|numeric|min:0',
            'due_date'  => 'required|date',
            'notes'     => 'nullable|string',
            'status'    => 'nullable|in:draft,unpaid,paid,overdue,cancelled',
        ];
    }
}
