<?php

return [
    '1c' => [
        'hostname' => env('SSH_1C_HOSTNAME'),
        'user' => env('SSH_1C_USER'),
        'port' => env('SSH_1C_PORT'),
        'bind_address' => env('SSH_1C_BIND_ADDRESS', '127.0.0.1'),
        'bind_port' => env('SSH_1C_BIND_PORT', '1433'),
    ],
];
