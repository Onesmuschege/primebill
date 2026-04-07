<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\ClientAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Router;
use App\Models\NetworkTraffic;
use App\Models\RadiusSession;
use App\Models\SmsLog;

class DashboardService
{
    public function getStats(): array
    {
        $today = now()->toDateString();

        return [
            'income_today'    => $this->getIncomeToday($today),
            'income_month'    => $this->getIncomeThisMonth(),
            'active_users'    => $this->getActiveUsers(),
            'total_users'     => Client::count(),
            'tickets'         => $this->getTicketStats(),
            'account_status'  => $this->getAccountStatus(),
            'hotspot_status'  => $this->getHotspotStatus(),
            'sms_stats'       => $this->getSmsStats($today),

            // ✅ NEW ADDED STATS
            'overdue_invoices' => [
                'count'  => Invoice::where('status', 'overdue')->count(),
                'amount' => Invoice::where('status', 'overdue')->sum('amount'),
            ],

            'routers' => [
                'total'   => Router::count(),
                'online'  => Router::where('status', 'online')->count(),
                'offline' => Router::where('status', 'offline')->count(),
            ],

            'account_summary' => [
                'online'    => ClientAccount::where('status', 'active')->count(),
                'offline'   => ClientAccount::where('status', 'inactive')->count(),
                'overdue'   => ClientAccount::where('status', 'overdue')->count(),
                'suspended' => ClientAccount::where('status', 'suspended')->count(),
            ],
        ];
    }

    private function getIncomeToday(string $today): array
    {
        return [
            'amount' => Payment::whereDate('created_at', $today)
                               ->where('status', 'completed')
                               ->sum('amount'),
            'count'  => Payment::whereDate('created_at', $today)
                               ->where('status', 'completed')
                               ->count(),
        ];
    }

    private function getIncomeThisMonth(): array
    {
        return [
            'amount' => Payment::whereYear('created_at', now()->year)
                               ->whereMonth('created_at', now()->month)
                               ->where('status', 'completed')
                               ->sum('amount'),
            'count'  => Payment::whereYear('created_at', now()->year)
                               ->whereMonth('created_at', now()->month)
                               ->where('status', 'completed')
                               ->count(),
        ];
    }

    private function getActiveUsers(): int
    {
        return RadiusSession::where('status', 'active')->count();
    }

    private function getTicketStats(): array
    {
        return [
            'open'    => Ticket::where('status', 'open')->count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'solved'  => Ticket::where('status', 'solved')->count(),
            'total'   => Ticket::count(),
        ];
    }

    private function getAccountStatus(): array
    {
        return [
            'online'   => RadiusSession::where('status', 'active')->count(),
            'offline'  => Client::whereDoesntHave('accounts', function ($q) {
                $q->whereHas('radiusSessions', function ($q) {
                    $q->where('status', 'active');
                });
            })->count(),
            'overdue'  => Invoice::where('status', 'overdue')
                                 ->distinct('client_id')
                                 ->count('client_id'),
        ];
    }

    private function getHotspotStatus(): array
    {
        return [
            'online'  => RadiusSession::where('status', 'active')->count(),
            'offline' => 0,
            'total'   => Client::whereHas('accounts', function ($q) {
                $q->where('type', 'prepaid');
            })->count(),
        ];
    }

    private function getSmsStats(string $today): array
    {
        return [
            'sent_today' => SmsLog::whereDate('created_at', $today)
                                  ->where('status', 'sent')
                                  ->count(),
            'failed'     => SmsLog::whereDate('created_at', $today)
                                  ->where('status', 'failed')
                                  ->count(),
        ];
    }

    public function getTrafficData(string $period = 'day'): array
    {
        $routers = Router::where('status', 'online')->get();
        $data    = [];

        foreach ($routers as $router) {
            $query = NetworkTraffic::where('router_id', $router->id);

            match ($period) {
                'day'   => $query->where('recorded_at', '>=', now()->subDay()),
                'week'  => $query->where('recorded_at', '>=', now()->subWeek()),
                'month' => $query->where('recorded_at', '>=', now()->subMonth()),
                default => $query->where('recorded_at', '>=', now()->subDay()),
            };

            $traffic = $query->orderBy('recorded_at', 'asc')->get();

            $data[] = [
                'router'  => $router->name,
                'traffic' => $traffic->map(fn($t) => [
                    'time'     => $t->recorded_at,
                    'tx_mbps'  => round($t->tx_bytes / 1048576, 2),
                    'rx_mbps'  => round($t->rx_bytes / 1048576, 2),
                ]),
            ];
        }

        return $data;
    }

    public function getTopDownloaders(int $limit = 10): array
    {
        return RadiusSession::with('account.client')
            ->where('status', 'active')
            ->orderBy('bytes_out', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($s) => [
                'username'   => $s->username,
                'client'     => trim(($s->account?->client?->first_name ?? '') . ' ' . ($s->account?->client?->last_name ?? '')),
                'downloaded' => round($s->bytes_out / 1073741824, 2) . ' GB',
                'uploaded'   => round($s->bytes_in / 1073741824, 2) . ' GB',
            ])
            ->toArray();
    }

    public function getIncomeAnalytics(string $from, string $to, string $groupBy = 'day'): array
    {
        $payments = Payment::whereBetween('created_at', [$from, $to])
                           ->where('status', 'completed')
                           ->get();

        $grouped = match ($groupBy) {
            'month' => $payments->groupBy(fn($p) => $p->created_at->format('Y-m')),
            'year'  => $payments->groupBy(fn($p) => $p->created_at->format('Y')),
            default => $payments->groupBy(fn($p) => $p->created_at->format('Y-m-d')),
        };

        return $grouped->map(fn($group, $key) => [
            'date'   => $key,
            'total'  => $group->sum('amount'),
            'count'  => $group->count(),
            'mpesa'  => $group->where('method', 'mpesa')->sum('amount'),
            'cash'   => $group->where('method', 'cash')->sum('amount'),
        ])->values()->toArray();
    }
}
