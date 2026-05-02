<?php

namespace App\Services\Mpesa;

use App\Models\Invoice;
use App\Models\MpesaTransaction;
use App\Services\Billing\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                return ['error' => 'Invoice not found'];
            }

            $token     = $this->getAccessToken();
            $timestamp = now()->format('YmdHis');
            $shortcode = config('mpesa.shortcode');
            $passkey   = config('mpesa.passkey');
            $password  = base64_encode($shortcode . $passkey . $timestamp);
            $phone     = $this->formatPhone($phone);

            $requestPayload = [
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
            ];

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                    ...$requestPayload,
                ]);

            $json = $response->json() ?? [];

            // Track STK request for secure callback reconciliation.
            MpesaTransaction::create([
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'phone' => $phone,
                'amount' => (int) $amount,
                'account_reference' => $accountRef,
                'merchant_request_id' => $json['MerchantRequestID'] ?? null,
                'checkout_request_id' => $json['CheckoutRequestID'] ?? null,
                'result_code' => $json['ResponseCode'] ?? null,
                'result_desc' => $json['ResponseDescription'] ?? ($json['errorMessage'] ?? null),
                'status' => isset($json['CheckoutRequestID']) ? 'pending' : 'failed',
                'raw_request' => $requestPayload,
            ]);

            return $json;

        } catch (Exception $e) {
            Log::error('STK Push Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // Handle STK Callback
    public function handleStkCallback(array $payload): bool
    {
        try {
            $body = $payload['Body']['stkCallback'] ?? null;
            if (!$body) {
                Log::warning('STK Callback missing Body.stkCallback');
                return false;
            }

            $resultCode = $body['ResultCode'] ?? null;
            $checkoutId = $body['CheckoutRequestID'] ?? null;
            $resultDesc = $body['ResultDesc'] ?? null;

            if (!$checkoutId) {
                Log::warning('STK Callback missing CheckoutRequestID');
                return false;
            }

            $tx = MpesaTransaction::where('checkout_request_id', $checkoutId)->first();
            if (!$tx) {
                Log::warning('STK Callback received for unknown CheckoutRequestID: ' . $checkoutId);
                return false;
            }

            // Idempotency: ignore duplicate callbacks once completed.
            if ($tx->status === 'completed') {
                return true;
            }

            $metadata = $body['CallbackMetadata']['Item'] ?? [];
            $amount = $this->getMetadataValue($metadata, 'Amount');
            $mpesaCode = $this->getMetadataValue($metadata, 'MpesaReceiptNumber');
            $phone = $this->getMetadataValue($metadata, 'PhoneNumber');

            $tx->update([
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'raw_callback' => $payload,
                'mpesa_receipt_number' => $mpesaCode ?: $tx->mpesa_receipt_number,
                'phone' => $phone ? (string) $phone : $tx->phone,
                'amount' => $amount ?: $tx->amount,
                'status' => ((int) $resultCode === 0) ? 'pending' : 'failed',
            ]);

            if ((int) $resultCode !== 0) {
                Log::warning('STK Push failed: ' . ($resultDesc ?? 'Unknown'));
                return false;
            }

            $invoiceId = $tx->invoice_id;
            $clientId = $tx->client_id;

            DB::transaction(function () use ($invoiceId, $clientId, $amount, $mpesaCode, $checkoutId, $tx) {
                if ($invoiceId) {
                    // Lock invoice row to avoid concurrent pay/overpay races.
                    Invoice::whereKey($invoiceId)->lockForUpdate()->first();
                }

                $this->paymentService->recordPayment([
                    'client_id' => $clientId,
                    'invoice_id' => $invoiceId,
                    'amount' => $amount,
                    'method' => 'mpesa',
                    'mpesa_code' => $mpesaCode,
                    'reference' => $checkoutId,
                    'idempotency_key' => $mpesaCode ?: $checkoutId,
                ], null);

                $tx->update(['status' => 'completed']);
            });

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
