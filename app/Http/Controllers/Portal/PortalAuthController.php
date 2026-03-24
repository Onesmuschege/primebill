<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAccount;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PortalAuthController extends Controller
{
    // POST /api/portal/login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = ClientAccount::with('client')
                                ->where('username', $request->username)
                                ->first();

        if (!$account || !Hash::check($request->password, $account->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($account->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is suspended. Please contact support.',
            ], 403);
        }

        $token = $account->client->createToken('portal_token')->plainTextToken;

        SystemLog::create([
            'action'     => 'client portal login',
            'model'      => 'Client',
            'model_id'   => $account->client_id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data'    => [
                'client'  => [
                    'id'         => $account->client->id,
                    'name'       => $account->client->first_name . ' ' . $account->client->last_name,
                    'email'      => $account->client->email,
                    'phone'      => $account->client->phone,
                ],
                'account' => [
                    'id'          => $account->id,
                    'username'    => $account->username,
                    'status'      => $account->status,
                    'expiry_date' => $account->expiry_date,
                    'plan'        => $account->plan,
                ],
                'token'   => $token,
            ],
        ]);
    }

    // POST /api/portal/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}