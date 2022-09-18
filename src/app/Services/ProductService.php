<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Session;

class ProductService
{
    /**
     * Применить фильтры к выборке
     */
    protected function applyFilters(array $filters): Builder
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
     * Return built query
     */
    public function getForCatalog(array $filters, string $sort, ?string $search = null): Builder
    {
        return $this->applyFilters($filters)
            ->with([
                'category:id,parent_id,title,path',
                'brand:id,name',
                'sizes:id,name',
                'media',
                'styles:id,name',
                'favorite:product_id',
            ])
            ->search($search)
            ->sorting($sort);
    }

    public function getById(array $ids): EloquentCollection
    {
        return Product::whereIn('id', $ids)->with([
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
     * @param boolean $withTrashed
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
