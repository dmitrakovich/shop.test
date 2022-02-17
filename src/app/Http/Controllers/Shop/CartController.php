<?php

namespace App\Http\Controllers\Shop;

use App\Facades\{Cart, Sale};
use App\Models\{User, Guest, Country, Product};
use Payments\PaymentMethod;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Deliveries\DeliveryMethod;
use App\Services\ProductService;
use Illuminate\Support\Facades\Session;
use App\Services\GoogleTagManagerService;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param GoogleTagManagerService $gtmService
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

        $countries = Country::get(['id', 'name', 'code', 'prefix']);
        $currentCountry = $countries->where('id', $user->getFirstAddress()->country_id)->first()
            ?? $countries->where('code', SxGeo::getCountry())->first();

        $gtmService->setViewForCart($cart);

        return view('shop.cart', compact(
            'cart', 'user', 'deliveriesList', 'paymentsList', 'countries', 'currentCountry'
        ));
    }

    /**
     * Delete cart item
     *
     * @param Request $request
     * @param GoogleTagManagerService $gtmService
     * @param integer $itemId
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
            'total_count' => Cart::itemsCount()
        ];
    }

    /**
     * Final cart page (order info)
     *
     * @param ProductService $productService
     * @param GoogleTagManagerService $gtmService
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
