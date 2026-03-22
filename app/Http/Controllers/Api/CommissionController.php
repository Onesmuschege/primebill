<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesCommission;
use App\Services\Finance\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    // GET /api/commissions
    public function index(Request $request)
    {
        $commissions = $this->commissionService->getAllCommissions($request);

        return response()->json([
            'success' => true,
            'data'    => $commissions,
        ]);
    }

    // GET /api/commissions/summary
    public function summary()
    {
        $summary = $this->commissionService->getSummary();

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    // POST /api/commissions/{id}/approve
    public function approve(Request $request, SalesCommission $commission)
    {
        $commission = $this->commissionService->approveCommission(
            $commission,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Commission approved successfully',
            'data'    => $commission,
        ]);
    }

    // POST /api/commissions/{id}/pay
    public function pay(Request $request, SalesCommission $commission)
    {
        $commission = $this->commissionService->payCommission(
            $commission,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Commission marked as paid',
            'data'    => $commission,
        ]);
    }
}
