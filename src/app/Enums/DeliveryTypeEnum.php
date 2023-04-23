<?php

namespace App\Enums;

enum DeliveryTypeEnum: int
{
    case BELPOST = 1;

    /**
     * Получить название
     *
     * @return string
     */
    public function name(): ?string
    {
        return match ($this) {
            self::BELPOST => 'БелПочта',
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
