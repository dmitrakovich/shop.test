<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id id товара
 * @property int $size_id id размера
 * @property string|null $reason Причина добавления в брак
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Size|null $size
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DefectiveProduct extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the product associated with the defective product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the product size associated with the defective product.
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }
}
