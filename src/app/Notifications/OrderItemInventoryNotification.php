<?php

namespace App\Notifications;

use App\Enums\Bot\TelegramBotActions;
use App\Enums\LogCategory;
use App\Enums\Order\OrderItemStatus;
use App\Models\Bots\Telegram\TelegramChat;
use App\Models\Orders\OrderItem;
use DefStudio\Telegraph\Client\TelegraphResponse;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Telegraph;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderItemInventoryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private OrderItem $orderItem) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegraphChannel::class];
    }

    /**
     * Get the notification's representation for Telegram.
     */
    public function toTelegram(TelegramChat $chat): TelegraphResponse
    {
        $product = $this->orderItem->product;
        $size = $this->orderItem->size;
        $stock = $this->orderItem->inventoryNotification->stock;
        $isConfirmAction = $this->orderItem->status->isConfirmed();

        $message = <<<MSG
        <b>{$this->getActionTitleByOrderItemStatus()}</b>
        {$product->brand->name} {$product->sku} ({$product->id}) р. {$size->name}
        {$this->getOrderInfo()}
        {$stock->name} {$stock->address}
        MSG;

        Log::channel(LogCategory::Debug->value)->debug($message);

        return $chat->message($message)
            ->photo($product->getFirstCatalogMediaUrl())
            ->when($isConfirmAction, $this->getConfirmKeyboard())
            ->send();
    }

    /**
     * Get the action title based on the order item status.
     *
     * @throws \Exception When attempting to send a message on an unknown status.
     */
    private function getActionTitleByOrderItemStatus(): string
    {
        return match ($this->orderItem->status) {
            OrderItemStatus::NEW => 'Отложить модель',
            OrderItemStatus::CANCELED => 'Убрать с отложенного',
            OrderItemStatus::CONFIRMED => 'Подтверждено на забор из магазина',
            OrderItemStatus::PICKUP => 'Забрано из магазина',
            OrderItemStatus::COMPLETED, OrderItemStatus::INSTALLMENT => 'Убрать с наличия',
            OrderItemStatus::RETURN => 'Возврат по срокам',
            OrderItemStatus::RETURN_FITTING => 'Возврат после примерки',
            default => throw new \Exception('Attempt to send message on unknown status'),
        };
    }

    /**
     * Get information about the order.
     */
    private function getOrderInfo(): string
    {
        $order = $this->orderItem->order;

        return $order ? "Номер заказа: {$order->id}" : '🏪 продажа с магазина';
    }

    /**
     * Get the closure for the confirm keyboard.
     */
    private function getConfirmKeyboard(): \Closure
    {
        return function (Telegraph $telegraph) {
            return $telegraph->keyboard(Keyboard::make()->row([
                Button::make(TelegramBotActions::COLLECT_CONFIRM->name())
                    ->action(TelegramBotActions::COLLECT_CONFIRM->value)
                    ->param('id', $this->orderItem->inventoryNotification->id),
                Button::make(TelegramBotActions::OUT_OF_STOCK->name())
                    ->action(TelegramBotActions::OUT_OF_STOCK->value)
                    ->param('id', $this->orderItem->inventoryNotification->id),
            ]));
        };
    }
}
