<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Favorite\FavoritesResource;
use App\Models\Product;
use App\Services\FavoriteService;

class FavoriteController extends Controller
{
    public function __construct(private readonly FavoriteService $favoriteService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): FavoritesResource
    {
        return new FavoritesResource($this->favoriteService->getProducts());
    }

    public function toggle(Product $product): FavoritesResource
    {
        $this->favoriteService->toggleProduct($product);

        return $this->index();
    }
}
