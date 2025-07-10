<?php

namespace App\Models\Bots\Telegram;

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $token
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Bots\Telegram\TelegramChat[] $chats
 */
class TelegramBot extends TelegraphBot
{
    /**
     * Get the chats associated with the Telegram bot.
     */
    public function chats(): HasMany
    {
        return $this->hasMany(TelegramChat::class);
    }
}
