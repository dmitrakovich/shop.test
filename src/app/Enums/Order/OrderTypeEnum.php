<?php

namespace App\Enums\Order;

enum OrderTypeEnum: string
{
    case MOBILE = 'mobile';
    case DESKTOP = 'desktop';
    case MANAGER = 'manager';

    /**
     * Retrieves the name of the object.
     *
     * @return string|null The name of the object.
     */
    public function name(): ?string
    {
        return match ($this) {
            self::MOBILE => 'Мобильная версия',
            self::DESKTOP => 'Десктопная версия',
            self::MANAGER => 'Заказ внесен вручную',
        };
    }
}
