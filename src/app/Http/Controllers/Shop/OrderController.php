<?php

namespace App\Http\Controllers\Shop;

use App\Facades\Cart;
use App\Facades\Sale;
use App\Models\Order;
use App\Models\CartData;
use App\Facades\Currency;
use App\Mail\OrderCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())->get();
        // dd($orders);

        return view('dashboard.orders', compact('orders'));
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
     * @param  StoreOrderRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreOrderRequest $request)
    {
        $userData = $request->validated();
        $isOneClick = $request->has(['product_id', 'sizes']);

        if ($isOneClick) {
            $cart = Cart::make();
            $items = [];
            foreach ($request->input('sizes') as $sizeId => $state) {
                $items[] = CartData::make([
                    'product_id' => (int)$request->input('product_id'),
                    'size_id' => $sizeId,
                    'color_id' => 17,
                    'count' => 1,
                ]);
            }
            $cart->setRelation('items', new EloquentCollection($items));
        } else {
            $cart = Cart::withData();
            abort_if(empty($cart['items']) || $cart->items->isEmpty(), 404);
        }
        Sale::applyForCart($cart);

        $order = Order::create([
            'user_name' => $userData['name'],
            'user_id' => Auth::check() ? Auth::id() : null,
            'type' => 'retail',
            'email' => $userData['email'] ?? null,
            'phone' => $userData['phone'],
            // 'comment' => $request->input('comment'),
            'currency' => Currency::getCurrentCurrency()->code,
            'rate' => Currency::getCurrentCurrency()->rate,
            // 'promocode_id' => Cart::getPromocodeId(),
            // 'country' => $address['country'] ?? null,
            // 'region' => $address['administrative_area_level_1'] ?? null,
            'city' => $userData['city'] ?? null,
            // 'zip' => $address['postal_code'] ?? null,
            // 'street' => $address['route'] ?? null,
            // 'house' => $address['street_number'] ?? null,
            'user_addr' => $userData['user_addr'] ?? null,
            'payment' => $userData['payment_name'],
            'payment_code' => $userData['payment_code'],
            // 'payment_cost' => isset($payment['price']) ? $payment['price'] : 0.00,
            'delivery' => $userData['delivery_name'],
            'delivery_code' => $userData['delivery_code'],
            // 'delivery_cost' => $delivery['price'] ?? null,
            // 'delivery_point' => $point['address'] ?? '',
            // 'delivery_point_code' => $point['code'] ?? '',
            // 'source' => Cookie::has('soc_order') ? 1 : 0
        ]);

        foreach ($cart->items as $item) {
            $order->data()->create([
                'product_id' => $item->product_id,
                'size_id' => $item->size_id,
                'color_id' => $item->color_id,
                'count' => $item->count,
                'buy_price' => $item->product->buy_price,
                'price' => $item->product->price,
                'old_price' => $item->product->getOldPrice(),
                'current_price' => $item->product->getPrice(),
                'discount' => $item->product->getSalePercentage(),
            ]);
        }

        if (!empty($order['email'])) {
            Mail::to($order['email'])->send(new OrderCreated($order));
        }
        if (App::environment('production')) {
            Mail::to(config('contacts.email.link', 'info@barocco.by'))
                ->send(new OrderCreated($order));
        }

        $orderInfo = [
            'orderNum' => $order->id,
            'totalPrice' => $cart->getTotalPrice(),
            'address' => $userData['user_addr'],
            'delivery' => $userData['delivery_name'],
            'payment' => $userData['payment_name'],
        ];
        Cart::clear();
        return redirect()->route('cart-final')->with('order_info', $orderInfo);

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
     * @param  \Illuminate\Http\Request  $request
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

    public function print(Order $order)
    {
        return view('admin.order-print', compact('order'));
    }
}
