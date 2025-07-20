<?php

namespace App\Enums\User;

use Filament\Support\Contracts\HasLabel;

enum BanReason: int implements HasLabel
{
    case SUSPICIOUS_ORDER = 1;
    case BY_ADMIN = 2;
    case BY_ERRORS = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::SUSPICIOUS_ORDER => 'Suspicious order',
            self::BY_ADMIN => 'By admin',
            self::BY_ERRORS => 'By error',
        };
    }
}
