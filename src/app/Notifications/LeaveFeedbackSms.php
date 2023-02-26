<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\Config;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\ShortLink;

class LeaveFeedbackSms extends AbstractSmsTraffic
{
    /**
     * mailing ID
     */
    public ?int $mailingId = 2;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private Order $order)
    {
    }

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $discount = Config::findCacheable('feedback')['discount'];
        $orderItems = $this->order->items->filter(function (OrderItem $item) {
            return $item->isCompleted();
        });

        $link = $orderItems->count() > 1
            ? $this->orderLink()
            : $this->productLink($orderItems->first()->product);

        return <<<SMS
        {$this->order->first_name}, к Вам приехал заказ?
        Оставьте отзыв о Вашей покупке {$link}.
        Получите $discount% на следующий заказ, если отзыв будет с фотографией.
        SMS;
    }

    /**
     * Generate link to user's orders
     */
    protected function orderLink(): string
    {
        $link = route('orders.index', $this->utms(), true);

        return 'по полученным товарам - ' . $this->reduceLink($link);
    }

    /**
     * Generate product name and short link to him
     */
    protected function productLink(Product $product): string
    {
        $link = url($product->getUrl()) . '?' . http_build_query($this->utms()) . '#review';

        return $product->shortName() . ' - ' . $this->reduceLink($link);
    }

    /**
     * Utm params list for link
     */
    protected function utms(): array
    {
        return [
            'utm_source' => 'viber',
            'utm_medium' => 'messenger',
            'utm_campaign' => 'auto',
            'utm_content' => 'review',
            'utm_term' => date('ymd'),
        ];
    }

    /**
     * Generate short link
     */
    protected function reduceLink(string $link): string
    {
        return route('short-link', ShortLink::createShortLink($link), true);
    }
}
