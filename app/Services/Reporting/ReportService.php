<?php

namespace App\Services\Reporting;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\SmsLog;
use App\Models\NetworkTraffic;
use App\Models\RadiusSession;
use App\Models\Expenditure;
use App\Models\InventoryItem;
use App\Models\FupLog;

class ReportService
{
    public function getIncomeReport(string $from, string $to): array
    {
        $payments = Payment::whereBetween('created_at', [$from, $to])
                           ->where('status', 'completed')
                           ->with('client')
                           ->get();

        return [
            'total'       => $payments->sum('amount'),
            'count'       => $payments->count(),
            'by_method'   => [
                'mpesa' => $payments->where('method', 'mpesa')->sum('amount'),
                'cash'  => $payments->where('method', 'cash')->sum('amount'),
                'bank'  => $payments->where('method', 'bank')->sum('amount'),
            ],
            'daily'       => $payments->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
                                      ->map(fn($g) => $g->sum('amount'))
                                      ->toArray(),
            'payments'    => $payments,
        ];
    }

    public function getClientReport(string $from, string $to): array
    {
        $newClients = Client::whereBetween('created_at', [$from, $to])->count();
        $total      = Client::count();
        $active     = Client::where('status', 'active')->count();
        $suspended  = Client::where('status', 'suspended')->count();

        return [
            'new_clients' => $newClients,
            'total'       => $total,
            'active'      => $active,
            'suspended'   => $suspended,
            'by_status'   => Client::selectRaw('status, count(*) as count')
                                   ->groupBy('status')
                                   ->pluck('count', 'status')
                                   ->toArray(),
        ];
    }

    public function getInvoiceReport(string $from, string $to): array
    {
        $invoices = Invoice::whereBetween('created_at', [$from, $to])->get();

        return [
            'total'     => $invoices->count(),
            'paid'      => $invoices->where('status', 'paid')->count(),
            'unpaid'    => $invoices->where('status', 'unpaid')->count(),
            'overdue'   => $invoices->where('status', 'overdue')->count(),
            'cancelled' => $invoices->where('status', 'cancelled')->count(),
            'total_value'    => $invoices->sum('total'),
            'collected'      => $invoices->where('status', 'paid')->sum('total'),
            'outstanding'    => $invoices->whereIn('status', ['unpaid', 'overdue'])->sum('total'),
        ];
    }

    public function getSmsReport(string $from, string $to): array
    {
        $logs = SmsLog::whereBetween('created_at', [$from, $to])->get();

        return [
            'total'     => $logs->count(),
            'sent'      => $logs->where('status', 'sent')->count(),
            'failed'    => $logs->where('status', 'failed')->count(),
            'delivered' => $logs->where('status', 'delivered')->count(),
            'by_gateway'=> $logs->groupBy('gateway')
                                ->map(fn($g) => $g->count())
                                ->toArray(),
        ];
    }

    public function getNetworkReport(string $from, string $to): array
    {
        $sessions = RadiusSession::whereBetween('created_at', [$from, $to])->get();

        return [
            'total_sessions'  => $sessions->count(),
            'total_download'  => round($sessions->sum('bytes_out') / 1073741824, 2) . ' GB',
            'total_upload'    => round($sessions->sum('bytes_in') / 1073741824, 2) . ' GB',
            'top_downloaders' => $sessions->sortByDesc('bytes_out')
                                          ->take(10)
                                          ->map(fn($s) => [
                                              'username'   => $s->username,
                                              'downloaded' => round($s->bytes_out / 1073741824, 2) . ' GB',
                                          ])->values()->toArray(),
        ];
    }

    public function getInventoryReport(): array
    {
        return [
            'total_items'  => InventoryItem::count(),
            'total_value'  => InventoryItem::selectRaw('SUM(quantity * unit_cost) as value')->value('value'),
            'by_status'    => InventoryItem::selectRaw('status, count(*) as count')
                                           ->groupBy('status')
                                           ->pluck('count', 'status')
                                           ->toArray(),
            'by_category'  => InventoryItem::selectRaw('category, count(*) as count, SUM(quantity * unit_cost) as value')
                                           ->groupBy('category')
                                           ->get()
                                           ->toArray(),
            'low_stock'    => InventoryItem::whereColumn('quantity', '<=', 'low_stock_alert')->count(),
        ];
    }

    public function getExpenditureReport(string $from, string $to): array
    {
        $expenditures = Expenditure::whereBetween('date', [$from, $to])->get();

        return [
            'total'       => $expenditures->sum('amount'),
            'count'       => $expenditures->count(),
            'by_category' => $expenditures->groupBy('category')
                                          ->map(fn($g) => $g->sum('amount'))
                                          ->toArray(),
            'items'       => $expenditures,
        ];
    }
}
