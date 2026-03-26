<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\ClientAccount;
use App\Services\Sms\SmsService;
use Illuminate\Console\Command;

class SuspendOverdueAccounts extends Command
{
    protected $signature   = 'billing:suspend-overdue';
    protected $description = 'Suspend accounts with overdue invoices';

    public function handle(SmsService $smsService): void
    {
        $overdueInvoices = Invoice::where('status', 'overdue')
                                  ->where('due_date', '<', now()->subDays(3))
                                  ->with('client.accounts')
                                  ->get();

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            $invoice->client->accounts()
                            ->where('status', 'active')
                            ->update(['status' => 'suspended']);

            $invoice->client->update(['status' => 'suspended']);

            $smsService->send(
                $invoice->client->phone,
                "Dear {$invoice->client->first_name}, your account has been suspended due to overdue invoice of KES {$invoice->total}. Pay to reactivate.",
                $invoice->client_id
            );

            $count++;
        }

        $this->info("Suspended {$count} accounts.");
    }
}
