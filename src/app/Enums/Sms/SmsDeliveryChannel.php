<?php

namespace App\Enums\Sms;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Colors\Color;

enum SmsDeliveryChannel: string implements HasColor, HasLabel
{
    case Viber = 'viber';
    case Sms = 'sms';

    public static function resolve(?string $value): ?self
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return match (strtolower(trim($value))) {
            'viber' => self::Viber,
            'sms', 'smsbatch' => self::Sms,
            default => null,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Viber => 'Viber',
            self::Sms => 'SMS',
        };
    }

    public function getColor(): string | array
    {
        return match ($this) {
            self::Viber => Color::Violet,
            self::Sms => 'info',
        };
    }
}
