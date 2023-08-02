<?php

namespace App\Models\Bots\Telegram;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * Class TelegramChat
 * @package App\Models\Bots\Telegram
 *
 * @property int $id
 * @property int $chat_id
 * @property string $name
 * @property int $telegram_bot_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read TelegramBot $bot
 */
class TelegramChat extends TelegraphChat
{
    use Notifiable;

    /**
     * Get the bot that owns the chat.
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class, 'telegram_bot_id');
    }
}
