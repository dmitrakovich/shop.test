<?php

return [
    'token_cache_key' => 'hgrosh_api_token',
    'token_url' => 'https://iii.by/connect/token',
    'client_id' => env('HGROSH_CLIENT_ID'),
    'client_secret' => env('HGROSH_SECRET_KEY'),
    'serviceproviderid' => env('HGROSH_LK_ID'),
    'serviceid' => 1,
    'retailoutletcode' => 1,

    'api_url' => env('HGROSH_API_URL', 'https://api-epos.hgrosh.by/public/v1'),
];
