<?php

namespace App\Enums\Product;

use Filament\Support\Contracts\HasLabel;

/**
 * Enum for product labels.
 */
enum ProductLabel: int implements HasLabel
{
    case HIT = 1;
    case LIQUIDATION = 2;
    case DO_NOT_PUBLISH = 3;
    case DO_NOT_UPDATE = 4;

    /**
     * Returns the name of the label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::HIT => 'Хит',
            self::LIQUIDATION => 'Ликвидация',
            self::DO_NOT_PUBLISH => 'Не публиковать',
            self::DO_NOT_UPDATE => 'Не обновлять',
        };
    }

    /**
     * Returns an array with the names of all labels.
     *
     * @return array<int, string>
     */
    public static function list(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->getLabel();
        }

        return $result;
    }

    public function isNotPublished(): bool
    {
        return $this === self::DO_NOT_PUBLISH;
    }
}
