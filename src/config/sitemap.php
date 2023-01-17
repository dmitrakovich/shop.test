<?php

return [
  'path' => storage_path('app/public/sitemap'),
  'index_name' => 'sitemap',
  'files' => [
    'products' => [
      'limit' => 5000,
      'file_name' => 'sitemap.products',
      'changefreq' => 'daily',
      'priority' => 1,
    ],
    'catalog' => [
      'file_name' => 'sitemap.catalog',
      'changefreq' => 'daily',
      'priority' => 1,
      'limit' => 5000,
      'options_list'   => [
        ['values' => ['category']],
        ['values' => ['brand']],
        ['values' => ['cities', 'category']],
        ['values' => ['cities', 'category', 'tags']],
        ['values' => ['category', 'brand']],
        ['values' => ['category', 'sizes']],
        ['values' => ['category', 'fabrics']],
        ['values' => ['category', 'colors']],
        ['values' => ['category', 'tags']],
        ['values' => ['category', 'heels']],
        ['values' => ['category', 'sizes', 'colors']],
        ['values' => ['category', 'fabrics', 'sizes']],
        ['values' => ['category', 'fabrics', 'sizes']],
      ]
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
  ],
];
