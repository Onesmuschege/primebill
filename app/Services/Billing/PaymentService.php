<?php

namespace App\Services\Billing;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ClientAccount;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class PaymentService
{
    public function getAllPayments(Request $request)
    {
        $query = Payment::with('client', 'invoice');

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('method')) {
            $query->where('method', $request->method);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 15);
    }

    public function recordPayment(array $data, $userId): Payment
    {
        $data['status']      = 'completed';
        $data['recorded_by'] = $userId;

        $payment = Payment::create($data);

        // Mark invoice as paid if invoice_id provided
        if (!empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice && $data['amount'] >= $invoice->total) {
                $invoice->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                // Extend client account expiry
                $this->extendClientAccount(
                    $data['client_id'],
                    $invoice
                );
            }
        }

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'recorded payment',
            'model'      => 'Payment',
            'model_id'   => $payment->id,
            'new_values' => $data,
        ]);

        return $payment->load('client', 'invoice');
    }

    public function deletePayment(Payment $payment, $userId): void
    {
        // Reverse invoice status if payment is deleted
        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice && $invoice->status === 'paid') {
                $invoice->update([
                    'status'  => 'unpaid',
                    'paid_at' => null,
                ]);
            }
        }

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted payment',
            'model'      => 'Payment',
            'model_id'   => $payment->id,
            'old_values' => $payment->toArray(),
        ]);

        $payment->delete();
    }

    private function extendClientAccount($clientId, Invoice $invoice): void
    {
        $account = ClientAccount::where('client_id', $clientId)
                                ->where('status', '!=', 'inactive')
                                ->first();

        if (!$account || !$account->plan) return;

        $validityDays = $account->plan->validity_days ?? 30;
        $currentExpiry = $account->expiry_date ?? now();
        $newExpiry = $currentExpiry < now()
            ? now()->addDays($validityDays)
            : $currentExpiry->addDays($validityDays);

        $account->update([
            'status'      => 'active',
            'expiry_date' => $newExpiry,
        ]);
    }

    public function getDailySummary(): array
    {
        $today = now()->toDateString();

        return [
            'total'  => Payment::whereDate('created_at', $today)
                               ->where('status', 'completed')
                               ->sum('amount'),
            'count'  => Payment::whereDate('created_at', $today)
                               ->where('status', 'completed')
                               ->count(),
            'mpesa'  => Payment::whereDate('created_at', $today)
                               ->where('method', 'mpesa')
                               ->where('status', 'completed')
                               ->sum('amount'),
            'cash'   => Payment::whereDate('created_at', $today)
                               ->where('method', 'cash')
                               ->where('status', 'completed')
                               ->sum('amount'),
        ];
    }
}
