<?php

namespace App\Models\Bots\Telegram;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Notifications\Notifiable;

class TelegramChat extends TelegraphChat
{
    use Notifiable;
}
