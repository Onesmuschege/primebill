<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Reporting\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    private function validateDates(Request $request): array
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date',
        ]);
        return [$request->from, $request->to];
    }

    public function income(Request $request)
    {
        [$from, $to] = $this->validateDates($request);
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getIncomeReport($from, $to),
        ]);
    }

    public function clients(Request $request)
    {
        [$from, $to] = $this->validateDates($request);
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getClientReport($from, $to),
        ]);
    }

    public function invoices(Request $request)
    {
        [$from, $to] = $this->validateDates($request);
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getInvoiceReport($from, $to),
        ]);
    }

    public function sms(Request $request)
    {
        [$from, $to] = $this->validateDates($request);
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getSmsReport($from, $to),
        ]);
    }

    public function network(Request $request)
    {
        [$from, $to] = $this->validateDates($request);
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getNetworkReport($from, $to),
        ]);
    }

    public function inventory()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getInventoryReport(),
        ]);
    }

    public function expenditure(Request $request)
    {
        [$from, $to] = $this->validateDates($request);
        return response()->json([
            'success' => true,
            'data'    => $this->reportService->getExpenditureReport($from, $to),
        ]);
    }

    public function export(Request $request, string $type)
    {
        [$from, $to] = $this->validateDates($request);

        $data = match($type) {
            'income'      => $this->reportService->getIncomeReport($from, $to),
            'clients'     => $this->reportService->getClientReport($from, $to),
            'invoices'    => $this->reportService->getInvoiceReport($from, $to),
            'sms'         => $this->reportService->getSmsReport($from, $to),
            'network'     => $this->reportService->getNetworkReport($from, $to),
            'expenditure' => $this->reportService->getExpenditureReport($from, $to),
            default       => [],
        };

        $csv  = "Key,Value\n";
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $csv .= "{$key},{$value}\n";
            }
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$type}_report.csv",
        ]);
    }
}
