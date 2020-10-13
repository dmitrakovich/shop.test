<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait AttributeFilterTrait
{
    // protected static $relationName;
    // protected static $relationTable;
    /**
     * Применить фильтр
     *
     * @param Builder $builder
     * @param array $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
    {
        $relationName = self::$relationName ?? self::getRelationNameByClass();
        $relationTable = self::$relationTable ?? $relationName;

        self::beforeApplyFilter($builder, $values);

        return $builder->whereHas($relationName, function ($query) use ($values, $relationTable) {
            if (count($values) == 1) {
                $query->where("$relationTable.id", $values[0]);
            } else {
                $query->whereIn("$relationTable.id", $values);
            }
        });
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
    public function slug()
    {
        return $this->morphOne(Slug::class, 'attribute');
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
