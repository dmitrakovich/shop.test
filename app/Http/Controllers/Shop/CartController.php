<?php

namespace App\Http\Controllers\Shop;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::withData();
        $user = auth()->user() ?? new User();
        return view('shop.cart', compact('cart', 'user'));
    }

    public function delete(Request $request, int $itemId)
    {
        Cart::items()->where('id', $itemId)->delete();
        Cart::removeItem($itemId);

        /*if (Cart::availableItemsCount() < 1) {
            Cart::removePromocodeAuto();
        }*/

        return back();
    }

    public function submit(StoreOrderRequest $request)
    {
        $cart = Cart::withData();
        $userData = $request->validated();

        $order = Order::create([
            'user_name' => $userData['name'],
            'user_id' => Auth::check() ? 123 : null,
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
            // 'payment' => $payment['name'] ?? '',
            // 'payment_code' => $payment['code'] ?? '',
            // 'payment_cost' => isset($payment['price']) ? $payment['price'] : 0.00,
            // 'delivery' => $delivery['name'] ?? '',
            // 'delivery_code' => $delivery['code'] ?? '',
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
        ];

        Cart::clear();

        return redirect()->route('cart-final')->with('order_info', $orderInfo);
    }

    public function final()
    {
        if (!Session::has('order_info')) {
            return redirect()->route('dashboard-orders');
        }
        $recomended = Product::inRandomOrder()->limit(5)->get();
        return view('shop.cart-done', compact('recomended'));
    }

    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id') ?? abort(404);
        $sizes = $request->input('sizes') ?? abort(404);
        // $colorId = $request->input('color_id') ?? abort(404);
        $colorId = 17;

        // Product::where('id', $request->input('id'))
            /*->whereHas('sizes', function ($query) use ($request) {
                $query->where('sizes.id', $request->input('size_id'));
            })*/
            /*->whereHas(function ($query) use ($request) {
                $query->where("$relationTable.id", $request->input('id')));
            })*/
            // ->first(['id']);

        $product = Product::findOrFail($productId);
        foreach ($sizes as $sizeId => $state) {
            Cart::addItem($product->id, $sizeId, $colorId);
        }

        return [
            'result' => 'ok',
            'total_count' => Cart::itemsCount()
        ];
    }
}
