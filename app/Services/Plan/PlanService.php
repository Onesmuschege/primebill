<?php

namespace App\Services\Plan;

use App\Models\Plan;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class PlanService
{
    public function getAllPlans(Request $request)
    {
        $query = Plan::with('router');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return $query->orderBy('price', 'asc')->get();
    }

    public function createPlan(array $data, $userId)
    {
        $plan = Plan::create($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'created plan',
            'model'      => 'Plan',
            'model_id'   => $plan->id,
            'new_values' => $data,
        ]);

        return $plan;
    }

    public function updatePlan(Plan $plan, array $data, $userId)
    {
        $oldValues = $plan->toArray();
        $plan->update($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'updated plan',
            'model'      => 'Plan',
            'model_id'   => $plan->id,
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $plan;
    }

    public function deletePlan(Plan $plan, $userId)
    {
        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted plan',
            'model'      => 'Plan',
            'model_id'   => $plan->id,
            'old_values' => $plan->toArray(),
        ]);

        $plan->delete();
    }
}
