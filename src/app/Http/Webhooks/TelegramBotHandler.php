<?php

namespace App\Http\Webhooks;

use App\Enums\Bot\TelegramBotActions;
use App\Models\Stock;
use App\Services\Order\OrderItemInventoryService;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;

class TelegramBotHandler extends WebhookHandler
{
    /**
     * The service responsible for managing order item inventory.
     */
    private OrderItemInventoryService $inventoryService;

    /**
     * TelegramBotHandler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->inventoryService = app(OrderItemInventoryService::class);
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
        $this->inventoryService->reserveItem($this->data->get('id'));

        $this->actionReply(TelegramBotActions::RESERVE_CONFIRM->name());
    }

    /**
     * Handle collect confirmation action.
     */
    public function collectConfirm(): void
    {
        $this->inventoryService->collectItem($this->data->get('id'));

        $this->actionReply(TelegramBotActions::COLLECT_CONFIRM->name());
    }

    /**
     * Handle reserve dismissal action
     */
    public function outOfStock(): void
    {
        $this->inventoryService->outOfStock($this->data->get('id'));

        $this->actionReply(TelegramBotActions::OUT_OF_STOCK->name());
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
        $message = $this->callbackQuery->message()->text() . "\n\n$text";
        $this->chat->editCaption($this->messageId)->message($message)->send();
        $this->reply($text);
    }

    /**
     * Command for saving the chat to the database.
     */
    public function start(): void
    {
        $this->reply('Чат добавлен в список');
    }

    /**
     * Alias for method `start`
     */
    public function addStockChat(): void
    {
        $this->start();
    }

    /**
     * Send a pickup list to the appropriate chat or present store selection buttons.
     */
    public function pickupList(): void
    {
        if ($this->isPrivateChat()) {
            $this->pickupListForChat($this->chat->chat_id);

            return;
        }

        $buttons = [];
        Stock::query()->with('privateChat:id,chat_id')
            ->where('group_chat_id', $this->chat->chat_id)
            ->each(function (Stock $stock) use (&$buttons) {
                $buttons[] = Button::make("{$stock->name} {$stock->address}")
                    ->action(TelegramBotActions::PICKUP_LIST->value)
                    ->param('chat_id', $stock->privateChat->chat_id);
            });

        $this->chat->message('Выберите магазин:')
            ->keyboard(Keyboard::make()->buttons($buttons))
            ->send();
    }

    /**
     * Send a pickup list to a specified chat or the current chat.
     */
    public function pickupListForChat(?int $chatId = null): void
    {
        $this->replyWebhook();
        $chatId ??= $this->data->get('chat_id');
        $pickupList = $this->inventoryService->pickupList($chatId);
        $this->chat->html($pickupList)->send();
    }

    /**
     * Check if the current chat is a private chat.
     */
    private function isPrivateChat(): bool
    {
        $telegramChat = $this->message?->chat() ?? $this->callbackQuery?->message()?->chat();

        return $telegramChat->type() === 'private';
    }

    /**
     * Reply to a webhook with a specified message if applicable.
     */
    private function replyWebhook(string $message = 'ok'): void
    {
        if (isset($this->callbackQueryId)) {
            $this->reply($message);
        }
    }
}
