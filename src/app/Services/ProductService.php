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
    public function applyFilters(array $filters)
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
}
