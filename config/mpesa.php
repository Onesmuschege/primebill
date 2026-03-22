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
];
