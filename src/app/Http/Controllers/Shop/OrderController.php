<?php

namespace App\Http\Controllers\Shop;

use App\Data\Order\OneClickOrderData;
use App\Data\Order\OrderData;
use App\Enums\Order\OrderMethod;
use App\Enums\Order\OrderStatus;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Facades\Cart;
use App\Http\Requests\Order\StoreRequest;
use App\Models\Orders\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $orders = Order::with([
            'country',
            'track',
            'onlinePayments' => fn ($query) => $query->where('last_status_enum_id', OnlinePaymentStatusEnum::PENDING),
            'itemsExtended',
        ])->where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('dashboard.orders', [
            'allOrders' => $orders,
            'expectedOrders' => $orders->whereIn('status', [
                OrderStatus::NEW,
                OrderStatus::IN_WORK,
                OrderStatus::WAIT_PAYMENT,
                OrderStatus::PAID,
                OrderStatus::ASSEMBLED,
                OrderStatus::PACKAGING,
                OrderStatus::READY,
            ]),
            'sentOrders' => $orders->whereIn('status', [
                OrderStatus::SENT,
                OrderStatus::FITTING,
            ]),
            'completedOrders' => $orders->where('status', OrderStatus::COMPLETED),
            'canceledOrders' => $orders->whereIn('status', [
                OrderStatus::CANCELED,
                OrderStatus::RETURN,
                OrderStatus::RETURN_FITTING,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, OrderService $orderService): RedirectResponse
    {
        $cart = $request->isOneClick()
            ? Cart::makeTempCart(OneClickOrderData::from($request))
            : Cart::getCart();

        $orderData = OrderData::from($request);

        if ($request->isOneClick()) {
            $orderData->setOrderMethod(OrderMethod::ONECLICK);
        }

        $order = $orderService->store($cart, $orderData);

        return redirect()->route('cart-final')->with('order_id', $order->id);
    }

    /**
     * Get print view
     */
    public function print(Order $order): View
    {
        return view('admin.order-print', compact('order'));
    }
}
