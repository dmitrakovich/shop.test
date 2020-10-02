<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Product;
use Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::session(345345)->getContent();
        return view('shop.cart', compact('items'));
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
