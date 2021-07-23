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
        $IdList = $values; // array_keys($values);

        self::beforeApplyFilter($builder, $IdList);

        if ($relationColumn = self::getRelationColumn()) {
            if (count($IdList) == 1) {
                return $builder->where($relationColumn, $IdList[0]);
            } else {
                return $builder->whereIn($relationColumn, $IdList);
            }
        }

        $relationTable = $relationName = self::getRelationNameByClass();

        return $builder->whereHas($relationName, function ($query) use ($IdList, $relationTable) {
            if (count($IdList) == 1) {
                $query->where("$relationTable.id", $IdList[0]);
            } else {
                $query->whereIn("$relationTable.id", $IdList);
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
