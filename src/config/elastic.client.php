<?php

declare(strict_types=1);

return [
    'default' => env('ELASTIC_CONNECTION', 'default'),
    'connections' => [
        'default' => [
            'hosts' => [
                env('ELASTIC_HOST', 'localhost:9200'),
            ],
            // configure basic authentication
            'basicAuthentication' => [
                env('ELASTIC_USERNAME'),
                env('ELASTIC_PASSWORD'),
            ],
            // bool, or absolute path to Elasticsearch HTTP CA (e.g. http_ca.crt)
            'sslVerification' => env('ELASTIC_SSL_CA') ?: true,
        ],
    ],
];
