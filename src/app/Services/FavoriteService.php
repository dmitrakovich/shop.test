<?php

namespace App\Services;

use App\Facades\Device;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class FavoriteService
{
    public function __construct(
        private readonly Favorite $favorite,
        private readonly ProductService $productService
    ) {}

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        $productIds = $this->favorite
            ->forUser()
            ->limit(100)
            ->pluck('product_id')
            ->toArray();

        return $this->productService->getById($productIds);
    }

    public function addProduct(Product $product): Favorite
    {
        return $this->favorite->newQuery()->updateOrCreate([
            'user_id' => Auth::id(),
            'device_id' => Device::id(),
            'product_id' => $product->id,
        ]);
    }

    public function removeProduct(Product $product): void
    {
        $this->favorite->forUser()->where('product_id', $product->id)->delete();
    }
}
