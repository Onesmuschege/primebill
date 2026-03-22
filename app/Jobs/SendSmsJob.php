<?php

namespace App\Jobs;

use App\Services\Sms\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $phone;
    protected string $message;
    protected ?int $clientId;

    public function __construct(string $phone, string $message, ?int $clientId = null)
    {
        $this->phone    = $phone;
        $this->message  = $message;
        $this->clientId = $clientId;
    }

    public function handle(SmsService $smsService): void
    {
        $smsService->send($this->phone, $this->message, $this->clientId);
    }
}
