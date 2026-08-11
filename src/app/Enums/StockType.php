<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StockType: string implements HasLabel
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
}
