<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Models\AvailableSizesFull;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Get the URL for a product by it 1C id.
     */
    public function getUrl(AvailableSizesFull $availableSizesFull): string
    {
        Log::info('#59. api route for 1C used');

        if (!$product = $availableSizesFull->product) {
            abort(404, 'Для данного id нет соответствующего товра на сайте');
        }

        return url($product->getUrl());
    }
}
