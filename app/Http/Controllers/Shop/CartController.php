<?php

namespace App\Http\Controllers\Shop;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::withData();
        $user = auth()->user() ?? new User();
        return view('shop.cart', compact('cart', 'user'));
    }

    public function submit(Request $request)
    {
        // dump($request->all());
        $orderInfo = [
            'orderNum' => mt_rand(),
            'totalPrice' => Cart::session(345345)->getTotal(),
            'address' => 'Брест, ' . $request->input('address'),
        ];
        Cart::clear();
        $recomended = Product::inRandomOrder()->limit(5)->get();
        return view('shop.cart-done', compact('orderInfo', 'recomended'));
    }

    public function addToCart(Request $request)
    {
        $productId = $request->input('id') ?? abort(404);
        $sizeId = $request->input('size_id') ?? abort(404);
        $colorId = $request->input('color_id') ?? abort(404);

        // Product::where('id', $request->input('id'))
            /*->whereHas('sizes', function ($query) use ($request) {
                $query->where('sizes.id', $request->input('size_id'));
            })*/
            /*->whereHas(function ($query) use ($request) {
                $query->where("$relationTable.id", $request->input('id')));
            })*/
            // ->first(['id']);

        $product = Product::findOrFail($productId);
        Cart::addItem($product->id, $sizeId, $colorId);

        return [
            'result' => 'ok',
            'total_count' => Cart::itemsCount()
        ];
    }
}
