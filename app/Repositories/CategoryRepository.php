<?php

namespace App\Repositories;

use App\Models\Category as Model;
use Illuminate\Database\Eloquent\Collection;

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
}
