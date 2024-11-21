<?php

return [
    'click' => [
        'service_id' => '',
        'merchant_id' => '',
        'merchant_user_id' => '',
        'secret_key' => '',
    ],

    'payme' => [
        'merchant_id' => '',
        'key' => '',
        'test_key' => '',
    ],

    'uzum' => [
        'is_test' => true,

        'terminal_id' => '',
        'api_key' => '',
    ],

    'quickpay' => [
        'shop_id' => '',
        'secret_key' => '',
        'success_url' => config('app.url'),
    ],

    'infinitypay' => [
        'is_test' => true,

        'vendor_id' => '',
        'secret_key' => '',
        'success_url' => config('app.url'),
    ],
];