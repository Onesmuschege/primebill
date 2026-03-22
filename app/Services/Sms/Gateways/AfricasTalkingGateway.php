<?php

namespace App\Services\Sms\Gateways;

use Illuminate\Support\Facades\Http;
use Exception;

class AfricasTalkingGateway implements SmsGatewayInterface
{
    protected string $apiKey;
    protected string $username;
    protected string $senderId;
    protected string $baseUrl = 'https://api.africastalking.com/version1';

    public function __construct()
    {
        $this->apiKey   = config('sms.africas_talking.api_key');
        $this->username = config('sms.africas_talking.username');
        $this->senderId = config('sms.sender_id');
    }

    public function send(string $phone, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'apiKey'       => $this->apiKey,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post("{$this->baseUrl}/messaging", [
                'username' => $this->username,
                'to'       => $phone,
                'message'  => $message,
                'from'     => $this->senderId,
            ]);

            return $response->json();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $phones, string $message): array
    {
        return $this->send(implode(',', $phones), $message);
    }

    public function getBalance(): string
    {
        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("https://api.africastalking.com/version1/user?username={$this->username}");

            return $response->json('UserData.balance') ?? 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }
}
