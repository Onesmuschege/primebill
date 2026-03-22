<?php

namespace App\Services\Sms\Gateways;

interface SmsGatewayInterface
{
    public function send(string $phone, string $message): array;
    public function sendBulk(array $phones, string $message): array;
    public function getBalance(): string;
}
