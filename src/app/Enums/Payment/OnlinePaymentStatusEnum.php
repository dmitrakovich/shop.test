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
     */
    public function name(): string
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
