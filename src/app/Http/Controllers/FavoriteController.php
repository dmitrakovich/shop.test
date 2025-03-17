<?php

namespace App\Http\Controllers;

use App\Events\Analytics\AddToCart;
use App\Models\Product;
use App\Services\FavoriteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(private readonly FavoriteService $favoriteService) {}

    /**
     * Display a listing of the favorites.
     */
    public function index(): View
    {
        return view('dashboard.favorites', [
            'products' => $this->favoriteService->getProducts(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): array
    {
        /** @var Product */
        $product = Product::query()->findOrFail($request->integer('productId'));
        $favorite = $this->favoriteService->addProduct($product);

        event($event = new AddToCart($product));

        return [
            'result' => 'ok',
            'favorite_id' => $favorite->id,
            'event_id' => $event->eventId,
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $favorite): void
    {
        $this->favoriteService->removeProduct($favorite);
    }
}
