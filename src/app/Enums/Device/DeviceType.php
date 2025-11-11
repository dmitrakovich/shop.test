<?php

namespace App\Enums\Device;

use Filament\Support\Contracts\HasLabel;

enum DeviceType: int implements HasLabel
{
    case MOBILE = 1;
    case DESKTOP = 2;
    case CONSOLE = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::MOBILE => 'Mobile',
            self::DESKTOP => 'Desktop',
            self::CONSOLE => 'Console',
        };
    }
}
