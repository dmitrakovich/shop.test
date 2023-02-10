<?php

namespace App\Models\Orders;

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
 * @property \Carbon\Carbon $status_updated_at
 * @property \Carbon\Carbon $release_date
 * @property int $pred_period
 * @property-read Product $product
 * @property-read OrderItemStatus $status
 * @property-read Installment $installment
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
     * The attributes that should be mutated to dates.
     */
    protected $dates = ['status_updated_at'];

    /**
     * Bootstrap the model and its traits
     */
    public static function boot(): void
    {
        parent::boot();

        static::saving(function (self $orderItem) {
            if ($orderItem->isDirty('status_key')) {
                $orderItem->status_updated_at = now();
            }
        });
    }

    /**
     * Product from order item
     */
    public function product(): Relations\BelongsTo
    {
        return $this->belongsTo(Product::class)
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
}
