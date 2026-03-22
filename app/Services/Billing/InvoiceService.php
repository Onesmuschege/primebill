<?php

namespace App\Services\Billing;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class InvoiceService
{
    public function getAllInvoices(Request $request)
    {
        $query = Invoice::with('client');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 15);
    }

    public function createInvoice(array $data, $userId): Invoice
    {
        $data['invoice_number'] = $this->generateInvoiceNumber();
        $data['tax']            = $data['tax'] ?? 0;
        $data['total']          = $data['amount'] + $data['tax'];
        $data['created_by']     = $userId;
        $data['status']         = $data['status'] ?? 'unpaid';

        $invoice = Invoice::create($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'created invoice',
            'model'      => 'Invoice',
            'model_id'   => $invoice->id,
            'new_values' => $data,
        ]);

        return $invoice->load('client');
    }

    public function updateInvoice(Invoice $invoice, array $data, $userId): Invoice
    {
        $oldValues = $invoice->toArray();

        if (isset($data['amount']) || isset($data['tax'])) {
            $amount       = $data['amount'] ?? $invoice->amount;
            $tax          = $data['tax'] ?? $invoice->tax;
            $data['total'] = $amount + $tax;
        }

        $invoice->update($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'updated invoice',
            'model'      => 'Invoice',
            'model_id'   => $invoice->id,
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $invoice;
    }

    public function deleteInvoice(Invoice $invoice, $userId): void
    {
        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted invoice',
            'model'      => 'Invoice',
            'model_id'   => $invoice->id,
            'old_values' => $invoice->toArray(),
        ]);

        $invoice->delete();
    }

    public function markAsPaid(Invoice $invoice, $userId): Invoice
    {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        SystemLog::create([
            'user_id'  => $userId,
            'action'   => 'marked invoice as paid',
            'model'    => 'Invoice',
            'model_id' => $invoice->id,
        ]);

        return $invoice;
    }

    public function markOverdueInvoices(): int
    {
        $count = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return $count;
    }

    public function generateInvoiceNumber(): string
    {
        $prefix  = 'INV';
        $year    = date('Y');
        $last    = Invoice::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();
        $number  = $last ? (intval(substr($last->invoice_number, -6)) + 1) : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function bulkGenerate(array $clientIds, $userId): int
    {
        $count = 0;

        foreach ($clientIds as $clientId) {
            $client = Client::with('accounts.plan')->find($clientId);

            if (!$client) continue;

            foreach ($client->accounts as $account) {
                if (!$account->plan) continue;

                $this->createInvoice([
                    'client_id' => $clientId,
                    'amount'    => $account->plan->price,
                    'due_date'  => now()->addDays(7)->toDateString(),
                    'status'    => 'unpaid',
                ], $userId);

                $count++;
            }
        }

        return $count;
    }
}
