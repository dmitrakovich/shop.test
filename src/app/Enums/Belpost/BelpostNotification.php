<?php

namespace App\Enums\Belpost;

use Filament\Support\Contracts\HasLabel;

enum BelpostNotification: int implements HasLabel
{
    case None = 0;
    case Simple = 1;
    case Registered = 2;
    case Electronic = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'Без уведомления',
            self::Simple => 'Простое',
            self::Registered => 'Заказное',
            self::Electronic => 'Электронное',
        };
    }

    /**
     * Values accepted by Belpost for e-commerce parcel types.
     *
     * @return list<int>
     */
    public static function ecommerceValues(): array
    {
        return [
            self::Simple->value,
            self::Registered->value,
            self::Electronic->value,
        ];
    }

    public static function tryFromConfigured(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return self::tryFrom((int)$value);
    }
}
