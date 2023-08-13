<?php

namespace App\Enums\Product;

/**
 * Enum for product labels.
 */
enum ProductLabels: int
{
    case NONE = 0;
    case HIT = 1;
    case LIQUIDATION = 2;
    case DO_NOT_PUBLISH = 3;
    case DO_NOT_UPDATE = 4;

    /**
     * Returns the name of the label.
     */
    public function name(): string
    {
        return match ($this) {
            self::NONE => 'Нет',
            self::HIT => 'Хит',
            self::LIQUIDATION => 'Ликвидация',
            self::DO_NOT_PUBLISH => 'Не публиковать',
            self::DO_NOT_UPDATE => 'Не обновлять',
        };
    }

    /**
     * Returns an array with the names of all labels.
     */
    public static function list(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->name();
        }

        return $result;
    }
}
