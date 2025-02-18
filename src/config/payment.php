<?php

return [
    'api_prefix' => 'api/v1',

    'vat_percent' => 12,
    'inn' => '',

    'click' => [
        'service_id' => '',
        'merchant_id' => '',
        'merchant_user_id' => '',
        'secret_key' => '',
        'with_split' => false,

        'enabled' => false,
    ],

    'payme' => [
        'merchant_id' => '',
        'key' => '',
        'test_key' => '',

        'is_test' => false,
        'enabled' => false,
    ],

    'uzum' => [
        'is_test' => true,

        'terminal_id' => '',
        'api_key' => '',

        'test' => [
            'terminal_id' => '',
            'api_key' => '',
        ],
        'enabled' => false,
    ],

    'quickpay' => [
        'shop_id' => '',
        'secret_key' => '',

        'enabled' => false,
    ],

    'infinitypay' => [
        'vendor_id' => '',
        'secret_key' => '',

        'is_test' => true,
        'enabled' => false,
    ],

    'octobank' => [
        'secret' => '',
        'hash_secret' => '',
        'shop_id' => '',

        'is_test' => true,
        'enabled' => false,
    ],
];