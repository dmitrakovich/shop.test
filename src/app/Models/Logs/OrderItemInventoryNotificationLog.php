<?php

namespace App\Models\Logs;

use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Logs\OrderItemInventoryNotificationLog
 *
 * @property int $id
 * @property int $order_item_id
 * @property int $stock_id
 * @property Carbon $created_at
 * @property Carbon|null $sended_at
 * @property Carbon|null $reserved_at
 * @property Carbon|null $canceled_at
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $completed_at
 * @property-read OrderItem $orderItem
 * @property-read Stock $stock
 */
class OrderItemInventoryNotificationLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'log_order_item_inventory_notifications';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

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
     * Get field with datatime by order item status
     */
    public static function getDateFieldByStatus(string $status): string
    {
        return match ($status) {
            'new', default => 'sended_at',
            'canceled' => 'canceled_at',
            'confirmed' => 'confirmed_at',
            'complete', 'installment' => 'completed_at',
        };
    }
}
