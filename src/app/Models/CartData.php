<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product $product
 * @todo rename to CartItem
 */
class CartData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
