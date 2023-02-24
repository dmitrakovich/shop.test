<?php

namespace App\Enums\Payment;

enum OnlinePaymentMethodEnum: int
{
    case ERIP = 1;
    case YANDEX = 2;

    /**
     * Получить название
     *
     * @return string
     */
    public function name(): ?string
    {
        return match ($this) {
            self::ERIP => 'ЕРИП',
            self::YANDEX => 'ЮКасса',
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

    /**
     * Получить enum по value
     *
     * @param  ?int  $value - значение
     * @return OnlinePaymentMethodEnum
     */
    public static function enumByValue(?int $value): ?self
    {
        $enum = self::tryFrom($value);

        return $enum ? $enum : null;
    }
}
