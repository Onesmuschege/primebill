<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Mpesa\MpesaService;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    protected MpesaService $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    // POST /api/mpesa/stk-push
    public function stkPush(Request $request)
    {
        $request->validate([
            'phone'      => 'required|string',
            'amount'     => 'required|numeric|min:1',
            'invoice_id' => 'required|exists:invoices,id',
            'account_ref' => 'required|string',
        ]);

        $response = $this->mpesaService->stkPush(
            $request->phone,
            $request->amount,
            $request->invoice_id,
            $request->account_ref
        );

        return response()->json([
            'success' => true,
            'message' => 'STK push sent successfully',
            'data'    => $response,
        ]);
    }

    // POST /api/mpesa/stk-callback (no auth — Safaricom callback)
    public function stkCallback(Request $request)
    {
        $payload = $request->all();
        $this->mpesaService->handleStkCallback($payload);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    // POST /api/mpesa/c2b-validation (no auth)
    public function c2bValidation(Request $request)
    {
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    // POST /api/mpesa/c2b-confirmation (no auth)
    public function c2bConfirmation(Request $request)
    {
        $this->mpesaService->handleC2BConfirmation($request->all());

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success',
        ]);
    }
}
