<?php

namespace App\Services\Network;

use App\Models\Router;
use App\Models\NetworkTraffic;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class RouterService
{
    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function getAllRouters()
    {
        return Router::orderBy('name')->get();
    }

    public function createRouter(array $data, $userId): Router
    {
        $router = Router::create($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'created router',
            'model'      => 'Router',
            'model_id'   => $router->id,
            'new_values' => $data,
        ]);

        return $router;
    }

    public function updateRouter(Router $router, array $data, $userId): Router
    {
        $oldValues = $router->toArray();
        $router->update($data);

        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'updated router',
            'model'      => 'Router',
            'model_id'   => $router->id,
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $router;
    }

    public function deleteRouter(Router $router, $userId): void
    {
        SystemLog::create([
            'user_id'    => $userId,
            'action'     => 'deleted router',
            'model'      => 'Router',
            'model_id'   => $router->id,
            'old_values' => $router->toArray(),
        ]);

        $router->delete();
    }

    public function testConnection(Router $router): bool
    {
        $connected = $this->mikrotik->connect($router);

        $router->update([
            'status'    => $connected ? 'online' : 'offline',
            'last_seen' => $connected ? now() : $router->last_seen,
        ]);

        return $connected;
    }

    public function getRouterResources(Router $router): array
    {
        if (!$this->mikrotik->connect($router)) {
            return [];
        }

        return $this->mikrotik->getRouterResources();
    }

    public function getActiveSessions(Router $router): array
    {
        if (!$this->mikrotik->connect($router)) {
            return [];
        }

        return $this->mikrotik->getActiveSessions();
    }

    public function pollTraffic(Router $router): void
    {
        if (!$this->mikrotik->connect($router)) {
            $router->update(['status' => 'offline']);
            return;
        }

        $stats = $this->mikrotik->getTrafficStats();

        if (!empty($stats)) {
            NetworkTraffic::create([
                'router_id'   => $router->id,
                'tx_bytes'    => $stats['tx-byte'] ?? 0,
                'rx_bytes'    => $stats['rx-byte'] ?? 0,
                'interface'   => $stats['name'] ?? 'ether1',
                'recorded_at' => now(),
            ]);

            $router->update([
                'status'    => 'online',
                'last_seen' => now(),
            ]);
        }
    }
}
