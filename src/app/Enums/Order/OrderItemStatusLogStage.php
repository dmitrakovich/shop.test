<?php

namespace App\Enums\Order;

use App\Models\Logs\OrderItemStatusLog;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Current stage of an order item unit on a stock.
 *
 * Value is the log date column of the stage. Cases are ordered by precedence:
 * the current stage is the first case whose date is filled.
 */
enum OrderItemStatusLogStage: string implements HasColor, HasLabel
{
    case OutOfStock = 'deleted_at';
    case Canceled = 'canceled_at';
    case Returned = 'returned_at';
    case Completed = 'completed_at';
    case Sent = 'sended_at';
    case PickedUp = 'picked_up_at';
    case Collected = 'collected_at';
    case Confirmed = 'confirmed_at';
    case New = 'created_at';

    public function getLabel(): string
    {
        return match ($this) {
            self::OutOfStock => 'Нет в наличии',
            self::Canceled => 'Отменён',
            self::Returned => 'Возврат',
            self::Completed => 'Выкуплен',
            self::Sent => 'Отправлен',
            self::PickedUp => 'Упакован',
            self::Collected => 'Собран',
            self::Confirmed => 'Подтверждён',
            self::New => 'Новый',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OutOfStock => 'danger',
            self::Canceled, self::New => 'gray',
            self::Returned => 'warning',
            self::Completed => 'success',
            self::Sent, self::PickedUp, self::Collected, self::Confirmed => 'info',
        };
    }

    public static function fromLog(OrderItemStatusLog $log): self
    {
        foreach (self::cases() as $stage) {
            if ($log->getAttribute($stage->value) !== null) {
                return $stage;
            }
        }

        return self::New;
    }

    /**
     * Limit the query to logs whose current stage is this one.
     *
     * @param  Builder<OrderItemStatusLog>  $query
     */
    public function applyToQuery(Builder $query): void
    {
        foreach (self::cases() as $stage) {
            if ($stage === $this) {
                $query->whereNotNull($stage->value);

                return;
            }

            $query->whereNull($stage->value);
        }
    }
}
