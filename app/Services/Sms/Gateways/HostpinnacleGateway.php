<?php

namespace App\Services\Sms\Gateways;

use Illuminate\Support\Facades\Http;
use Exception;

class HostpinnacleGateway implements SmsGatewayInterface
{
    protected string $apiKey;
    protected string $senderId;
    protected string $baseUrl = 'https://sms.hostpinnacle.co.ke/sms/v1';

    public function __construct()
    {
        $this->apiKey   = config('sms.hostpinnacle.api_key');
        $this->senderId = config('sms.sender_id');
    }

    public function send(string $phone, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/send", [
                'mobile'  => $phone,
                'message' => $message,
                'sender'  => $this->senderId,
            ]);

            return $response->json();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $phones, string $message): array
    {
        $results = [];
        foreach ($phones as $phone) {
            $results[] = $this->send($phone, $message);
        }
        return $results;
    }

    public function getBalance(): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/balance");

            return $response->json('balance') ?? 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }
}
