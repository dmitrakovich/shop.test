<?php

namespace App\Contracts;

// todo: прописать остальные методы AttributeFilterTrait

interface Filterable
{
    public static function getFilters(): array;
}
