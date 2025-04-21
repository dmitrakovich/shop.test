<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Filterable
{
    public static function getFilters(): array;

    public static function applyFilter(Builder $builder, array $values): Builder;

    public static function beforeApplyFilter(Builder &$builder, array &$values): void;

    public function url(): MorphOne;

    public function delete();

    public function getModelAttribute(): string;

    public function isInvisible(): bool;

    public function getBadgeName(): string;
}
