<?php

namespace App\Models\Orders;

use App\Models\Logs\OrderItemInventoryNotificationLog;
use App\Models\Payments\Installment;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * class OrderItem
 *
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
 * @property string $status_key
 * @property \Illuminate\Support\Carbon $status_updated_at
 * @property \Illuminate\Support\Carbon $release_date
 * @property int $pred_period
 * @property-read Order $order
 * @property-read Product $product
 * @property-read Size $size
 * @property-read OrderItemStatus $status
 * @property-read Installment $installment
 * @property-read OrderItemInventoryNotificationLog $inventoryNotification
 */
class OrderItem extends Model
{
    use HasFactory;

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
        'status_key',
        'status_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status_updated_at' => 'datetime',
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
     * Order item status
     */
    public function status(): Relations\BelongsTo
    {
        return $this->belongsTo(OrderItemStatus::class);
    }

    /**
     * Get the installment associated with the order.
     */
    public function installment(): Relations\HasOne
    {
        return $this->hasOne(Installment::class);
    }

    /**
     * Get the inventory notification associated with the order item.
     */
    public function inventoryNotification(): Relations\HasOne
    {
        return $this->hasOne(OrderItemInventoryNotificationLog::class);
    }

    /**
     * Check if order item completed
     */
    public function isCompleted(): bool
    {
        return $this->status_key === 'complete';
    }

    /**
     * Set canceled status
     */
    public function cancel(): bool
    {
        return $this->update(['status_key' => 'canceled']);
    }
}
