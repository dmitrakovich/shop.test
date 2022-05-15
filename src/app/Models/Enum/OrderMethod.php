<?php

namespace App\Models\Enum;

class OrderMethod implements Enum
{
    use EnumTrait;

    final const DEFAULT = 'default';
    final const ONECLICK = 'oneclick';
    final const CHAT = 'chat';
    final const PHONE = 'phone';
    final const EMAIL = 'email';
    final const VIBER = 'viber';
    final const TELEGRAM = 'telegram';
    final const WHATSAPP = 'whatsapp';
    final const INSTAGRAM = 'insta';
    final const OTHER = 'other';

    /**
     * Generate key => rus value for select box
     */
    public static function getOptionsForSelect(): array
    {
        return [
            OrderMethod::DEFAULT => 'через корзину',
            OrderMethod::ONECLICK => 'в один клик',
            OrderMethod::CHAT => 'через чат сайта',
            OrderMethod::PHONE => 'по телефону',
            OrderMethod::EMAIL => 'по email',
            OrderMethod::VIBER => 'через viber',
            OrderMethod::TELEGRAM => 'через telegram',
            OrderMethod::WHATSAPP => 'через whatsapp',
            OrderMethod::INSTAGRAM => 'через instagram',
            OrderMethod::OTHER => 'другое',
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
