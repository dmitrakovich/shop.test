<?php

namespace App\Enums\Bot;

/**
 * Enum for telegram bot actions.
 */
enum TelegramBotActions: string
{
    case RESERVE_CONFIRM = 'reserveConfirm';
    case COLLECT_CONFIRM = 'collectConfirm';
    case OUT_OF_STOCK = 'outOfStock';
    case PICKUP_LIST = 'pickupListForChat';

    /**
     * Returns the name of the action.
     */
    public function name(): string
    {
        return match ($this) {
            self::RESERVE_CONFIRM => '✅ Отложено',
            self::COLLECT_CONFIRM => '✅ Собран',
            self::OUT_OF_STOCK => '❌ Нет в наличии',
            self::PICKUP_LIST => 'Список товаров для отгрузки',
        };
    }
}
