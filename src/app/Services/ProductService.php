<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    /**
     * Применить фильтры к выборке
     *
     * @param array $filters
     * @return Illuminate\Database\Eloquent\Collection
     */
    protected function applyFilters(array $filters)
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
}
