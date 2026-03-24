<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClientAccount;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PortalDashboardController extends Controller
{
    // GET /api/portal/dashboard
    public function index(Request $request)
    {
        $client  = $request->user();
        $account = ClientAccount::with('plan')
                                ->where('client_id', $client->id)
                                ->first();

        $unpaidInvoices = Invoice::where('client_id', $client->id)
                                 ->whereIn('status', ['unpaid', 'overdue'])
                                 ->sum('total');

        $lastPayment = $client->payments()
                              ->latest()
                              ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'client'          => [
                    'id'    => $client->id,
                    'name'  => $client->first_name . ' ' . $client->last_name,
                    'phone' => $client->phone,
                    'email' => $client->email,
                ],
                'account'         => $account,
                'unpaid_balance'  => $unpaidInvoices,
                'last_payment'    => $lastPayment,
                'days_remaining'  => $account?->expiry_date
                    ? max(0, now()->diffInDays($account->expiry_date, false))
                    : 0,
            ],
        ]);
    }
}