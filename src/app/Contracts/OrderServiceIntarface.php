<?php

namespace App\Contracts;

use App\Models\Cart;
use App\Http\Requests\StoreOrderRequest;

interface OrderServiceIntarface
{
    /**
     * Store order (create new)
     *
     * @param StoreOrderRequest $request
     * @param Cart $cart
     * @return \App\Models\Order
     */
    public function store(StoreOrderRequest $request, Cart $cart);
}
