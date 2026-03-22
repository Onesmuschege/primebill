<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class InventoryService
{
    public function getAllItems(Request $request)
    {
        $query = InventoryItem::with('assignedClient');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 15);
    }

    public function createItem(array $data, $userId): InventoryItem
    {
        $data['status'] = $data['status'] ?? 'in_stock';
        $item = InventoryItem::create($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'created inventory item',
            'model'      => 'InventoryItem',
            'model_id'   => $item->id,
            'new_values' => $data,
        ]);

        return $item;
    }

    public function updateItem(InventoryItem $item, array $data, $userId): InventoryItem
    {
        $oldValues = $item->toArray();
        $item->update($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'updated inventory item',
            'model'      => 'InventoryItem',
            'model_id'   => $item->id,
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $item;
    }

    public function deleteItem(InventoryItem $item, $userId): void
    {
        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted inventory item',
            'model'      => 'InventoryItem',
            'model_id'   => $item->id,
            'old_values' => $item->toArray(),
        ]);

        $item->delete();
    }

    public function assignToClient(InventoryItem $item, int $clientId, $userId): InventoryItem
    {
        $item->update([
            'assigned_to_client_id' => $clientId,
            'status'                => 'assigned',
        ]);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'assigned inventory item to client',
            'model'      => 'InventoryItem',
            'model_id'   => $item->id,
            'new_values' => ['client_id' => $clientId],
        ]);

        return $item->load('assignedClient');
    }

    public function returnFromClient(InventoryItem $item, $userId): InventoryItem
    {
        $item->update([
            'assigned_to_client_id' => null,
            'status'                => 'in_stock',
        ]);

        SystemLog::create([
            'user_id'  => $userId,
            'action'   => 'returned inventory item from client',
            'model'    => 'InventoryItem',
            'model_id' => $item->id,
        ]);

        return $item;
    }

    public function getLowStockItems()
    {
        return InventoryItem::whereColumn('quantity', '<=', 'low_stock_alert')
                            ->where('status', 'in_stock')
                            ->get();
    }

    public function getAssignedItems()
    {
        return InventoryItem::with('assignedClient')
                            ->where('status', 'assigned')
                            ->get();
    }

    public function getSummary(): array
    {
        return [
            'total_items'    => InventoryItem::count(),
            'total_value'    => InventoryItem::sum(\DB::raw('quantity * unit_cost')),
            'in_stock'       => InventoryItem::where('status', 'in_stock')->count(),
            'assigned'       => InventoryItem::where('status', 'assigned')->count(),
            'faulty'         => InventoryItem::where('status', 'faulty')->count(),
            'low_stock'      => InventoryItem::whereColumn('quantity', '<=', 'low_stock_alert')->count(),
        ];
    }
}
