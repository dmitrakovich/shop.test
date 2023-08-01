<?php

namespace App\Notifications;

use App\Enums\Bot\TelegramBotActions;
use App\Models\Bots\Telegram\TelegraphChat;
use App\Models\Orders\OrderItem;
use DefStudio\Telegraph\Client\TelegraphResponse;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Telegraph;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderItemInventoryNotification extends Notification //!!! implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private OrderItem $orderItem)
    {
    }

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
     * Get the mail representation of the notification.
     */
    public function toTelegram(TelegraphChat $chat): TelegraphResponse
    {
        $product = $this->orderItem->product;
        $size = $this->orderItem->size;
        $stock = $this->orderItem->invertoryNotification->stock;
        $isReserveAction = $this->orderItem->status_key === 'new';

        $message = <<<MSG
        <b>{$this->getActionTitleByOrderItemStatus()}</b>
        {$product->brand->name} {$product->sku}, размер: {$size->name}
        {$stock->name} {$stock->address}
        MSG;

        return $chat->message($message)
            ->photo($product->getFirstMediaPath())
            ->when($isReserveAction, $this->getReserveKeyboard())
            ->send();
    }

    private function getActionTitleByOrderItemStatus(): string
    {
        return match ($this->orderItem->status_key) {
            'new' => 'Отложить модель',
            'canceled' => 'Убрать с отложенного',
            'confirmed' => 'Подтверждено на забор из магазина',
            'complete', 'installment' => 'Убрать с наличия',
            default => throw new \Exception('Attempt to send message on unknown status'),
        };
    }

    private function getReserveKeyboard(): \Closure
    {
        return function (Telegraph $telegraph) {
            return $telegraph->keyboard(Keyboard::make()->row([
                Button::make(TelegramBotActions::RESERVE_CONFIRM->name())
                    ->action(TelegramBotActions::RESERVE_CONFIRM->value)
                    ->param('id', $this->orderItem->invertoryNotification->id),
                Button::make(TelegramBotActions::RESERVE_DISMISS->name())
                    ->action(TelegramBotActions::RESERVE_DISMISS->value)
                    ->param('id', $this->orderItem->invertoryNotification->id),
            ]));
        };
    }
}
