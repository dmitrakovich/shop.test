<?php

namespace App\Http\Controllers\Shop;

use App\Models\Orders\Order;
use Illuminate\Contracts\View\View;

class OrderController extends BaseController
{
    /**
     * Get print view
     */
    public function print(Order $order): View
    {
        return view('admin.order-print', compact('order'));
    }
}
