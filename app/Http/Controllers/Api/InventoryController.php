<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryRequest;
use App\Models\InventoryItem;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    // GET /api/inventory
    public function index(Request $request)
    {
        $items = $this->inventoryService->getAllItems($request);

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    // POST /api/inventory
    public function store(StoreInventoryRequest $request)
    {
        $item = $this->inventoryService->createItem(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory item created successfully',
            'data'    => $item,
        ], 201);
    }

    // GET /api/inventory/{id}
    public function show(InventoryItem $inventoryItem)
    {
        $inventoryItem->load('assignedClient');

        return response()->json([
            'success' => true,
            'data'    => $inventoryItem,
        ]);
    }

    // PUT /api/inventory/{id}
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'name'            => 'sometimes|string|max:255',
            'category'        => 'sometimes|string|max:255',
            'quantity'        => 'sometimes|integer|min:0',
            'unit_cost'       => 'sometimes|numeric|min:0',
            'serial_number'   => 'sometimes|nullable|string',
            'status'          => 'sometimes|in:in_stock,assigned,faulty,lost',
            'low_stock_alert' => 'sometimes|integer|min:0',
        ]);

        $item = $this->inventoryService->updateItem(
            $inventoryItem,
            $request->all(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory item updated successfully',
            'data'    => $item,
        ]);
    }

    // DELETE /api/inventory/{id}
    public function destroy(Request $request, InventoryItem $inventoryItem)
    {
        $this->inventoryService->deleteItem(
            $inventoryItem,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully',
        ]);
    }

    // POST /api/inventory/{id}/assign
    public function assign(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $item = $this->inventoryService->assignToClient(
            $inventoryItem,
            $request->client_id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Item assigned to client successfully',
            'data'    => $item,
        ]);
    }

    // POST /api/inventory/{id}/return
    public function return(Request $request, InventoryItem $inventoryItem)
    {
        $item = $this->inventoryService->returnFromClient(
            $inventoryItem,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Item returned successfully',
            'data'    => $item,
        ]);
    }

    // GET /api/inventory/low-stock
    public function lowStock()
    {
        $items = $this->inventoryService->getLowStockItems();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    // GET /api/inventory/assigned
    public function assigned()
    {
        $items = $this->inventoryService->getAssignedItems();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    // GET /api/inventory/summary
    public function summary()
    {
        $summary = $this->inventoryService->getSummary();

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }
}
