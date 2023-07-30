<?php

namespace App\Http\Webhooks;

use App\Enums\Bot\TelegramBotActions;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;

class TelegramBotHandler extends WebhookHandler
{
    /**
     * TelegramBotHandler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        //todo: add service ...
    }

    /**
     * Handle incoming chat message.
     */
    protected function handleChatMessage(Stringable $text): void
    {
        // .. do nothing
    }

    /**
     * Handle reserve confirmation action.
     */
    public function reserveConfirm(): void
    {

        //todo: handle ...

        $this->actionReply(TelegramBotActions::RESERVE_CONFIRM->name());
    }

    /**
     * Handle reserve dismissal action
     */
    public function reserveDismiss(): void
    {
        //todo: handle ...


        $this->actionReply(TelegramBotActions::RESERVE_DISMISS->name());
    }

    /**
     * Handle an action reply from the user.
     *
     * This method is responsible for processing an action (typically a button press)
     * performed by the user in response to a previous message. It removes any existing
     * inline keyboards from the message, sends a webhook with the text of the pressed
     * button, and updates the original message by appending the text of the pressed button
     * as a confirmation of which action was taken.
     */
    protected function actionReply(string $text): void
    {
        // $this->deleteKeyboard(); // editCaption enough
        $message = $this->callbackQuery->message()->text() . PHP_EOL . $text;
        $this->chat->editCaption($this->messageId)->message($message)->send();
        $this->reply($text);
    }

    /**
     * Command for saving the chat to the database.
     */
    public function addStockChat(): void
    {
        $this->reply('Чат добавлен в список');
    }
}
