<?php

namespace App\Services\Sms;

use App\Models\SmsLog;
use App\Models\Client;
use App\Services\Sms\Gateways\AfricasTalkingGateway;
use App\Services\Sms\Gateways\HostpinnacleGateway;
use App\Services\Sms\Gateways\SmsGatewayInterface;
use Illuminate\Http\Request;

class SmsService
{
    protected SmsGatewayInterface $gateway;

    public function __construct()
    {
        $this->gateway = $this->resolveGateway();
    }

    protected function resolveGateway(): SmsGatewayInterface
    {
        return match(config('sms.gateway')) {
            'hostpinnacle' => new HostpinnacleGateway(),
            default        => new AfricasTalkingGateway(),
        };
    }

    public function send(string $phone, string $message, ?int $clientId = null): bool
    {
        $phone    = $this->formatPhone($phone);
        $response = $this->gateway->send($phone, $message);
        $status   = isset($response['error']) ? 'failed' : 'sent';

        SmsLog::create([
            'client_id'        => $clientId,
            'phone'            => $phone,
            'message'          => $message,
            'status'           => $status,
            'gateway_response' => json_encode($response),
            'gateway'          => config('sms.gateway'),
        ]);

        return $status === 'sent';
    }

    public function sendBulk(array $recipients, string $message): int
    {
        $count = 0;
        foreach ($recipients as $recipient) {
            $phone    = is_array($recipient) ? $recipient['phone'] : $recipient;
            $clientId = is_array($recipient) ? ($recipient['client_id'] ?? null) : null;

            if ($this->send($phone, $message, $clientId)) {
                $count++;
            }
        }
        return $count;
    }

    public function sendToOverdueClients(string $message): int
    {
        $clients = Client::where('status', 'suspended')
                         ->orWhereHas('invoices', function ($q) {
                             $q->where('status', 'overdue');
                         })->get();

        $recipients = $clients->map(fn($c) => [
            'phone'     => $c->phone,
            'client_id' => $c->id,
        ])->toArray();

        return $this->sendBulk($recipients, $message);
    }

    public function getBalance(): string
    {
        return $this->gateway->getBalance();
    }

    public function parseTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    public function getLogs(Request $request)
    {
        $query = SmsLog::with('client');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 15);
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
}
