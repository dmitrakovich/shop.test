<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $count
 * @property int $size_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Size|null $size
 */
class CartData extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'cart_id',
        'product_id',
        'size_id',
        'count',

        // mock for sync
        'price',
        'status_key',
    ];

    /**
     * Indicates whether the product is available.
     */
    private bool $available = true;

    /**
     * Get the product associated with this cart data.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the size associated with this cart data.
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Set the product as not available.
     */
    public function setNotAvailable(): void
    {
        $this->available = false;
    }

    /**
     * Determine if the product is available.
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }
}
