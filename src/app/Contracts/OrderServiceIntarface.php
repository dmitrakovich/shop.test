<?php

namespace App\Contracts;

use App\Models\Cart;
use Illuminate\Foundation\Http\FormRequest;

interface OrderServiceIntarface
{
    /**
     * Store order (create new)
     *
     * @param FormRequest $request
     * @param Cart $cart
     * @return \App\Models\Order
     */
    public function store(FormRequest $request, Cart $cart);
}
