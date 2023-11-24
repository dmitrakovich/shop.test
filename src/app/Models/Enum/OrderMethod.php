<?php

namespace App\Models\Enum;

/**
 * @deprecated
 * @see \App\Enums\Order\OrderMethods
 */
class OrderMethod implements Enum
{
    use EnumTrait;

    final const UNDEFINED = 'undefined';

    final const DEFAULT = 'default';

    final const ONECLICK = 'oneclick';

    final const CHAT = 'chat';

    final const PHONE = 'phone';

    final const INSTAGRAM = 'insta';

    final const VIBER = 'viber';

    final const TELEGRAM = 'telegram';

    final const WHATSAPP = 'whatsapp';

    final const EMAIL = 'email';

    final const OTHER = 'other';

    /**
     * Generate key => rus value for select box
     */
    public static function getOptionsForSelect(): array
    {
        return [
            self::UNDEFINED => 'Неопределенный (по умолчанию)',
            // self::DEFAULT => 'через корзину',
            self::ONECLICK => 'в один клик',
            self::CHAT => 'через чат сайта',
            self::PHONE => 'по телефону',
            self::INSTAGRAM => 'через instagram',
            self::VIBER => 'через viber',
            self::TELEGRAM => 'через telegram',
            self::WHATSAPP => 'через whatsapp',
            self::EMAIL => 'по email',
            // self::OTHER => 'другое',
        ];
    }

    /**
     * Return umt sources for selected order method
     *
     * @return array<string> utm sources:
     * - utm_source
     * - utm_medium
     * - utm_campaign
     */
    public static function getUtmSources(string $orderMethod): array
    {
        return match ($orderMethod) {
            self::INSTAGRAM => ['instagram', 'social', 'manager'],
            self::PHONE => ['phone', 'offline', 'manager'],
            self::CHAT => ['site', 'chat', 'manager'],
            self::EMAIL => ['email', 'email', 'manager'],
            self::VIBER => ['viber', 'messenger', 'manager'],
            self::TELEGRAM => ['telegram', 'messenger', 'manager'],
            self::WHATSAPP => ['whatsapp', 'messenger', 'manager'],
            default => ['none', 'none', 'manager'],
        };
    }
}
