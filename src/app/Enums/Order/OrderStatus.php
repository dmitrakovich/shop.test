<?php

namespace App\Enums\Order;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: int implements HasLabel
{
    case NEW = 1;
    case CANCELED = 2;
    case IN_WORK = 3;
    case WAIT_PAYMENT = 4;
    case PAID = 5;
    case ASSEMBLED = 6;
    case PACKAGING = 7;
    case READY = 8;
    case SENT = 9;
    case FITTING = 10;
    case COMPLETED = 11;
    case RETURN = 12;
    case RETURN_FITTING = 13;
    case INSTALLMENT = 14;
    case CONFIRMED = 15;
    case PARTIAL_COMPLETED = 16;
    case DELIVERED = 17;

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Принят',
            self::CANCELED => 'Отменен',
            self::IN_WORK => 'В работе',
            self::WAIT_PAYMENT => 'Ожидает оплату',
            self::PAID => 'Оплачен',
            self::ASSEMBLED => 'Собран',
            self::PACKAGING => 'На упаковке',
            self::READY => 'Готов к отправке',
            self::SENT => 'Отправлен',
            self::FITTING => 'Отправлен на примерку',
            self::COMPLETED => 'Выполнен',
            self::RETURN => 'Возврат по сроку',
            self::RETURN_FITTING => 'Возвращен с примерки',
            self::INSTALLMENT => 'Рассрочка',
            self::CONFIRMED => 'Подтвержден',
            self::PARTIAL_COMPLETED => 'Частичный выкуп',
            self::DELIVERED => 'Доставлен',
        };
    }

    public function getLabelForClient(): string
    {
        return match ($this) {
            self::CONFIRMED, self::ASSEMBLED, self::PACKAGING => 'В работе',
            self::RETURN, self::RETURN_FITTING => 'Возвращен',
            self::PARTIAL_COMPLETED => 'Выполнен',
            default => $this->getLabel(),
        };
    }

    public function isNew(): bool
    {
        return $this === self::NEW;
    }

    public function isCanceled(): bool
    {
        return $this === self::CANCELED;
    }

    public function isInWork(): bool
    {
        return $this === self::IN_WORK;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isWaitPayment(): bool
    {
        return $this === self::WAIT_PAYMENT;
    }

    public function hasTracking(): bool
    {
        return in_array($this, [self::SENT, self::FITTING]);
    }

    /**
     * @return list<self>
     */
    public static function shipmentPreparationStatuses(): array
    {
        return [
            self::PACKAGING,
            self::READY,
            self::SENT,
        ];
    }

    public function getOldKey(): string
    {
        return match ($this) {
            self::NEW => 'new',
            self::CANCELED => 'canceled',
            self::IN_WORK => 'in_work',
            self::WAIT_PAYMENT => 'wait_payment',
            self::PAID => 'paid',
            self::ASSEMBLED => 'assembled',
            self::PACKAGING => 'packaging',
            self::READY => 'ready',
            self::SENT => 'sent',
            self::FITTING => 'fitting',
            self::COMPLETED => 'complete',
            self::RETURN => 'return',
            self::RETURN_FITTING => 'return_fitting',
            self::INSTALLMENT => 'installment',
            self::CONFIRMED => 'confirmed',
            self::PARTIAL_COMPLETED => 'partial_complete',
            self::DELIVERED => 'delivered',
        };
    }
}
