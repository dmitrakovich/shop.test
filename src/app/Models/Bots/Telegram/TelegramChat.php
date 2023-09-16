<?php

namespace App\Models\Bots\Telegram;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Class TelegramChat
 *
 * @property int $id
 * @property int $chat_id
 * @property string $name
 * @property int $telegram_bot_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read TelegramBot $bot
 */
class TelegramChat extends TelegraphChat
{
    use Notifiable;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'offline_notifications_pause_until' => 'datetime',
    ];

    /**
     * Get the bot that owns the chat.
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class, 'telegram_bot_id');
    }
}
