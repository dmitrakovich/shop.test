<?php

namespace App\Http\Controllers\Shop;

use App\Facades\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends BaseController
{
    public function index()
    {
        $cart = Cart::withData();
        $user = auth()->user() ?? new User();
        $deliveriesList = [
            'BelpostCourierFitting' => 'Курьером с примеркой',
            'BelpostCourier' => 'Курьер',
            'Belpost' => 'Белпочта',
            'BelpostEMS' => 'Емс',
        ];
        $paymentsList = [
            'COD' => 'При получении',
            'Card' => 'Банковской картой',
            'ERIP' => 'Ерип',
            'Installment' => 'Оформить рассрочку',
        ];
        return view('shop.cart', compact('cart', 'user', 'deliveriesList', 'paymentsList'));
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

    public function final()
    {
        if (!Session::has('order_info')) {
            return redirect()->route('orders.index');
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
