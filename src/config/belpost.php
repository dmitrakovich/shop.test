<?php

use App\Enums\Belpost\BelpostDirection;
use App\Enums\Belpost\BelpostNotification;
use App\Enums\Belpost\BelpostPaymentType;
use App\Enums\Belpost\BelpostPostalDeliveryType;

return [

    'base_url' => env('BELPOST_API_BASE_URL', 'https://api.belpost.by'),

    'token' => env('BELPOST_API_TOKEN'),

    'defaults' => [
        'postal_delivery_type' => env('BELPOST_POSTAL_DELIVERY_TYPE', BelpostPostalDeliveryType::EcommerceElite->value),
        'direction' => env('BELPOST_DIRECTION', BelpostDirection::Internal->value),
        'payment_type' => env('BELPOST_PAYMENT_TYPE', BelpostPaymentType::ElectronicPersonalAccount->value),
        'card_number' => env('BELPOST_CARD_NUMBER'),
        'negotiated_rate' => (bool)env('BELPOST_NEGOTIATED_RATE', false),
        // For e-commerce parcel types API accepts only 1, 2, 5 (not 0).
        'notification' => (int)env('BELPOST_NOTIFICATION', BelpostNotification::Electronic->value),
        'fallback_recipient_email' => env('BELPOST_FALLBACK_RECIPIENT_EMAIL'),
        'sender_phone' => env('BELPOST_SENDER_PHONE', config('app.phone')),
        'sender_email' => env('BELPOST_SENDER_EMAIL', config('app.email')),
        // Item `addons.shelf_life` when batch flags require it.
        'shelf_life_days' => (int)env('BELPOST_SHELF_LIFE_DAYS', 10),
        // Batch item `category`: official API allows 0, 1, 2; 1 is used for typical e-commerce merchandise.
        'item_category_ecommerce' => (int)env('BELPOST_ITEM_CATEGORY_ECOMMERCE', 1),
        'item_category' => (int)env('BELPOST_ITEM_CATEGORY', 0),
        // Приём списков отправлений в ОПС (рекомендуется Белпочтой для e-commerce тарифов).
        'postal_items_in_ops' => (bool)env('BELPOST_POSTAL_ITEMS_IN_OPS', true),
        // Max COD (BYN) per item when batch has no declared value; above this — enable «С объявленной ценностью».
        'max_cod_without_declared_value' => (float)env('BELPOST_MAX_COD_WITHOUT_DECLARED_VALUE', 238),
        // S10 series from your Belpost track range (comma-separated). Empty = any valid series.
        's10_series_prefixes' => array_values(array_filter(array_map(
            static fn (string $part): string => strtoupper(trim($part)),
            explode(',', (string)env('BELPOST_S10_SERIES_PREFIXES', 'PC')),
        ))),
        // Optional inclusive 8-digit serial bounds from the Belpost contract (e.g. 14419547–14419999).
        's10_serial_min' => ($v = env('BELPOST_S10_SERIAL_MIN')) !== null && $v !== '' ? (int)$v : null,
        's10_serial_max' => ($v = env('BELPOST_S10_SERIAL_MAX')) !== null && $v !== '' ? (int)$v : null,
        // When series is not in s10_series_prefixes, omit s10code and let Belpost assign a number.
        'omit_s10code_on_series_mismatch' => (bool)env('BELPOST_OMIT_S10CODE_ON_SERIES_MISMATCH', true),
    ],

    'cod_payment_ids' => [1, 4],

    'geo_directory' => [
        'enabled' => (bool)env('BELPOST_GEO_DIRECTORY_ENABLED', true),
    ],

];
