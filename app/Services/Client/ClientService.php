<?php

namespace App\Services\Client;

use App\Models\Client;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class ClientService
{
    public function getAllClients(Request $request)
    {
        $query = Client::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by town
        if ($request->has('town')) {
            $query->where('town', $request->town);
        }

        // Search by name or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 15);
    }

    public function createClient(array $data, $userId)
    {
        $data['created_by'] = $userId;
        $client = Client::create($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'created client',
            'model'      => 'Client',
            'model_id'   => $client->id,
            'new_values' => $data,
        ]);

        return $client;
    }

    public function updateClient(Client $client, array $data, $userId)
    {
        $oldValues = $client->toArray();
        $client->update($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'updated client',
            'model'      => 'Client',
            'model_id'   => $client->id,
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $client;
    }

    public function suspendClient(Client $client, $userId)
    {
        $client->update(['status' => 'suspended']);

        // Suspend all active accounts
        $client->accounts()->where('status', 'active')
               ->update(['status' => 'suspended']);

        SystemLog::create([
            'user_id'  => $userId,
            'action'   => 'suspended client',
            'model'    => 'Client',
            'model_id' => $client->id,
        ]);

        return $client;
    }

    public function activateClient(Client $client, $userId)
    {
        $client->update(['status' => 'active']);

        // Activate suspended accounts
        $client->accounts()->where('status', 'suspended')
               ->update(['status' => 'active']);

        SystemLog::create([
            'user_id'  => $userId,
            'action'   => 'activated client',
            'model'    => 'Client',
            'model_id' => $client->id,
        ]);

        return $client;
    }

    public function deleteClient(Client $client, $userId)
    {
        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted client',
            'model'      => 'Client',
            'model_id'   => $client->id,
            'old_values' => $client->toArray(),
        ]);

        $client->delete();
    }
}
