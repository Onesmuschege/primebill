<?php

return [
    'env'              => env('MPESA_ENV', 'sandbox'),
    'consumer_key'     => env('MPESA_CONSUMER_KEY'),
    'consumer_secret'  => env('MPESA_CONSUMER_SECRET'),
    'shortcode'        => env('MPESA_SHORTCODE'),
    'passkey'          => env('MPESA_PASSKEY'),
    'callback_url'     => env('MPESA_CALLBACK_URL'),
    'c2b_validation'   => env('MPESA_C2B_VALIDATION_URL'),
    'c2b_confirmation' => env('MPESA_C2B_CONFIRMATION_URL'),

    'base_url' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'live'    => 'https://api.safaricom.co.ke',
    ],

    // Callback hardening (recommended in production)
    // - **callback_allowed_ips**: comma-separated Safaricom IPs / proxies that are allowed to hit callbacks.
    // - **callback_signature_secret**: if set, require header `X-MPESA-SIGNATURE` = HMAC-SHA256(raw_body, secret)
    'callback_allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('MPESA_CALLBACK_ALLOWED_IPS', ''))))),
    'callback_signature_secret' => env('MPESA_CALLBACK_SIGNATURE_SECRET', ''),
];
