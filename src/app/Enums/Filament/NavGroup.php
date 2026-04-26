<?php

namespace App\Enums\Filament;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum NavGroup implements HasIcon, HasLabel
{
    case Promo;
    case Users;
    case Products;
    case Registers;
    case OldAdminPanel;
    case Automation;
    case Management;

    public function getLabel(): string
    {
        return match ($this) {
            self::Promo => 'Промо',
            self::Users => 'Клиенты',
            self::Products => 'Товары',
            self::Registers => 'Реестры',
            self::OldAdminPanel => 'Старая админка',
            self::Automation => 'Автоматизация',
            self::Management => 'Управление',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Promo => Heroicon::OutlinedFire,
            self::Users => Heroicon::OutlinedUserGroup,
            self::Products => Heroicon::OutlinedSquares2x2,
            self::Registers => Heroicon::OutlinedFolder,
            self::OldAdminPanel => Heroicon::OutlinedArrowUturnLeft,
            self::Automation => Heroicon::OutlinedCog8Tooth,
            self::Management => Heroicon::OutlinedCog6Tooth,
        };
    }
}
