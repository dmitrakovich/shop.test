<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog search driver
    |--------------------------------------------------------------------------
    |
    | mysql — current Eloquent listing (default).
    | elasticsearch — Phase 3+ read model (not wired yet).
    |
    */
    'search_driver' => env('CATALOG_SEARCH_DRIVER', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch index / alias
    |--------------------------------------------------------------------------
    |
    | App code should query the alias. Physical indices are versioned
    | (catalog_v1, catalog_v2, …) and swapped via elastic migrations.
    |
    */
    'elasticsearch' => [
        'alias' => env('CATALOG_ELASTICSEARCH_ALIAS', 'catalog'),
        'index' => env('CATALOG_ELASTICSEARCH_INDEX', 'catalog_v1'),
    ],
];
