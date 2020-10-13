<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::session(345345)->getContent();
        $user = auth()->user() ?? new User();
        return view('shop.cart', compact('items', 'user'));
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
        $product = Product::findOrFail($request->input('id'));

        $userID = 345345;

        Cart::session($userID)->add([
            'id' => $product->id, // $product->size !!!!
            'name' => $product->getFullName(),
            'price' => $product->product_price,
            'quantity' => 1,
            'attributes' => [],
            'associatedModel' => $product
        ]);
        
        return [
            'result' => 'ok',
            'total_count' => Cart::getTotalQuantity()
        ];
    }
}
