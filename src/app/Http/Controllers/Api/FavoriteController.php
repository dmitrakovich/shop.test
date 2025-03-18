<?php

namespace App\Http\Controllers\Api;

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

    public function toggle(Product $product): AnonymousResourceCollection
    {
        $this->favoriteService->toggleProduct($product);

        return $this->index();
    }
}
