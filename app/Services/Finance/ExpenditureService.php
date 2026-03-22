<?php

namespace App\Services\Finance;

use App\Models\Expenditure;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class ExpenditureService
{
    public function getAllExpenditures(Request $request)
    {
        $query = Expenditure::with('recordedBy');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        return $query->orderBy('date', 'desc')
                     ->paginate($request->per_page ?? 15);
    }

    public function createExpenditure(array $data, $userId): Expenditure
    {
        $data['recorded_by'] = $userId;
        $expenditure = Expenditure::create($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'created expenditure',
            'model'      => 'Expenditure',
            'model_id'   => $expenditure->id,
            'new_values' => $data,
        ]);

        return $expenditure;
    }

    public function updateExpenditure(Expenditure $expenditure, array $data, $userId): Expenditure
    {
        $oldValues = $expenditure->toArray();
        $expenditure->update($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'updated expenditure',
            'model'      => 'Expenditure',
            'model_id'   => $expenditure->id,
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $expenditure;
    }

    public function deleteExpenditure(Expenditure $expenditure, $userId): void
    {
        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted expenditure',
            'model'      => 'Expenditure',
            'model_id'   => $expenditure->id,
            'old_values' => $expenditure->toArray(),
        ]);

        $expenditure->delete();
    }

    public function getMonthlySummary(): array
    {
        $month = now()->month;
        $year  = now()->year;

        $totalIncome = Payment::whereMonth('created_at', $month)
                              ->whereYear('created_at', $year)
                              ->where('status', 'completed')
                              ->sum('amount');

        $totalExpenditure = Expenditure::whereMonth('date', $month)
                                       ->whereYear('date', $year)
                                       ->sum('amount');

        $unpaidInvoices = Invoice::where('status', 'unpaid')
                                 ->orWhere('status', 'overdue')
                                 ->sum('total');

        return [
            'income'       => $totalIncome,
            'expenditure'  => $totalExpenditure,
            'net_revenue'  => $totalIncome - $totalExpenditure,
            'receivables'  => $unpaidInvoices,
            'month'        => now()->format('F Y'),
        ];
    }

    public function getCategories(): array
    {
        return Expenditure::distinct()
                          ->pluck('category')
                          ->toArray();
    }
}
