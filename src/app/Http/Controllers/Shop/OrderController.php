<?php

namespace App\Http\Controllers\Shop;

use App\Data\Order\OneClickOrderData;
use App\Data\Order\OrderData;
use App\Enums\Order\OrderMethod;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Facades\Cart;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\UserAddressRequest;
use App\Http\Requests\Order\UserRequest;
use App\Models\Orders\Order;
use App\Services\AuthService;
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
            'status:key,name_for_user',
        ])->where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('dashboard.orders', [
            'allOrders' => $orders,
            'expectedOrders' => $orders->whereIn('status.key', ['new', 'in_work', 'wait_payment', 'paid', 'assembled', 'packaging', 'ready']),
            'sentOrders' => $orders->whereIn('status.key', ['sent', 'fitting']),
            'completedOrders' => $orders->where('status.key', 'complete'),
            'canceledOrders' => $orders->whereIn('status.key', ['canceled', 'return', 'return_fitting']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreRequest $request,
        UserRequest $userRequest,
        UserAddressRequest $userAddressRequest,
        AuthService $authService,
        OrderService $orderService
    ): RedirectResponse {
        $cart = $request->isOneClick()
            ? Cart::makeTempCart(OneClickOrderData::from($request))
            : Cart::getCart();

        $orderData = OrderData::from($request);

        if ($request->isOneClick()) {
            $orderData->setOrderMethod(OrderMethod::ONECLICK);
        }

        abort_if(!$cart->hasAvailableItems(), 404, 'Товаров нет в наличии');

        // $user = $authService->getOrCreateUser($userRequest->input('phone'), $userRequest->validated(), $userAddressRequest->validated());
        $order = $orderService->store($request, $cart, $orderData);

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
