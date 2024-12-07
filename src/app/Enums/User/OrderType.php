<?php

namespace App\Enums\User;

use Filament\Support\Contracts\HasLabel;

enum OrderType: int implements HasLabel
{
    case ONLINE = 1;
    case OFFLINE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE => 'Online',
            self::OFFLINE => 'Магазин',
        };
    }
}
