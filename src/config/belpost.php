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
        'negotiated_rate' => (bool)env('BELPOST_NEGOTIATED_RATE', false),
        // For e-commerce parcel types API accepts only 1, 2, 5 (not 0).
        'notification' => (int)env('BELPOST_NOTIFICATION', BelpostNotification::Electronic->value),
        'fallback_recipient_email' => env('BELPOST_FALLBACK_RECIPIENT_EMAIL'),
        'sender_phone' => env('BELPOST_SENDER_PHONE', config('app.phone')),
        'sender_email' => env('BELPOST_SENDER_EMAIL', config('app.email')),
    ],

    'cod_payment_ids' => [1, 4],

    'geo_directory' => [
        'enabled' => (bool)env('BELPOST_GEO_DIRECTORY_ENABLED', true),
    ],

];
