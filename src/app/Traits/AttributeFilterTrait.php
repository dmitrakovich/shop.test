<?php

namespace App\Traits;

use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait AttributeFilterTrait
{
    /**
     * Применить фильтр
     *
     * @param  Builder<Model>  $builder
     * @param  array<array-key, mixed>  $values
     * @return Builder<Model>
     */
    public static function applyFilter(Builder $builder, array $values): Builder
    {
        self::beforeApplyFilter($builder, $values);

        if (!$values) {
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

    /**
     * Relation column name in product table
     */
    protected static function getRelationColumn(): ?string
    {
        return null;
    }

    /**
     * Autogenerate relation name by model class
     */
    protected static function getRelationNameByClass(): string
    {
        return Str::snake(class_basename(self::class)) . 's';
    }

    /**
     * Slug для фильтра
     *
     * @return MorphOne<Url, $this>
     */
    public function url(): MorphOne
    {
        return $this->morphOne(Url::class, 'model');
    }

    /**
     * Удалить отношение при удалении фильтра
     */
    public function delete()
    {
        $this->url()->delete();

        return parent::delete();
    }

    /**
     * Если перед применением фильтра необходимо произвести
     * преобразование над данными или запросом
     *
     * @param  Builder<Model>  $builder
     * @param  array<array-key, mixed>  $values
     */
    public static function beforeApplyFilter(Builder &$builder, array &$values): void
    {
        if (!array_is_list($values)) {
            $values = array_column($values, 'model_id');
        }
    }

    /**
     * Return model class name as property
     */
    public function getModelAttribute(): string
    {
        return self::class;
    }

    /**
     * Mark filter as invisible
     */
    public function isInvisible(): bool
    {
        return false;
    }

    /**
     * Generate filter badge name
     */
    public function getBadgeName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFilters(): array
    {
        return (new self())->newQuery()
            ->get()
            ->makeHidden(['created_at', 'updated_at'])
            ->keyBy('slug')
            ->append(['model'])
            ->toArray();
    }
}
