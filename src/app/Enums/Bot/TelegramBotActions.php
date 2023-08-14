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

    /**
     * Returns the name of the action.
     */
    public function name(): string
    {
        return match ($this) {
            self::RESERVE_CONFIRM => '✅ Отложено',
            self::COLLECT_CONFIRM => '✅ Собран',
            self::OUT_OF_STOCK => '❌ Нет в наличии',
        };
    }
}
