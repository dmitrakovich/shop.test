<?php

namespace App\Http\Controllers\Shop;

use App\Contracts\OrderServiceInterface;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Events\OrderCreated;
use App\Facades\Cart;
use App\Http\Requests\Order\UserRequest;
use App\Http\Requests\Order\UserAddressRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\SyncRequest;
use App\Models\CartData;
use App\Models\Orders\Order;
use App\Services\OldSiteSyncService;
use App\Services\AuthService;
use Database\Seeders\ProductSeeder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
                $items[] = CartData::make([
                    'product_id' => (int)$request->input('product_id'),
                    'size_id' => $sizeId,
                    'count' => 1,
                ]);
            }
            $cart->setRelation('items', new EloquentCollection($items));
        } else {
            $cart = Cart::withData();
            abort_if(empty($cart['items']) || $cart->items->isEmpty(), 404);
        }
        $user = $authService->getOrCreateUser($userRequest->input('phone'), $userRequest->validated(), $userAddressRequest->validated());
        $order = app(OrderServiceInterface::class)->store($request, $cart, $user);
        Cart::clear();

        event(new OrderCreated($order, $user));

        return redirect()->route('cart-final')->with('order_id', $order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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

    /**
     * Sync order with another DB
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(SyncRequest $request)
    {
        $oldId = (int)$request->input('id');
        $oldSizes = (new ProductSeeder)->attributesList['sizes']['new_id'];
        $cart = Cart::make();
        $items = [];
        foreach ($request->input('items') as $item) {
            $item['size_id'] = $oldSizes[$item['size']] ?? 1;
            $items[] = CartData::make($item);
        }
        $cart->setRelation('items', new EloquentCollection($items));

        try {
            $order = app(OrderServiceInterface::class)
                ->store($request, $cart);
        } catch (\Throwable $th) {
            \Sentry\captureException($th);
            abort(OldSiteSyncService::errorResponse($th->getMessage()));
        }

        return OldSiteSyncService::successResponse([$oldId => $order->id]);
    }
}
