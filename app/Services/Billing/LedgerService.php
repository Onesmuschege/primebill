<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Payment;

class LedgerService
{
    public function postInvoiceDebit(Invoice $invoice, ?int $userId = null): void
    {
        LedgerEntry::create([
            'client_id'   => $invoice->client_id,
            'invoice_id'  => $invoice->id,
            'entry_type'  => 'invoice_debit',
            'amount'      => $invoice->total,
            'currency'    => 'KES',
            'description' => 'Invoice issued',
            'meta'        => ['invoice_number' => $invoice->invoice_number],
            'recorded_by' => $userId,
        ]);
    }

    public function postPaymentCredit(Payment $payment, ?int $userId = null): void
    {
        LedgerEntry::create([
            'client_id'   => $payment->client_id,
            'invoice_id'  => $payment->invoice_id,
            'payment_id'  => $payment->id,
            'entry_type'  => 'payment_credit',
            'amount'      => $payment->amount,
            'currency'    => 'KES',
            'description' => 'Payment received',
            'meta'        => [
                'method'     => $payment->method,
                'reference'  => $payment->reference,
                'mpesa_code' => $payment->mpesa_code,
            ],
            'recorded_by' => $userId,
        ]);
    }

    public function postPaymentReversal(Payment $payment, ?int $userId = null, ?string $reason = null): void
    {
        LedgerEntry::create([
            'client_id'   => $payment->client_id,
            'invoice_id'  => $payment->invoice_id,
            'payment_id'  => $payment->id,
            'entry_type'  => 'payment_reversal',
            'amount'      => $payment->amount,
            'currency'    => 'KES',
            'description' => 'Payment reversed',
            'meta'        => [
                'method'    => $payment->method,
                'reference' => $payment->reference,
                'reason'    => $reason ?: 'Payment deleted',
            ],
            'recorded_by' => $userId,
        ]);
    }

    public function postInvoiceReversal(Invoice $invoice, ?int $userId = null): void
    {
        LedgerEntry::create([
            'client_id'   => $invoice->client_id,
            'invoice_id'  => $invoice->id,
            'entry_type'  => 'invoice_reversal',
            'amount'      => $invoice->total,
            'currency'    => 'KES',
            'description' => 'Invoice deleted',
            'meta'        => ['invoice_number' => $invoice->invoice_number],
            'recorded_by' => $userId,
        ]);
    }
}