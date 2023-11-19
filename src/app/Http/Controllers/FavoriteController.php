<?php

namespace App\Http\Controllers;

use App\Events\Analytics\AddToCart;
use App\Models\Device;
use App\Models\Favorite;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the favorites.
     */
    public function index(ProductService $productService): View
    {
        $favorites = Favorite::limit(100)->pluck('product_id')->toArray();
        $products = $productService->getById($favorites);

        return view('dashboard.favorites', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** @var Product */
        $product = Product::findOrFail((int)$request->input('productId'));
        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            'device_id' => Device::getId(),
            'product_id' => $product->id,
        ]);

        event($event = new AddToCart($product));

        return [
            'result' => 'ok',
            'favorite_id' => $favorite->id,
            'event_id' => $event->eventId,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $productId = (int)$request->route('favorite');

        return Favorite::where('product_id', $productId)->delete();
    }
}
