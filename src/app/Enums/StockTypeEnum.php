<?php

namespace App\Enums;

enum StockTypeEnum: string
{
    case SHOP = 'shop';
    case STOCK = 'stock';


    /**
     * Получить название
     *
     * @return string
     */
    public function name(): ?string
    {
        return match ($this) {
            self::SHOP => 'Магазин',
            self::STOCK => 'Склад',
        };
    }

    /**
     * Получить список
     */
    public static function list(): array
    {
        $result = [];
        $cases = self::cases();
        foreach ($cases as $case) {
            $result[$case->value] = $case->name();
        }

        return $result;
    }
}
