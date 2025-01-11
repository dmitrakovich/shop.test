<?php

namespace App\Enums;

enum Cookie: string
{
    case DEVICE_ID = 'device_id';
    case YANDEX_ID = '_ym_uid';
    case GOOGLE_ID = '_ga';
    case OLD_CART_ID = 'cart_token';
}
