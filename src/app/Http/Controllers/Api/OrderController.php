<?php

namespace App\Http\Controllers\Api;

use App\Data\Order\OneClickOrderData;
use App\Data\Order\OrderData;
use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Services\OrderService;
use App\Services\UserService;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly UserService $userService
    ) {}

    public function checkout(OrderData $orderData): OrderResource
    {
        $order = $this->orderService->store($request, $cart, $user);

        return new OrderResource($order);
    }

    public function oneclick(OneClickOrderData $oneClickOrderData, OrderData $orderData): OrderResource
    {
        $cart = Cart::makeTempCart($oneClickOrderData);
        // $user = $this->userService->getOrCreate($oneClickOrderData);

        $order = $this->orderService->store($request, $cart, $user);

        return new OrderResource($order);
    }
}
