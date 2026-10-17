<?php

namespace App\Enums\Product;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * What the automatic availability update did with a product.
 */
enum InventoryLogAction: string implements HasColor, HasLabel
{
    case Restore = 'RESTORE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';

    public function getLabel(): string
    {
        return match ($this) {
            self::Restore => 'Опубликован',
            self::Update => 'Изменены размеры',
            self::Delete => 'Снят с публикации',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Restore => 'success',
            self::Update => 'info',
            self::Delete => 'danger',
        };
    }
}
