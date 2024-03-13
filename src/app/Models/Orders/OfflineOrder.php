<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $receipt_number Receipt number
 * @property int|null $stock_id
 * @property int|null $product_id
 * @property int|null $size_id
 * @property float $price Цена покупки
 * @property bool $count Number of items in the order
 * @property int|null $user_id
 * @property string $user_phone
 * @property string $sold_at Date and time of sale
 * @property string|null $returned_at Date and time of return
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
}
