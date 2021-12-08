<?php

namespace App\Models;

use App\Models\Orders\OrderItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * class OrderData
 *
 * @property-read OrderItemStatus $status
 */
class OrderData extends Model
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)
            ->with(['brand', 'category', 'media']);
    }

    /**
     * Product size
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Order item status
     *
     * @return Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(OrderItemStatus::class);
    }
}
