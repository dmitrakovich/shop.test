<?php

namespace App\Enums\Belpost;

use Filament\Support\Contracts\HasLabel;

enum BelpostDirection: string implements HasLabel
{
    case Internal = 'internal';
    case Cis = 'CIS';
    case International = 'international';

    public function getLabel(): string
    {
        return match ($this) {
            self::Internal => 'По РБ',
            self::Cis => 'СНГ',
            self::International => 'Международное',
        };
    }
}
