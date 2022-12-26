<?php

return [

    'path' => storage_path('app/public/sitemap'),
    'index_name' => 'index',
    'files' => [
        'products' => [
            'limit' => 2500,
            'file_name' => 'sitemap.products',
            'changefreq' => 'daily',
            'priority' => 1,
        ],
        'brands' => [
            'limit' => 5000,
            'file_name' => 'sitemap.catalog.brands',
            'changefreq' => 'daily',
            'priority' => 0.7,
        ],
        'static' => [
            'file_name' => 'sitemap.static',
            'changefreq' => 'daily',
            'priority' => 0.5,
            'routes' => ['index-page', 'static-shops', 'cart'],
            'routesWithParams' => [
                'feedbacks' => ['reviews', 'models', 'questions'],
                'info' => ['instruction', 'payment', 'delivery', 'return', 'installments'],
            ],
        ],
        'categories' => [
            'file_name' => 'sitemap.catalog.categories',
            'changefreq' => 'daily',
            'priority' => 1,
            'with_attributes' => [
                //   'brands',
                //   'sizes',
                //   'fabrics',
                //   'colors',
                //   'tags',
                //   'heels',
                ['colors', 'sizes', 'tags'],
                ['fabrics', 'sizes'],
            ],
        ],
    ],
];
