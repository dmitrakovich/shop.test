<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog search driver
    |--------------------------------------------------------------------------
    |
    | mysql — Eloquent listing (default).
    | elasticsearch — ES read model via CatalogSearchService
    |   (falls back to mysql when Status "promotion" is selected).
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
