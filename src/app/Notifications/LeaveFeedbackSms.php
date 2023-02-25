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
        $productLinks = [];
        $orderItems->each(function (OrderItem $item) use (&$productLinks) {
            $productLinks[] = $this->generateLink($item->product);
        });
        $productLinks = implode(", \n", $productLinks);

        return <<<SMS
        {$this->order->first_name}, к Вам приехал заказ?
        Оставьте отзыв о Вашей покупке
        {$productLinks}.
        Получите $discount% на следующий заказ, если отзыв будет с фотографией.
        SMS;
    }

    /**
     * Generate product name and short link to him
     */
    protected function generateLink(Product $product): string
    {
        $link = url($product->getUrl()) . '#review';
        $link = ShortLink::createShortLink($link);

        return $product->shortName() . ' - ' . route('short-link', $link, true);
    }
}
