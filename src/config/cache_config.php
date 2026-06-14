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
    // похожие товары на товар id
    'similar_products' => ['key' => 'similar_products_by_product_id_', 'ttl' => 1800],
    // группа товаров
    'product_group' => ['key' => 'product_group_by_product_id_', 'ttl' => 1800],
];
