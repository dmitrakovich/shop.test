<?php

namespace App\Models\Logs;

use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_item_id
 * @property bool $moved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrderItem $orderItem
 */
class OrderItemPickupStatusLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'log_order_item_pickup_statuses';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * Get the order item associated with the notification log.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
