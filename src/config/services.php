<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'yandex' => [
        'counter_id' => env('YANDEX_COUNTER_ID'),
        'token' => env('YANDEX_TOKEN'),
    ],

    'sxgeo' => [
        'token' => env('SXGEO_TOKEN'),
    ],

    'facebook' => [
        'enabled' => env('FACEBOOK_ENABLED', false),
        'account_id' => env('FACEBOOK_ACCOUNT_ID', ''),
        'pixel_id' => env('FACEBOOK_PIXEL_ID', ''),
        'token' => env('FACEBOOK_TOKEN', ''),
        'log_file' => storage_path('logs/facebook.log'),
    ],

    'imap' => [
        'belpost_host' => env('IMAP_BELPOST_HOST'),
        'belpost_user' => env('IMAP_BELPOST_USER'),
        'belpost_password' => env('IMAP_BELPOST_PASSWORD'),
    ],
];
