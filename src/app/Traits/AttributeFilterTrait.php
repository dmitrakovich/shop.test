<?php

namespace App\Traits;

use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait AttributeFilterTrait
{
    /**
     * Применить фильтр
     *
     * @param Builder $builder
     * @param array $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
    {
        self::beforeApplyFilter($builder, $values);

        if ($values === false) {
            return $builder;
        }

        if ($relationColumn = self::getRelationColumn()) {
            if (count($values) == 1) {
                return $builder->where($relationColumn, $values[0]);
            } else {
                return $builder->whereIn($relationColumn, $values);
            }
        }

        $relationTable = $relationName = self::getRelationNameByClass();

        return $builder->whereHas($relationName, function ($query) use ($values, $relationTable) {
            if (count($values) == 1) {
                $query->where("$relationTable.id", $values[0]);
            } else {
                $query->whereIn("$relationTable.id", $values);
            }
        });
    }

    protected static function getRelationColumn()
    {
        return null;
    }

    protected static function getRelationNameByClass()
    {
        return Str::snake(class_basename(self::class)) . 's';;
    }
    /**
     * Slug для фильтра
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function url()
    {
        return $this->morphOne(Url::class, 'model');
    }
    /**
     * Удалить отношение при удалении фильтра
     *
     * @return void
     */
    public function delete()
    {
        $this->url()->delete();
        return parent::delete();
    }
    /**
     * Если перед применением фильтра необходимо произветсти
     * преобразолвание над данными или запросом
     *
     * @param Builder $builder
     * @param array $values
     * @return void
     */
    public static function beforeApplyFilter(Builder &$builder, array &$values)
    {
        # code...
    }
}
