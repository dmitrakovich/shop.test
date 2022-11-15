<?php

namespace App\Http\Controllers\Shop;

use App\Facades\Cart;
use App\Facades\Sale;
use App\Models\Country;
use App\Models\Guest;
use App\Models\Orders\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\GoogleTagManagerService;
use App\Services\ProductService;
use Deliveries\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Payments\PaymentMethod;

class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GoogleTagManagerService $gtmService)
    {
        $cart = Cart::withData();
        Sale::applyForCart($cart);

        /** @var User $user */
        $user = auth()->user() ?? new User();

        $deliveriesList = DeliveryMethod::where('active', true)->pluck('name', 'id');
        $paymentsList = PaymentMethod::where('active', true)->pluck('name', 'id');

        if (!Sale::hasFitting()) {
            unset($deliveriesList['BelpostCourierFitting']);
        }
        if (!Sale::hasInstallment()) {
            unset($paymentsList['Installment']);
        }

        $countries = Country::getAll();
        $currentCountry = Country::getCurrent();

        $gtmService->setViewForCart($cart);

        return view('shop.cart', compact(
            'cart', 'user', 'deliveriesList', 'paymentsList', 'countries', 'currentCountry'
        ));
    }

    /**
     * Delete cart item
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request, GoogleTagManagerService $gtmService, int $itemId)
    {
        $item = Cart::items()->findOrFail($itemId);
        $gtmService->setProductRemoveFlashEvent($item->product, $item->count);
        $item->delete();

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
            'total_count' => Cart::itemsCount(),
        ];
    }

    /**
     * Final cart page (order info)
     *
     * @return \Illuminate\Http\Response
     */
    public function final(ProductService $productService, GoogleTagManagerService $gtmService)
    {
        if (!Session::has('order_id')) {
            return redirect()->route('orders.index');
        }
        $order = Order::with('items')->findOrFail(Session::get('order_id'));
        Guest::setData($order->only(['first_name', 'last_name', 'email', 'phone']));

        $gtmService->setViewForOrder();
        $gtmService->setPurchaseEvent($order->items, $order->id, $order->isOneClick());

        return view('shop.cart-done', [
            'order' => $order,
            'recommended' => $productService->getRecommended(),
        ]);
    }
}
