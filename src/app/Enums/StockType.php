<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockType: string implements HasColor, HasLabel
{
    case SHOP = 'shop';
    case STOCK = 'stock';

    public function getLabel(): string
    {
        return match ($this) {
            self::SHOP => 'Магазин',
            self::STOCK => 'Склад',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SHOP => 'success',
            self::STOCK => 'gray',
        };
    }
}
