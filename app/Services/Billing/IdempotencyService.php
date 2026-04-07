<?php

namespace App\Services\Billing;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Database\QueryException;
use RuntimeException;

class IdempotencyService
{
    public function run(string $scope, ?string $key, Closure $callback): mixed
    {
        if (!$key) {
            return $callback();
        }

        $existing = IdempotencyKey::where('scope', $scope)
            ->where('idempotency_key', $key)
            ->first();

        if ($existing?->status === 'completed') {
            return $existing->response_payload['result'] ?? null;
        }

        if ($existing?->status === 'processing') {
            throw new RuntimeException('A request with this idempotency key is currently processing.');
        }

        try {
            $record = IdempotencyKey::create([
                'scope' => $scope,
                'idempotency_key' => $key,
                'status' => 'processing',
            ]);
        } catch (QueryException) {
            $concurrent = IdempotencyKey::where('scope', $scope)
                ->where('idempotency_key', $key)
                ->first();

            if ($concurrent?->status === 'completed') {
                return $concurrent->response_payload['result'] ?? null;
            }

            throw new RuntimeException('Duplicate idempotency key submitted.');
        }

        try {
            $result = $callback();

            $record->update([
                'status' => 'completed',
                'response_payload' => ['result' => $result],
            ]);

            return $result;
        } catch (\Throwable $e) {
            $record->update([
                'status' => 'failed',
                'response_payload' => ['message' => $e->getMessage()],
            ]);
            throw $e;
        }
    }
}
