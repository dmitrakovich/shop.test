<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface Filterable
{
    /** @return array<string, mixed> */
    public static function getFilters(): array;

    /**
     * @param  Builder<TModel>  $builder
     * @param  array<string, mixed>  $values
     * @return Builder<TModel>
     */
    public static function applyFilter(Builder $builder, array $values): Builder;

    /**
     * @param  Builder<TModel>  $builder
     * @param  array<string, mixed>  $values
     */
    public static function beforeApplyFilter(Builder &$builder, array &$values): void;

    public function getModelAttribute(): string;

    public function isInvisible(): bool;

    public function getBadgeName(): string;
}
