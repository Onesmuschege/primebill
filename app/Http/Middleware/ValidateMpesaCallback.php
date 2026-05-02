<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateMpesaCallback
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('mpesa.callback_allowed_ips', []);
        $remoteIp = $request->ip();

        if (!empty($allowedIps) && !in_array($remoteIp, $allowedIps, true)) {
            Log::warning('Blocked M-Pesa callback from non-whitelisted IP', ['ip' => $remoteIp]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Forbidden'], 403);
        }

        $secret = (string) config('mpesa.callback_signature_secret', '');
        if ($secret !== '') {
            $provided = (string) $request->header('X-MPESA-SIGNATURE', '');
            if ($provided === '') {
                Log::warning('Missing M-Pesa callback signature header', ['ip' => $remoteIp]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Forbidden'], 403);
            }

            $raw = $request->getContent();
            $expected = hash_hmac('sha256', $raw, $secret);

            if (!hash_equals($expected, $provided)) {
                Log::warning('Invalid M-Pesa callback signature', ['ip' => $remoteIp]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Forbidden'], 403);
            }
        }

        return $next($request);
    }
}

