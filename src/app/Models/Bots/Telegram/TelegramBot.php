<?php

namespace App\Models\Bots\Telegram;

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TelegramBot
 * @package App\Models\Bots\Telegram
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<TelegramChat> $chats
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
