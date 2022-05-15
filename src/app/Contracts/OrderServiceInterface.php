<?php

namespace App\Contracts;

use App\Models\Cart;
use App\Http\Requests\Order\StoreRequest;

interface OrderServiceInterface
{
    /**
     * Store order (create new)
     *
     * @return \App\Models\Orders\Order
     */
    public function store(StoreRequest $request, Cart $cart);
}
