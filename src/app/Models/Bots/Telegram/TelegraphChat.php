<?php

namespace App\Models\Bots\Telegram;

use DefStudio\Telegraph\Models\TelegraphChat as BaseTelegraphChat;
use Illuminate\Notifications\Notifiable;

class TelegraphChat extends BaseTelegraphChat
{
    use Notifiable;
}
