<?php

return [
    'gateway'   => env('SMS_GATEWAY', 'africas_talking'),
    'sender_id' => env('SMS_SENDER_ID', 'PRIMEBILL'),

    'africas_talking' => [
        'api_key'  => env('AT_API_KEY'),
        'username' => env('AT_USERNAME', 'sandbox'),
    ],

    'hostpinnacle' => [
        'api_key' => env('HOSTPINNACLE_API_KEY'),
    ],

    'templates' => [
        'payment_received'   => 'Dear {name}, payment of KES {amount} received. Ref: {mpesa_code}. Expires: {expiry}.',
        'invoice_due'        => 'Dear {name}, invoice KES {amount} due on {date}. Pay via M-Pesa {paybill}.',
        'account_suspended'  => 'Dear {name}, your account has been suspended. Pay KES {amount} to reactivate.',
        'account_activated'  => 'Dear {name}, your account is now active. Enjoy {plan} until {expiry}.',
        'welcome'            => 'Welcome to {company}, {name}! Username: {username} Password: {password}.',
    ],
];
