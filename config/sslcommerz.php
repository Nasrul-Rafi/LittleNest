<?php

return [
    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
    'base_url' => rtrim((string) env(
        'SSLCOMMERZ_BASE_URL',
        env('SSLCOMMERZ_SANDBOX', true)
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com'
    ), '/'),
    'store_id' => env('SSLCOMMERZ_STORE_ID'),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    'timeout' => 20,
];
