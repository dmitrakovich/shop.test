<?php

namespace App\Enums\Payment;

enum OnlinePaymentMethodEnum: int
{
    case ERIP = 1;
    case YANDEX = 2;
    case COD = 3;

    /**
     * Получить название
     */
    public function name(): string
    {
        return match ($this) {
            self::ERIP => 'ЕРИП',
            self::YANDEX => 'ЮКасса',
            self::COD => 'Наложенный платеж',
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
