<?php

namespace App\Repositories;

use App\Models\Category as Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository extends CoreRepository
{
    protected function getModelClass()
    {
        return Model::class;
    }
    /**
     * Получить модель для редактирования
     *
     * @param integer $id
     * @return mixed
     */
    public function getEdit($id)
    {
        return $this->startConditions()->find($id);
    }
    /**
     * Получить все категории
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        if (Cache::has('categories')) {
            $categories = Cache::get('categories');
        } else {
            $categories = $this->startConditions()
                ->select('id', 'slug', 'title')
                ->get();

            Cache::put('categories', $categories);
        }
        return $categories;
    }
    /**
     * Получить категории в древовидной форме
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getTree()
    {
        if (Cache::has('categoriesTree')) {
            $categoriesTree = Cache::get('categoriesTree');
        } else {
            $categoriesTree = $this->startConditions()
                ->whereNull('parent_id')
                ->with('childrenCategories')
                ->get();

            Cache::put('categoriesTree', $categoriesTree);
        }
        return $categoriesTree;
    }
}
