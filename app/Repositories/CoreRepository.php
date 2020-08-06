<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CoreRepository
 * 
 * Репозиторий работы с сущностью.
 * Может выдавать наборы данных.
 * Не может создавать/изменять сущности.
 */
abstract class CoreRepository
{
    /**
     * Model
     *
     * @var Model
     */
    protected $model;
    /**
     * CoreRepository construct
     */
    function __construct()
    {
        $this->model = app($this->getModelClass());
    }
    /**
     * Получить модель класса
     *
     * @return mixed
     */
    abstract protected function getModelClass();
    /**
     * Получение всегда пустого объекта класса, котрый не хранит состояние
     *
     * @return Model|Illuminate\Foundation\Application|mixed
     */
    protected function startConditions()
    {
        return clone $this->model;
    }
}
