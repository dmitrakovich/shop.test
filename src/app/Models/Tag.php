<?php

namespace App\Models;

use App\Models\Product;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;


    /**
     * Теги
     */
    public function products()
    {
        return $this->morphToMany(Product::class, 'attribute', 'product_attributes');
    }
}
