<?php

namespace App\Notifications;

use App\Models\Bots\Telegram\TelegramChat;
use DefStudio\Telegraph\Client\TelegraphResponse;
use Illuminate\Notifications\Notification;

class TelegraphChannel
{
    /**
     * Send the given notification.
     */
    public function send(TelegramChat $chat, Notification $notification): TelegraphResponse
    {
        return $notification->toTelegram($chat)->throw();
    }
}
