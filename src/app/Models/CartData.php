<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartData extends Model
{
    use HasFactory;

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
     * Связть с товарами
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')
            ->with(['category', 'brand', 'media', 'styles']);
    }
    /**
     * Связть с размерами
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function size()
    {
        return $this->hasOne(Size::class, 'id', 'size_id');
    }
}
