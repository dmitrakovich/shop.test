<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Models\AvailableSizesFull;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Get the URL for a product by it 1C id.
     */
    public function getUrl(AvailableSizesFull $availableSizesFull): string
    {
        if (!$product = $availableSizesFull->product) {
            abort(404, 'Для данного id нет соответствующего товра на сайте');
        }

        return url($product->getUrl());
    }
}
