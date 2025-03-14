<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function __construct(private readonly Product $model) {}

    /**
     * @param  array<int, int>  $ids
     * @return Collection|Product[]
     */
    public function getForSliderByIds(array $ids): Collection
    {
        return $this->model->newQuery()
            ->withTrashed()
            ->with(['media', 'category', 'brand', 'styles', 'favorite'])
            ->whereIn('id', $ids)
            ->get();
    }
}
