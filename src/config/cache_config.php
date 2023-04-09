<?php

return [
    /**
     * Global view
     */
    'global_nav_categories' => ['key' => 'global_nav_categories'],
    'global_nav_info_pages' => ['key' => 'global_nav_info_pages'],
    'global_user_discounts' => ['key' => 'global_user_discounts'],

    /**
     * Slider
     */
    // слайдер похожие товары
    'product_carousel_similar_products' => ['key' => 'product_carousel_similar_products'],
    //похожие товары на товар id
    'similar_products' => ['key' => 'similar_products_by_product_id_', 'ttl' => 1800],
    // слайдер недавно просмотренные
    'product_carousel_recent_products' => ['key' => 'product_carousel_recent_products'],
    // слайдер группа товаров
    'product_carousel_product_group' => ['key' => 'product_carousel_product_group'],
    //группа товаров
    'product_group' => ['key' => 'product_group_by_product_id_', 'ttl' => 1800],
];
