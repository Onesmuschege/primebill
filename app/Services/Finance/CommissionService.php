<?php

namespace App\Services\Finance;

use App\Models\SalesCommission;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class CommissionService
{
    public function getAllCommissions(Request $request)
    {
        $query = SalesCommission::with('user', 'client', 'invoice');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 15);
    }

    public function approveCommission(SalesCommission $commission, $userId): SalesCommission
    {
        $commission->update(['status' => 'approved']);

        SystemLog::create([
            'user_id'  => $userId,
            'action'   => 'approved commission',
            'model'    => 'SalesCommission',
            'model_id' => $commission->id,
        ]);

        return $commission;
    }

    public function payCommission(SalesCommission $commission, $userId): SalesCommission
    {
        $commission->update(['status' => 'paid']);

        SystemLog::create([
            'user_id'  => $userId,
            'action'   => 'paid commission',
            'model'    => 'SalesCommission',
            'model_id' => $commission->id,
        ]);

        return $commission;
    }

    public function getSummary(): array
    {
        return [
            'pending'  => SalesCommission::where('status', 'pending')->sum('amount'),
            'approved' => SalesCommission::where('status', 'approved')->sum('amount'),
            'paid'     => SalesCommission::where('status', 'paid')->sum('amount'),
            'total'    => SalesCommission::sum('amount'),
        ];
    }
}
