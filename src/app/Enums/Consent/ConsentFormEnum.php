<?php

namespace App\Enums\Consent;

enum ConsentFormEnum: int
{
    case Unknown = 0;
    case Order = 1;
    case OneClick = 2;
    case Feedback = 3;
    case Login = 4;

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Неизвестно',
            self::Order => 'Заказ',
            self::OneClick => 'Заказ в 1 клик',
            self::Feedback => 'Отзыв',
            self::Login => 'Вход',
        };
    }
}
