<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use App\Models\Size;
use App\Models\Stock;
use App\Models\User\User;

/**
 * @property int $id
 * @property string $receipt_number Receipt number
 * @property int|null $stock_id
 * @property int|null $product_id
 * @property int|null $size_id
 * @property float $price Цена покупки
 * @property bool $count Number of items in the order
 * @property string $sku product stock keeping unit
 * @property int|null $user_id
 * @property string $user_phone
 * @property \Illuminate\Support\Carbon $sold_at Date and time of sale
 * @property \Illuminate\Support\Carbon|null $returned_at Date and time of return
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Size|null $size
 * @property-read \App\Models\Stock|null $stock
 * @property-read \App\Models\User\User|null $user
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class OfflineOrder extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sold_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    /**
     * Get the product associated with the offline order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the size associated with the offline order.
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get the stock associated with the offline order.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the user associated with the offline order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
