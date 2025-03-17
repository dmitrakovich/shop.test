<?php

namespace App\Http\Controllers\Api;

use App\Events\Analytics\AddToCart;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\CatalogProductResource;
use App\Models\Product;
use App\Services\FavoriteService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function __construct(private readonly FavoriteService $favoriteService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return CatalogProductResource::collection($this->favoriteService->getProducts());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function add(Product $product): AnonymousResourceCollection
    {
        $this->favoriteService->addProduct($product);

        // event($event = new AddToCart($product));

        return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function remove(Product $product): AnonymousResourceCollection
    {
        $this->favoriteService->removeProduct($product);

        return $this->index();
    }
}
