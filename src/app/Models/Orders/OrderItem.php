<?php

namespace App\Models\Orders;

use App\Enums\Order\OrderItemStatus;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\Payments\Installment;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $size_id
 * @property int $count
 * @property float $buy_price
 * @property float $price
 * @property float $old_price
 * @property float $current_price
 * @property float $discount
 * @property bool $promocode_applied
 * @property \App\Enums\Order\OrderItemStatus $status
 * @property \Illuminate\Support\Carbon $status_updated_at
 * @property \Illuminate\Support\Carbon|null $release_date
 * @property int|null $pred_period
 *
 * @property-read \App\Models\Orders\Order|null $order
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Size|null $size
 * @property-read \App\Models\Payments\Installment|null $installment
 * @property-read \App\Models\Logs\OrderItemStatusLog|null $inventoryNotification
 * @property-read \App\Models\Logs\OrderItemStatusLog|null $statusLog
 */
class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'size_id',
        'count',
        'buy_price',
        'price',
        'old_price',
        'current_price',
        'discount',
        'status',
        'item_status', // stub for admin panel
        'status_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'promocode_applied' => 'boolean',
        'status' => OrderItemStatus::class,
        'status_updated_at' => 'datetime',
        'release_date' => 'datetime',
    ];

    /**
     * Get the order associated with the order's item.
     */
    public function order(): Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Product from order item
     */
    public function product(): Relations\BelongsTo
    {
        return $this->belongsTo(Product::class)
            ->withTrashed()
            ->withDefault(function ($product, $orderItem) {
                $product->setDefaultValues($orderItem->product_id);
            })
            ->with(['brand', 'category', 'media']);
    }

    /**
     * Product size
     */
    public function size(): Relations\BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get the installment associated with the order.
     */
    public function installment(): Relations\HasOne
    {
        return $this->hasOne(Installment::class);
    }

    /**
     * Get the inventory notification associated with the order item. Alias for statusLog
     */
    public function inventoryNotification(): Relations\HasOne
    {
        return $this->hasOne(OrderItemStatusLog::class);
    }

    /**
     * Get the status log associated with the order item.
     */
    public function statusLog(): Relations\HasOne
    {
        return $this->hasOne(OrderItemStatusLog::class);
    }

    /**
     * Check if order item completed
     */
    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    /**
     * Checks if the model's status is final.
     */
    public function isFinalStatus(): bool
    {
        return $this->status->isFinalStatus();
    }

    /**
     * Set canceled status
     */
    public function cancel(): bool
    {
        return $this->update(['status' => OrderItemStatus::CANCELED]);
    }

    /**
     * Set `no_availability` status
     */
    public function outOfStock(): bool
    {
        return $this->update(['status' => OrderItemStatus::NO_AVAILABILITY]);
    }
}
