<?php

namespace App\Enums\Bot;

/**
 * Enum for telegram bot actions.
 */
enum TelegramBotActions: string
{
    case RESERVE_CONFIRM = 'reserveConfirm';
    case RESERVE_DISMISS = 'reserveDismiss';

    /**
     * Returns the name of the action.
     */
    public function name(): string
    {
        return match ($this) {
            self::RESERVE_CONFIRM => '✅ Отложено',
            self::RESERVE_DISMISS => '❌ Нет в наличии',
        };
    }
}
