<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expenditure\StoreExpenditureRequest;
use App\Models\Expenditure;
use App\Services\Finance\ExpenditureService;
use Illuminate\Http\Request;

class ExpenditureController extends Controller
{
    protected ExpenditureService $expenditureService;

    public function __construct(ExpenditureService $expenditureService)
    {
        $this->expenditureService = $expenditureService;
    }

    // GET /api/expenditures
    public function index(Request $request)
    {
        $expenditures = $this->expenditureService->getAllExpenditures($request);

        return response()->json([
            'success' => true,
            'data'    => $expenditures,
        ]);
    }

    // POST /api/expenditures
    public function store(StoreExpenditureRequest $request)
    {
        $expenditure = $this->expenditureService->createExpenditure(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Expenditure recorded successfully',
            'data'    => $expenditure,
        ], 201);
    }

    // GET /api/expenditures/{id}
    public function show(Expenditure $expenditure)
    {
        return response()->json([
            'success' => true,
            'data'    => $expenditure,
        ]);
    }

    // PUT /api/expenditures/{id}
    public function update(Request $request, Expenditure $expenditure)
    {
        $request->validate([
            'category'    => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'amount'      => 'sometimes|numeric|min:1',
            'date'        => 'sometimes|date',
        ]);

        $expenditure = $this->expenditureService->updateExpenditure(
            $expenditure,
            $request->all(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Expenditure updated successfully',
            'data'    => $expenditure,
        ]);
    }

    // DELETE /api/expenditures/{id}
    public function destroy(Request $request, Expenditure $expenditure)
    {
        $this->expenditureService->deleteExpenditure(
            $expenditure,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Expenditure deleted successfully',
        ]);
    }

    // GET /api/expenditures/summary
    public function summary()
    {
        $summary = $this->expenditureService->getMonthlySummary();

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    // GET /api/expenditures/categories
    public function categories()
    {
        $categories = $this->expenditureService->getCategories();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }
}
