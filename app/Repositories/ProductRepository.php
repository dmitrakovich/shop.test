<?php

namespace App\Repositories;

use App\Product as Model;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends CoreRepository
{
    protected function getModelClass()
    {
        return Model::class;
    }
    /**
     * Получить товары для вывода пагинатором
     *
     * @param integer|null $perPage
     * @return Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithPaginate(?int $perPage = null)
    {
        $columns = ['id', 'category_id', 'title'];

        $result = $this
            ->startConditions()
            ->select($columns)
            ->orderBy('id', 'DESC')
            ->with([
                'category:id,title',
                'images' => function ($query) {
                    $query->select('product_id', 'img')
                        ->orderBy('sorting', 'asc');
                },
            ])
            ->paginate($perPage);

        return $result;
    }
}
