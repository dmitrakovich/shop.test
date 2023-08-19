<?php

namespace App\Http\Webhooks;

use App\Enums\Bot\TelegramBotActions;
use App\Services\Order\OrderItemInventoryService;
use DefStudio\Telegraph\Handlers\WebhookHandler;
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
     * !!! Stub
     */
    public function pickupList() : void
    {
        $this->reply('выводит список моделей (без фото)
            Забор на хх.хх.хххх магазин ХХХХХХ
            - бренд артикул (код товара), размер
            - бренд артикул (код товара), размер
            - бренд артикул (код товара), размер');

        // Если в личном чате, то сразу ответ. Если в групповом чате в следующем сообщении “Выберите магазин” и кнопки с адресами магазинов.
    }
}
