<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch index / alias
    |--------------------------------------------------------------------------
    |
    | Used by Catalog API v2 and index sync. Physical indices are versioned
    | (catalog_v1, …) and swapped via elastic migrations; app code uses the alias.
    |
    */
    'elasticsearch' => [
        'alias' => env('CATALOG_ELASTICSEARCH_ALIAS', 'catalog'),
        'index' => env('CATALOG_ELASTICSEARCH_INDEX', 'catalog_v1'),
    ],
];
