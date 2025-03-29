<?php

namespace App\Http\Controllers\Api;

use App\Data\Order\OneClickOrderData;
use App\Data\Order\OrderData;
use App\Enums\Order\OrderMethod;
use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(OrderRepository $repository): OrderCollection
    {
        return new OrderCollection($repository->getForUser());
    }

    public function checkout(OrderData $orderData): OrderResource
    {
        $order = $this->orderService->store(Cart::getCart(), $orderData);

        return new OrderResource($order);
    }

    public function oneclick(OneClickOrderData $oneClickOrderData, OrderData $orderData): OrderResource
    {
        $orderData->setOrderMethod(OrderMethod::ONECLICK);

        $order = $this->orderService->store(Cart::makeTempCart($oneClickOrderData), $orderData);

        return new OrderResource($order);
    }
}
