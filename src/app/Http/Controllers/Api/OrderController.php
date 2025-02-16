<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Models\Orders\Order;

class OrderController extends Controller
{
    public function checkout(): OrderResource
    {
        $order = Order::query()->latest('id')->first();

        return new OrderResource($order);
    }

    public function oneclick(): OrderResource
    {
        $order = Order::query()->latest('id')->first();

        return new OrderResource($order);
    }
}
