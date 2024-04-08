<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Services settings
    |--------------------------------------------------------------------------
    |
    | Service specific settings.
    |
    */

    'sxgeo' => [
        'path' => env('SXGEO_PATH', '/database/sxgeo/'), // database path
        'file' => env('SXGEO_FILE', 'SxGeo.dat'), // SxGeo.dat or SxGeoCityMax.dat - database file
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Location
    |--------------------------------------------------------------------------
    |
    | Return when a location is not found.
    |
    */

    'default_location' => [
        'city' => [
            'id' => 524901,
            'lat' => 55.75222,
            'lon' => 37.61556,
            'name_ru' => 'Москва',
            'name_en' => 'Moscow',
            'okato' => '45',
        ],
        'region' => [
            'id' => 524894,
            'lat' => 55.76,
            'lon' => 37.61,
            'name_ru' => 'Москва',
            'name_en' => 'Moskva',
            'iso' => 'RU-MOW',
            'timezone' => 'Europe/Moscow',
            'okato' => '45',
        ],
        'country' => [
            'id' => 185,
            'iso' => 'RU',
            'continent' => 'EU',
            'lat' => 60,
            'lon' => 100,
            'name_ru' => 'Россия',
            'name_en' => 'Russia',
            'timezone' => 'Europe/Moscow',
        ],
    ],

    'default_country' => 'BY',
    'default_iso_country_code' => 'BY',

    'ignored_ip' => [
        '127.0.0.1',
    ],
];
