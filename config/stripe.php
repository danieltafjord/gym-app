<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'connect_webhook_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
    'application_fee_percent' => (int) env('STRIPE_APPLICATION_FEE_PERCENT', 10),
    'currency' => env('STRIPE_CURRENCY', 'usd'),
    'dev_mode' => (bool) env('STRIPE_DEV_MODE', false),
];
