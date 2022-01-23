<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductService
{
    /**
     * Применить фильтры к выборке
     *
     * @param array $filters
     * @return EloquentCollection
     */
    protected function applyFilters(array $filters): EloquentCollection
    {
        $query = (new Product())->newQuery();

        foreach ($filters as $filterName => $filterValues) {
            if (class_exists($filterName) && method_exists($filterName, 'applyFilter')) {
                $query = $filterName::applyFilter($query, array_column($filterValues, 'model_id'));
            } else {
                continue;
            }
        }
        return $query;
    }

    /**
     * Return built query
     *
     * @param array $filters
     * @param string $sort
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getForCatalog(array $filters, string $sort, ?string $search = null)
    {
        return $this->applyFilters($filters)
            ->with([
                'category:id,title,path',
                'brand:id,name',
                'sizes:id,name',
                'media',
                'styles:id,name',
            ])
            ->search($search)
            ->sorting($sort);
    }

    /**
     * @param array $ids
     * @return EloquentCollection
     */
    public function getById(array $ids): EloquentCollection
    {
        return Product::whereIn('id', $ids)->with([
            'category:id,title,path',
            'brand:id,name',
            'sizes:id,name',
            'media',
            'styles:id,name',
        ])->get();
    }

    /**
     * Get products collection for xml
     *
     * @return EloquentCollection
     */
    public function getForXml(): EloquentCollection
    {
        return Product::with([
            'category',
            'sizes:id,name',
            'media',
            'brand:id,name',
        ])
            ->withTrashed()
            ->limit(5) // !!!
            ->whereIn('id', ['3415', /*'3015'*/])
            ->get();
    }
}
