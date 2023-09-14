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
 * @property Carbon $offline_notifications_pause_until
 * @property Carbon $created_at
 * @property Carbon $updated_at
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

    /**
     * Set a pause for offline order notifications and return the new pause time.
     */
    public function setOfflineNotificationsPause(int $minutes): Carbon
    {
        $newPauseUntil = now()->addMinutes($minutes);

        $this->offline_notifications_pause_until = $newPauseUntil;
        $this->save();

        return $newPauseUntil;
    }

    /**
     * Check if offline order notifications are paused relative to the current time.
     */
    public function areOfflineNotificationsPaused(): bool
    {
        return $this->offline_notifications_pause_until !== null
            && $this->offline_notifications_pause_until->isFuture();
    }
}
