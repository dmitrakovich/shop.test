<?php

namespace App\Models\Enum;

/**
 * @deprecated
 * @see \App\Enums\Order\OrderMethod
 */
class OrderMethod
{
    final const string UNDEFINED = 'undefined';

    final const string DEFAULT = 'default';

    final const string ONECLICK = 'oneclick';

    final const string CHAT = 'chat';

    final const string PHONE = 'phone';

    final const string INSTAGRAM = 'insta';

    final const string VIBER = 'viber';

    final const string TELEGRAM = 'telegram';

    final const string WHATSAPP = 'whatsapp';

    final const string EMAIL = 'email';

    final const string OTHER = 'other';

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
     *                       - utm_source
     *                       - utm_medium
     *                       - utm_campaign
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

    public static function getValues(): array
    {
        $class = new \ReflectionClass(static::class);

        return array_values($class->getConstants());
    }
}
