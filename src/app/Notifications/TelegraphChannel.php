<?php

namespace App\Notifications;

use App\Models\Bots\Telegram\TelegramChat;
use DefStudio\Telegraph\Client\TelegraphResponse;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class TelegraphChannel
{
    /**
     * Send the given notification.
     */
    public function send(TelegramChat $chat, Notification $notification): TelegraphResponse
    {
        if (!App::environment('production')) {
            Telegraph::fake();
        }

        return $notification->toTelegram($chat)->throw();
    }
}
