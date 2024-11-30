<?php

return [
    'click' => [
        'service_id' => '',
        'merchant_id' => '',
        'merchant_user_id' => '',
        'secret_key' => '',
        'with_split' => false,
    ],

    'payme' => [
        'merchant_id' => '',
        'key' => '',
        'test_key' => '',
        'is_test' => false,
    ],

    'uzum' => [
        'is_test' => true,

        'terminal_id' => '',
        'api_key' => '',
        'inn' => '',

        'test' => [
            'terminal_id' => '',
            'api_key' => '',
        ]
    ],

    'quickpay' => [
        'shop_id' => '',
        'secret_key' => '',
    ],

    'infinitypay' => [
        'is_test' => true,

        'vendor_id' => '',
        'secret_key' => '',
    ],
];