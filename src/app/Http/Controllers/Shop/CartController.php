<?php

namespace App\Http\Controllers\Shop;

use App\Events\Analytics\AddToCart;
use App\Events\Analytics\Purchase;
use App\Facades\Cart;
use App\Facades\Sale;
use App\Models\Country;
use App\Models\Guest;
use App\Models\Orders\Order;
use App\Models\Product;
use App\Models\User\User;
use App\Services\CartService;
use App\Services\GoogleTagManagerService;
use App\Services\ProductService;
use App\Services\SliderService;
use Deliveries\DeliveryMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Payments\PaymentMethod;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GoogleTagManagerService $gtmService, CartService $cartService): View
    {
        $cart = Cart::getCart();
        $prices = $cartService->getCartPrices($cart);

        /** @var User $user */
        $user = auth()->user() ?? new User();

        $countryCode = SxGeo::getCountry();
        $countries = Country::getAll();
        $currentCountry = Country::getCurrent();

        $deliveryMethods = DeliveryMethod::active()
            ->filterFitting(Sale::hasFitting())
            ->filterByCountry($countryCode)
            ->get();

        $paymentsList = PaymentMethod::active()
            ->filterInstallment($cart->availableInstallment() && Sale::hasInstallment())
            ->filterByCountry($countryCode)
            ->pluck('name', 'id');

        $gtmService->setViewForCart($cart);

        return view('shop.cart', array_merge($prices, compact(
            'cart', 'user', 'deliveryMethods', 'paymentsList', 'countries', 'currentCountry'
        )));
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

        event($event = new AddToCart($product));

        return [
            'result' => 'ok',
            'event_id' => $event->eventId,
            'total_count' => Cart::itemsCount(),
        ];
    }

    /**
     * Final cart page (order info)
     *
     * @return \Illuminate\Http\Response
     */
    public function final(
        ProductService $productService,
        SliderService $sliderService
    ) {
        if (!Session::has('order_id')) {
            return redirect()->route('orders.index');
        }
        $order = Order::with('items', 'payment')->findOrFail(Session::get('order_id'));
        Guest::setData($order->only(['first_name', 'last_name', 'email', 'phone']));

        event(new Purchase($order));

        return view('shop.cart-done', [
            'order' => $order,
            'recommended' => $productService->getRecommended(),
            'finalSliders' => $sliderService->getFinalOrderPageSliders($order),
        ]);
    }
}
