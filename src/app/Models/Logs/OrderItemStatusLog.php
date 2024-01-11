<?php

namespace App\Models\Logs;

use App\Models\Bots\Telegram\TelegramChat;
use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Logs\OrderItemStatusLog
 *
 * @property int $id
 * @property int $order_item_id
 * @property int $stock_id
 * @property Carbon $created_at
 * @property Carbon|null $reserved_at
 * @property Carbon|null $canceled_at
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $collected_at
 * @property Carbon|null $picked_up_at
 * @property Carbon|null $moved_at
 * @property Carbon|null $sended_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $deleted_at
 * @property-read OrderItem $orderItem
 * @property-read Stock $stock
 */
class OrderItemStatusLog extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'log_order_item_statuses';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sended_at' => 'datetime',
        'reserved_at' => 'datetime',
        'canceled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'collected_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'sended_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * Get the order item associated with the notification log.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the stock associated with the notification log.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Set the specified status date field to the current date and time.
     */
    public function setDateFieldForStatus(string $status): void
    {
        if ($dateField = $this->getDateFieldByStatus($status)) {
            $this->{$dateField} = now();
            $this->save();
        }
    }

    /**
     * Get the corresponding date field name for the given status.
     */
    public static function getDateFieldByStatus(string $status): ?string
    {
        return match ($status) {
            'canceled' => 'canceled_at',
            'confirmed' => 'confirmed_at',
            'pickup' => 'picked_up_at',
            'sent', 'fitting' => 'sended_at',
            'complete', 'installment' => 'completed_at',
            'return', 'return_fitting' => 'returned_at',
            default => null
        };
    }

    /**
     * Get a Telegram chat based on the given order item status.
     */
    public function getChatByStatus(string $status): ?TelegramChat
    {
        if ($status === 'confirmed') {
            return $this->stock->privateChat;
        }

        return $this->stock->groupChat;
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('d.m.Y H:i:s');
    }
}
