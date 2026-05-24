<?php

namespace App\Enums\Sms;

use Filament\Support\Contracts\HasLabel;

enum SmsRoute: string implements HasLabel
{
    case Sms = 'sms';
    case Viber = 'viber';
    case ViberWithSmsFallback = 'viber(60)-sms';

    public static function default(): self
    {
        return self::ViberWithSmsFallback;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Sms => 'SMS',
            self::Viber => 'Viber',
            self::ViberWithSmsFallback => 'Vb/SMS',
        };
    }
}
