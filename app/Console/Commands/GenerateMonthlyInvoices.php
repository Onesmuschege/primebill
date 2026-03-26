<?php

namespace App\Console\Commands;

use App\Models\ClientAccount;
use App\Services\Billing\InvoiceService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature   = 'billing:generate-invoices';
    protected $description = 'Generate monthly invoices for all active accounts';

    public function handle(InvoiceService $invoiceService): void
    {
        $accounts = ClientAccount::with('client', 'plan')
                                 ->where('status', 'active')
                                 ->whereHas('client')
                                 ->whereHas('plan')
                                 ->get();

        $count = 0;
        foreach ($accounts as $account) {
            $invoiceService->createInvoice([
                'client_id' => $account->client_id,
                'amount'    => $account->plan->price,
                'due_date'  => now()->addDays(7)->toDateString(),
                'status'    => 'unpaid',
            ], 1);
            $count++;
        }

        $this->info("Generated {$count} invoices.");
    }
}
