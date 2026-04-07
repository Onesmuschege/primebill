<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Console\Command;

class ReconcileMpesaPayments extends Command
{
    protected $signature = 'payments:reconcile-mpesa';
    protected $description = 'Reconcile M-Pesa payments and invoice paid states';

    public function handle(): void
    {
        $invoiceIds = Payment::query()
            ->where('method', 'mpesa')
            ->whereNotNull('invoice_id')
            ->where('status', 'completed')
            ->pluck('invoice_id')
            ->unique()
            ->values();

        $fixed = 0;
        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                continue;
            }

            $paidAmount = Payment::where('invoice_id', $invoice->id)
                ->where('status', 'completed')
                ->sum('amount');

            if ($paidAmount >= $invoice->total && $invoice->status !== 'paid') {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                $fixed++;
            }
        }

        $this->info("Reconciliation complete. Updated {$fixed} invoices.");
    }
}
