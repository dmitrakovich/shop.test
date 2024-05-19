<?php

namespace App\Enums\Promo;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;

/**
 * Enum representing sorting options for a cart sale.
 */
enum CartSortForSale: int implements HasLabel
{
    case PRICE_ASC = 1;
    case PRICE_DESC = 2;

    /**
     * Get the label for the sorting option.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::PRICE_ASC => 'Цена по возрастанию',
            self::PRICE_DESC => 'Цена по убыванию',
        };
    }

    /**
     * Apply the sorting option to a collection of products.
     */
    public function apply(Collection $products): Collection
    {
        return match ($this) {
            self::PRICE_ASC => $products->sortBy('price'),
            self::PRICE_DESC => $products->sortByDesc('price'),
        };
    }
}
