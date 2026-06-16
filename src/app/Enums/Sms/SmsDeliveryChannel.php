<?php

namespace App\Enums\Sms;

use Filament\Support\Contracts\HasLabel;

enum SmsDeliveryChannel: string implements HasLabel
{
    case Viber = 'viber';
    case Sms = 'sms';

    public function getLabel(): string
    {
        return match ($this) {
            self::Viber => 'Viber',
            self::Sms => 'SMS',
        };
    }
}
