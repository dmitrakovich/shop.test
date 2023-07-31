<?php

namespace App\Notifications;

use App\Models\Bots\Telegram\TelegraphChat;
use DefStudio\Telegraph\Client\TelegraphResponse;
use Illuminate\Notifications\Notification;

class TelegraphChannel
{
    /**
     * Send the given notification.
     */
    public function send(TelegraphChat $chat, Notification $notification): TelegraphResponse
    {
        return $notification->toTelegram($chat);
    }
}
