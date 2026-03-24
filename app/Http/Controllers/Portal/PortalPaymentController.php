<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Mpesa\MpesaService;
use Illuminate\Http\Request;

class PortalPaymentController extends Controller
{
    protected MpesaService $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    // GET /api/portal/payments
    public function index(Request $request)
    {
        $payments = $request->user()
                            ->payments()
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $payments,
        ]);
    }

    // POST /api/portal/payments/stk-push
    public function stkPush(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'phone'      => 'required|string',
        ]);

        $invoice = Invoice::find($request->invoice_id);

        if ($invoice->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $response = $this->mpesaService->stkPush(
            $request->phone,
            $invoice->total,
            $invoice->id,
            'INV-' . $invoice->id
        );

        return response()->json([
            'success' => true,
            'message' => 'STK push sent to your phone',
            'data'    => $response,
        ]);
    }
}