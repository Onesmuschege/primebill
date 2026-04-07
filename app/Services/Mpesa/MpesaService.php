<?php

namespace App\Services\Mpesa;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;
use App\Services\Billing\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MpesaService
{
    protected string $baseUrl;
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $env           = config('mpesa.env');
        $this->baseUrl = config("mpesa.base_url.{$env}");
        $this->paymentService = $paymentService;
    }

    // Get OAuth token
    public function getAccessToken(): string
    {
        $consumerKey    = config('mpesa.consumer_key');
        $consumerSecret = config('mpesa.consumer_secret');
        $credentials    = base64_encode("{$consumerKey}:{$consumerSecret}");

        $response = Http::withHeaders([
            'Authorization' => "Basic {$credentials}",
        ])->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

        return $response->json('access_token');
    }

    // STK Push
    public function stkPush(string $phone, float $amount, int $invoiceId, string $accountRef): array
    {
        try {
            $token     = $this->getAccessToken();
            $timestamp = now()->format('YmdHis');
            $shortcode = config('mpesa.shortcode');
            $passkey   = config('mpesa.passkey');
            $password  = base64_encode($shortcode . $passkey . $timestamp);
            $phone     = $this->formatPhone($phone);

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                    'BusinessShortCode' => $shortcode,
                    'Password'          => $password,
                    'Timestamp'         => $timestamp,
                    'TransactionType'   => 'CustomerPayBillOnline',
                    'Amount'            => (int) $amount,
                    'PartyA'            => $phone,
                    'PartyB'            => $shortcode,
                    'PhoneNumber'       => $phone,
                    'CallBackURL'       => config('mpesa.callback_url'),
                    'AccountReference'  => $accountRef,
                    'TransactionDesc'   => 'Internet Bill Payment',
                ]);

            return $response->json();

        } catch (Exception $e) {
            Log::error('STK Push Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // Handle STK Callback
    public function handleStkCallback(array $payload): bool
    {
        try {
            $body        = $payload['Body']['stkCallback'];
            $resultCode  = $body['ResultCode'];
            $checkoutId  = $body['CheckoutRequestID'];

            if ($resultCode !== 0) {
                Log::warning('STK Push failed: ' . $body['ResultDesc']);
                return false;
            }

            $metadata   = $body['CallbackMetadata']['Item'];
            $amount     = $this->getMetadataValue($metadata, 'Amount');
            $mpesaCode  = $this->getMetadataValue($metadata, 'MpesaReceiptNumber');
            $phone      = $this->getMetadataValue($metadata, 'PhoneNumber');

            // Find client by phone
            $client = Client::where('phone', 'like', '%' . substr($phone, -9))->first();

            if (!$client) {
                Log::warning('Client not found for phone: ' . $phone);
                return false;
            }

            // Find unpaid invoice
            $invoice = Invoice::where('client_id', $client->id)
                              ->where('status', 'unpaid')
                              ->orderBy('created_at', 'asc')
                              ->first();

            // Record payment
            $this->paymentService->recordPayment([
                'client_id'  => $client->id,
                'invoice_id' => $invoice?->id,
                'amount'     => $amount,
                'method'     => 'mpesa',
                'mpesa_code' => $mpesaCode,
                'reference'  => $checkoutId,
                'idempotency_key' => $mpesaCode ?: $checkoutId,
            ], null);

            return true;

        } catch (Exception $e) {
            Log::error('STK Callback Error: ' . $e->getMessage());
            return false;
        }
    }

    // Handle C2B Confirmation
    public function handleC2BConfirmation(array $payload): bool
    {
        try {
            $amount    = $payload['TransAmount'];
            $mpesaCode = $payload['TransID'];
            $phone     = $payload['MSISDN'];
            $account   = $payload['BillRefNumber'];

            $client = Client::where('phone', 'like', '%' . substr($phone, -9))->first();

            if (!$client) return false;

            $invoice = Invoice::where('client_id', $client->id)
                              ->where('status', 'unpaid')
                              ->orderBy('created_at', 'asc')
                              ->first();

            $this->paymentService->recordPayment([
                'client_id'  => $client->id,
                'invoice_id' => $invoice?->id,
                'amount'     => $amount,
                'method'     => 'mpesa',
                'mpesa_code' => $mpesaCode,
                'reference'  => $account,
                'idempotency_key' => $mpesaCode ?: $account,
            ], null);

            return true;

        } catch (Exception $e) {
            Log::error('C2B Confirmation Error: ' . $e->getMessage());
            return false;
        }
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    private function getMetadataValue(array $items, string $name): mixed
    {
        foreach ($items as $item) {
            if ($item['Name'] === $name) {
                return $item['Value'];
            }
        }
        return null;
    }
}
