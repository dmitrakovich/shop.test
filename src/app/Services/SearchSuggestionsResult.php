<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class SearchSuggestionsResult
{
    /**
     * @param  list<string>  $suggestions
     * @param  Collection<int, Category>  $categories
     * @param  list<string>  $queries
     * @param  Collection<int, Product>  $products
     */
    public function __construct(
        public array $suggestions,
        public Collection $categories,
        public array $queries,
        public Collection $products,
    ) {}
}
