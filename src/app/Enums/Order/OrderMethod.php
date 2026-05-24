<?php

namespace App\Enums\Order;

use Filament\Support\Contracts\HasLabel;

enum OrderMethod: string implements HasLabel
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

    public function getLabel(): string
    {
        return match ($this) {
            self::UNDEFINED => 'Неопределенный (по умолчанию)',
            self::DEFAULT => 'через корзину',
            self::ONECLICK => 'в один клик',
            self::CHAT => 'через чат сайта',
            self::PHONE => 'по телефону',
            self::INSTAGRAM => 'через instagram',
            self::VIBER => 'через viber',
            self::TELEGRAM => 'через telegram',
            self::WHATSAPP => 'через whatsapp',
            self::EMAIL => 'по email',
            self::OTHER => 'другое',
        };
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public function utmSources(): array
    {
        return match ($this) {
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

    /**
     * @return array<string, string>
     */
    public static function getOptionsForSelect(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            if (in_array($case, [self::DEFAULT, self::OTHER], true)) {
                continue;
            }

            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    /**
     * Order methods available in the short-link generator.
     *
     * @return array<string, string>
     */
    public static function shortLinkSelectOptions(): array
    {
        $sources = [
            self::ONECLICK,
            self::CHAT,
            self::INSTAGRAM,
            self::VIBER,
            self::TELEGRAM,
            self::WHATSAPP,
            self::EMAIL,
        ];

        $options = [];

        foreach ($sources as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}
