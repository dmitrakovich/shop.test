<?php

namespace App\Contracts;

use App\Http\Requests\Order\StoreRequest;
use App\Models\Cart;
use App\Models\User\User;

interface OrderServiceInterface
{
    /**
     * Store order (create new)
     *
     * @return \App\Models\Orders\Order
     */
    public function store(StoreRequest $request, Cart $cart, User $user);
}
