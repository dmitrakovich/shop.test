<?php

namespace App\Models\Enum;

class OrderMethod extends EnumAbstract
{
    const DEFAULT = 'default';
    const ONECLICK = 'oneclick';
    const CHAT = 'chat';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const VIBER = 'viber';
    const TELEGRAM = 'telegram';
    const WHATSAPP = 'whatsapp';
    const INSTAGRAM = 'insta';
    const OTHER = 'other';

    /**
     * Generate key => rus value for select box
     *
     * @return array
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
     * @param string $orderMethod
     * @return array utm sources:
     * - utm_source
     * - utm_medium
     * - utm_campaign
     */
    public static function getUtmSources(string $orderMethod): array
    {
        switch ($orderMethod) {
            default:
            case self::OTHER:
                return ['none', 'none', 'manager'];

            case self::INSTAGRAM:
                return ['instagram', 'social', 'manager'];

            case self::PHONE:
                return ['phone', 'offline', 'manager'];

            case self::CHAT:
                return ['site', 'chat', 'manager'];

            case self::EMAIL:
                return ['email', 'email', 'manager'];

            case self::VIBER:
                return ['viber', 'messenger', 'manager'];

            case self::TELEGRAM:
                return ['telegram', 'messenger', 'manager'];

            case self::WHATSAPP:
                return ['whatsapp', 'messenger', 'manager'];
        }

        // PHP > 8
        // return match ($orderMethod) {
        //     self::OTHER => ['none', 'none', 'manager'],
        //     self::INSTAGRAM => ['instagram', 'social', 'manager'],
        //     self::PHONE => ['phone', 'offline', 'manager'],
        //     self::CHAT => ['site', 'chat', 'manager'],
        //     self::EMAIL => ['email', 'email', 'manager'],
        //     self::VIBER => ['viber', 'messenger', 'manager'],
        //     self::TELEGRAM => ['telegram', 'messenger', 'manager'],
        //     self::WHATSAPP => ['whatsapp', 'messenger', 'manager'],
        // };
    }
}
