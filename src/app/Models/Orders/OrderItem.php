<?php

namespace App\Models\Orders;

use App\Models\Size;
use App\Models\Product;
use App\Models\Payments\Installment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * class OrderItem
 *
 * @property integer $count
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
    ];

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
