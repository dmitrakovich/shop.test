<?php

namespace App\Enums\Promo;

use Filament\Support\Contracts\HasLabel;

enum SettingType: int implements HasLabel
{
    case CATEGORY = 1;
    case MANUFACTURER = 2;
    case PRODUCT = 3;
    case COLLECTION = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::CATEGORY => 'Категории',
            self::MANUFACTURER => 'Производители',
            self::PRODUCT => 'Товары',
            self::COLLECTION => 'Коллекции',
        };
    }
}
