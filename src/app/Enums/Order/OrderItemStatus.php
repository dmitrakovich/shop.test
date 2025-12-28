<?php

namespace App\Enums\Order;

use Filament\Support\Contracts\HasLabel;

enum OrderItemStatus: int implements HasLabel
{
    case NEW = 1;
    case RESERVED = 2;
    case PICKUP = 3;
    case PACKAGING = 4;
    case SENT = 5;
    case FITTING = 6;
    case COMPLETED = 7;
    case RETURN = 8;
    case CANCELED = 9;
    case RETURN_FITTING = 10;
    case NO_AVAILABILITY = 11;
    case INSTALLMENT = 12;
    case CONFIRMED = 13;
    case COLLECT = 14;
    case WAITING_REFUND = 15;
    case DISPLACEMENT = 16;

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Принят',
            self::RESERVED => 'Отложен',
            self::PICKUP => 'Забран',
            self::PACKAGING => 'Упакован',
            self::SENT => 'Отправлен',
            self::FITTING => 'Отправлен на примерку',
            self::COMPLETED => 'Выкуплен',
            self::RETURN => 'Возвращен',
            self::CANCELED => 'Отменен',
            self::RETURN_FITTING => 'Возвращен с примерки',
            self::NO_AVAILABILITY => 'Нет в наличии',
            self::INSTALLMENT => 'Рассрочка',
            self::CONFIRMED => 'Подтвержден',
            self::COLLECT => 'Собран',
            self::WAITING_REFUND => 'Ожидает возврат',
            self::DISPLACEMENT => 'Перемещение',
        };
    }

    public function getLabelForClient(): string
    {
        return match ($this) {
            self::CONFIRMED, self::PICKUP => 'На упаковке',
            self::RETURN, self::RETURN_FITTING => 'Возвращен',
            self::DISPLACEMENT => 'Собирается',
            default => $this->getLabel(),
        };
    }

    public function isNew(): bool
    {
        return $this === self::NEW;
    }

    public function isConfirmed(): bool
    {
        return $this === self::CONFIRMED;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFinalStatus(): bool
    {
        return in_array($this, [
            self::NO_AVAILABILITY,
            self::CANCELED,
            self::RETURN,
            self::RETURN_FITTING,
        ]);
    }

    /**
     * @return list<self>
     */
    public static function departureStatuses(): array
    {
        return [
            self::INSTALLMENT,
            self::PACKAGING,
            self::PICKUP,
            self::SENT,
            self::FITTING,
            self::COMPLETED,
            self::RETURN,
            self::RETURN_FITTING,
        ];
    }

    public function getOldKey(): string
    {
        return match ($this) {
            self::NEW => 'new',
            self::RESERVED => 'reserved',
            self::PICKUP => 'pickup',
            self::PACKAGING => 'packaging',
            self::SENT => 'sent',
            self::FITTING => 'fitting',
            self::COMPLETED => 'complete',
            self::RETURN => 'return',
            self::CANCELED => 'canceled',
            self::RETURN_FITTING => 'return_fitting',
            self::NO_AVAILABILITY => 'no_availability',
            self::INSTALLMENT => 'installment',
            self::CONFIRMED => 'confirmed',
            self::COLLECT => 'collect',
            self::WAITING_REFUND => 'waiting_refund',
            self::DISPLACEMENT => 'displacement',
        };
    }
}
