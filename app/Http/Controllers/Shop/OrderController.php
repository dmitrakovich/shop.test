<?php

namespace App\Http\Controllers\Shop;

use App\Facades\Cart;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
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
        $orders = Order::where('user_id', Auth::user()->getAuthIdentifier())
            ->with('price')
            ->get();
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
        $cart = Cart::withData();
        $userData = $request->validated();

        list($deliveryCode, $delivery) = explode('|', $userData['delivery']);
        list($paymentCode, $payment) = explode('|', $userData['payment']);

        $order = Order::create([
            'user_name' => $userData['name'],
            'user_id' => Auth::check() ? Auth::user()->getAuthIdentifier() : null,
            'type' => 'retail',
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            // 'comment' => $request->input('comment'),
            'currency' => 'BYN',
            'rate' => 1, // CurrencyFacade::getCurrentCurrencyData()->rate,
            // 'promocode_id' => Cart::getPromocodeId(),
            // 'country' => $address['country'] ?? null,
            // 'region' => $address['administrative_area_level_1'] ?? null,
            'city' => $userData['city'],
            // 'zip' => $address['postal_code'] ?? null,
            // 'street' => $address['route'] ?? null,
            // 'house' => $address['street_number'] ?? null,
            'user_addr' => $userData['user_addr'],
            'payment' => $payment,
            'payment_code' => $paymentCode,
            // 'payment_cost' => isset($payment['price']) ? $payment['price'] : 0.00,
            'delivery' => $delivery,
            'delivery_code' => $deliveryCode,
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
                'old_price' => $item->product->old_price,
                'current_price' => $item->product->price,
                'discount' => 0,
            ]);
        }

        $orderInfo = [
            'orderNum' => $order->id,
            'totalPrice' => $cart->getTotalPrice(),
            'address' => $userData['user_addr'],
            'delivery' => $delivery,
            'payment' => $payment,
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
}
