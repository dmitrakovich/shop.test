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
    // С этим товаром также заказывают (конфиг)
    'final_upsells_slider_config' => ['key' => 'final_upsells_slider_config', 'ttl' => 1800],
    // С этим товаром также заказывают (товары)
    'final_upsells_slider_products' => ['key' => 'final_upsells_by_order_id_', 'ttl' => 1800],
    // Аксессуары с доп. бонусом
    'final_accessories_slider_config' => ['key' => 'final_accessories', 'ttl' => 1800],
    // Товары на распродаже
    'final_sale_slider_config' => ['key' => 'final_sale', 'ttl' => 1800],
];
