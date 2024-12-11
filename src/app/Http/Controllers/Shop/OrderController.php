<?php

namespace App\Http\Controllers\Shop;

use App\Contracts\OrderServiceInterface;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Events\OrderCreated;
use App\Facades\Cart;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\UserAddressRequest;
use App\Http\Requests\Order\UserRequest;
use App\Models\CartData;
use App\Models\Orders\Order;
use App\Services\AuthService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        StoreRequest $request,
        UserRequest $userRequest,
        UserAddressRequest $userAddressRequest,
        AuthService $authService
    ) {
        if ($request->isOneClick()) {
            $cart = Cart::make();
            $items = [];
            foreach ($request->input('sizes') as $sizeId => $state) {
                $items[] = new CartData([
                    'product_id' => (int)$request->input('product_id'),
                    'size_id' => $sizeId,
                    'count' => 1,
                ]);
            }
            $cart->setRelation('items', new EloquentCollection($items));
        } else {
            $cart = Cart::getCart();
            abort_if(empty($cart['items']) || $cart->availableItems()->isEmpty(), 404);
        }
        $user = $authService->getOrCreateUser($userRequest->input('phone'), $userRequest->validated(), $userAddressRequest->validated());
        $order = app(OrderServiceInterface::class)->store($request, $cart, $user);
        Cart::clear(true);
        Cart::clearPromocode();

        event(new OrderCreated($order, $user));

        return redirect()->route('cart-final')->with('order_id', $order->id);
    }

    /**
     * Get print view
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function print(Order $order)
    {
        return view('admin.order-print', compact('order'));
    }
}
