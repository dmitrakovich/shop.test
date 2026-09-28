<?php

namespace App\Enums\Filament;

use Filament\Support\Contracts\Collapsible;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum NavGroup implements Collapsible, HasIcon, HasLabel
{
    case Docs;
    case Promo;
    case Users;
    case Products;
    case Registers;
    case Analytics;
    case OldAdminPanel;
    case Automation;
    case Management;
    case Settings;
    case Departures;
    case Seo;

    public function getLabel(): string
    {
        return match ($this) {
            self::Docs => 'Документация',
            self::Promo => 'Промо',
            self::Users => 'Клиенты',
            self::Products => 'Товары',
            self::Registers => 'Реестры',
            self::Analytics => 'Аналитика',
            self::OldAdminPanel => 'Старая админка',
            self::Automation => 'Автоматизация',
            self::Management => 'Управление',
            self::Settings => 'Настройки',
            self::Departures => 'Отправления',
            self::Seo => 'SEO',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Docs => Heroicon::OutlinedBookOpen,
            self::Promo => Heroicon::OutlinedFire,
            self::Users => Heroicon::OutlinedUserGroup,
            self::Products => Heroicon::OutlinedSquares2x2,
            self::Registers => Heroicon::OutlinedFolder,
            self::Analytics => Heroicon::OutlinedChartBar,
            self::OldAdminPanel => Heroicon::OutlinedArrowUturnLeft,
            self::Automation => Heroicon::OutlinedCog8Tooth,
            self::Management => Heroicon::OutlinedShieldCheck,
            self::Settings => Heroicon::OutlinedAdjustmentsHorizontal,
            self::Departures => Heroicon::OutlinedTruck,
            self::Seo => Heroicon::OutlinedGlobeAlt,
        };
    }

    public function isCollapsible(): bool
    {
        return true;
    }

    public function isCollapsed(): bool
    {
        return match ($this) {
            self::Docs, self::Analytics, self::Management, self::Settings => true,
            default => false,
        };
    }
}
