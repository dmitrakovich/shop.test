<?php

namespace App\Contracts;

use App\Models\Cart;
use App\Http\Requests\Order\StoreRequest;

interface OrderServiceIntarface
{
    /**
     * Store order (create new)
     *
     * @param StoreRequest $request
     * @param Cart $cart
     * @return \App\Models\Orders\Order
     */
    public function store(StoreRequest $request, Cart $cart);
}
