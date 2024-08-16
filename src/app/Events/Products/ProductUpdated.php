<?php

namespace App\Events\Products;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;

class ProductUpdated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public Product $product) {}
}
