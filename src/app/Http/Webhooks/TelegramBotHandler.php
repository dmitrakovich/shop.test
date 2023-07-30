<?php

namespace App\Http\Webhooks;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;

class TelegramBotHandler extends WebhookHandler
{
    protected function handleChatMessage(Stringable $text): void
    {
        // in this example, a received message is sent back to the chat
        $this->chat->html("Received: $text")->send();
    }
}
