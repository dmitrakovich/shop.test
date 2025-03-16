<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ProductGroup extends Model
{
    protected $guarded = ['id'];

    /**
     * Product relation.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id', 'product_group_id');
    }
}
