<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Payment;
use App\Services\Billing\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // GET /api/payments
    public function index(Request $request)
    {
        $payments = $this->paymentService->getAllPayments($request);

        return response()->json([
            'success' => true,
            'data'    => $payments,
        ]);
    }

    // POST /api/payments
    public function store(StorePaymentRequest $request)
    {
        $payment = $this->paymentService->recordPayment(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data'    => $payment,
        ], 201);
    }

    // GET /api/payments/{id}
    public function show(Payment $payment)
    {
        $payment->load('client', 'invoice');

        return response()->json([
            'success' => true,
            'data'    => $payment,
        ]);
    }

    // DELETE /api/payments/{id}
    public function destroy(Request $request, Payment $payment)
    {
        $this->paymentService->deletePayment(
            $payment,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }

    // GET /api/payments/summary
    public function summary()
    {
        $summary = $this->paymentService->getDailySummary();

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }
}
