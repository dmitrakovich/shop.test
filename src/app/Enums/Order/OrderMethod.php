<?php

namespace App\Enums\Order;

enum OrderMethod: string
{
    case UNDEFINED = 'undefined';
    case DEFAULT = 'default';
    case ONECLICK = 'oneclick';
    case CHAT = 'chat';
    case PHONE = 'phone';
    case INSTAGRAM = 'insta';
    case VIBER = 'viber';
    case TELEGRAM = 'telegram';
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case OTHER = 'other';
}
