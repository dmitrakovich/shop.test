<?php

namespace App\Http\Controllers\Shop;

use App\Models\User;
use App\Facades\Cart;
use App\Facades\Sale;
use App\Models\Order;
use App\Models\Product;
use Payments\PaymentMethod;
use Illuminate\Http\Request;
use Deliveries\DeliveryMethod;
use Illuminate\Support\Facades\Session;

class CartController extends BaseController
{
    public function index()
    {
        $cart = Cart::withData();
        Sale::applyForCart($cart);

        $user = auth()->user() ?? new User();

        $deliveriesList = DeliveryMethod::where('active', true)->pluck('name', 'class');
        $paymentsList = PaymentMethod::where('active', true)->pluck('name', 'class');

        if (!Sale::hasFitting()) {
            unset($deliveriesList['BelpostCourierFitting']);
        }
        if (!Sale::hasInstallment()) {
            unset($paymentsList['Installment']);
        }

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

    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id') ?? abort(404);
        $sizes = $request->input('sizes') ?? abort(404);

        $product = Product::findOrFail($productId);
        foreach ($sizes as $sizeId => $state) {
            Cart::addItem($product->id, $sizeId);
        }

        return [
            'result' => 'ok',
            'total_count' => Cart::itemsCount()
        ];
    }

    /**
     * Final cart page (order info)
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function final()
    {
        if (!Session::has('order_id')) {
            return redirect()->route('orders.index');
        }

        return view('shop.cart-done', [
            'order' => Order::findOrFail(Session::get('order_id')),
            'recomended' => Product::inRandomOrder()->limit(5)->get()
        ]);
    }
}
