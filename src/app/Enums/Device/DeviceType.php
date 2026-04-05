<?php

namespace App\Enums\Device;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum DeviceType: int implements HasColor, HasIcon, HasLabel
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

    public function getIcon(): \BackedEnum
    {
        return match ($this) {
            self::MOBILE => Heroicon::DevicePhoneMobile,
            default => Heroicon::ComputerDesktop,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MOBILE => 'info',
            default => 'primary',
        };
    }

    public function getTooltip(): string
    {
        return match ($this) {
            self::MOBILE => 'Мобильное устройство',
            default => 'Настольный компьютер',
        };
    }
}
