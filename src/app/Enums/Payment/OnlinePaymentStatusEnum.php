<?php

namespace App\Enums\Payment;

enum OnlinePaymentStatusEnum: int
{
    case PENDING = 1;
    case WAITING_FOR_CAPTURE = 2;
    case SUCCEEDED = 3;
    case CANCELED = 4;

    /**
     * Получить название
     *
     * @return string
     */
    public function name(): ?string
    {
        return match ($this) {
            self::PENDING => 'В обработке',
            self::WAITING_FOR_CAPTURE => 'Ожидает подтверждения',
            self::SUCCEEDED => 'Оплачен',
            self::CANCELED => 'Отменен',
        };
    }

    /**
     * Получить список
     *
     * @return array
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
     * @return OnlinePaymentStatusEnum
     */
    public static function enumByValue(?int $value): self
    {
        $enum = self::tryFrom($value);

        return $enum ? $enum : self::PENDING;
    }
}
