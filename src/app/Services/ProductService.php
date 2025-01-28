<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Session;

class ProductService
{
    /**
     * Применить фильтры к выборке
     */
    public function applyFilters(array $filters): Builder
    {
        $query = (new Product())->newQuery();

        foreach ($filters as $filterName => $filterValues) {
            if (class_exists($filterName) && method_exists($filterName, 'applyFilter')) {
                $query = $filterName::applyFilter($query, $filterValues);
            } else {
                continue;
            }
        }

        return $query;
    }

    /**
     * Load the relationships that should be eager loaded.
     */
    public function addEager(CursorPaginator|LengthAwarePaginator|EloquentCollection $products): void
    {
        $products->load([
            'category:id,parent_id,title,path',
            'category.parentCategory:id,parent_id,title,path',
            'brand:id,name',
            'sizes:id,name',
            'media',
            'favorite:product_id',
        ]);
    }

    public function getById(array $ids): EloquentCollection
    {
        return Product::query()->whereIn('id', $ids)->with([
            'category:id,title,path',
            'brand:id,name',
            'sizes:id,name',
            'media',
            'styles:id,name',
            'favorite:product_id',
        ])
            ->get()
            ->each(function (Product $product) {
                $product->dataLayer = GoogleTagManagerService::prepareProduct($product);
            });
    }

    /**
     * Get products collection for feed
     *
     * @param  bool  $withTrashed
     */
    public function getForFeed($withTrashed = false): EloquentCollection
    {
        return Product::with([
            'category',
            'sizes:id,name',
            'media',
            'brand:id,name',
            'colors:id,name',
        ])
            ->when($withTrashed, function ($query) {
                $query->withTrashed();
            })
            ->has('brand')
            ->has('colors')
            ->where('price', '>', 0)
            ->get();
    }

    /**
     * Return recommended products
     */
    public function getRecommended(): EloquentCollection
    {
        return $this->getById(
            Product::inRandomOrder()->limit(5)->pluck('id')->toArray()
        );
    }

    /**
     * Add product to recent
     */
    public function addToRecent(int $productId): void
    {
        $recentProducts = Session::get('recent_products', []);
        foreach ($recentProducts as $key => $id) {
            if ($id == $productId) {
                unset($recentProducts[$key]);
            }
        }
        array_push($recentProducts, $productId);
        $recentProducts = array_values(array_slice($recentProducts, 0, 20));
        Session::put('recent_products', $recentProducts);
        Session::save();
    }

    /**
     * Get product to recent
     */
    public function getRecent(): array
    {
        return Session::get('recent_products', []);
    }
}
