<?php

return [

    'return_url' => 'https://barocco.by/catalog',
    'payment_systems' => [
        'yandex' => [
            'mode' => env('YOOKASSA_MODE', 'production'),
            'auth' => [
                'account' => env('YOOKASSA_ACCOUNT'),
                'secure' => env('YOOKASSA_SECURE'),
                'test_account' => env('YOOKASSA_TEST_ACCOUNT'),
                'test_secure' => env('YOOKASSA_TEST_SECURE'),
            ],
        ],
    ],
];
