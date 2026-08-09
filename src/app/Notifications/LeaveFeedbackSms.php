<?php

namespace App\Notifications;

use App\Enums\Config\ConfigKey;
use App\Enums\Feedback\ReviewDiscountType;
use App\Facades\Currency;
use App\Models\Config;
use App\Models\Orders\Order;

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
    public function __construct(private readonly Order $order) {}

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $currency = $this->order->currency;
        $photoDiscount = Currency::format(
            (float)Config::value(ConfigKey::Feedback, 'discount.' . ReviewDiscountType::Photo->value . '.' . $currency),
            $currency,
            ' ',
        );
        $videoDiscount = Currency::format(
            (float)Config::value(ConfigKey::Feedback, 'discount.' . ReviewDiscountType::Video->value . '.' . $currency),
            $currency,
            ' ',
        );

        return <<<SMS
        {$this->order->first_name}, спасибо, что выбрали Barocco.by.
        Мы очень ценим ваше доверие!

        Воспользуйтесь специальным предложением при следующей покупке пары обуви или аксессуара.

        📸 Оставьте отзыв с фото — получите скидку {$photoDiscount}.

        🎥 Запишите видеоотзыв или снимите распаковку и забирайте скидку {$videoDiscount}.

        Поделиться мнением о покупке можно здесь:
        https://barocco.by/feedbacks

        Ждем ваш отзыв!
        SMS;
    }
}
