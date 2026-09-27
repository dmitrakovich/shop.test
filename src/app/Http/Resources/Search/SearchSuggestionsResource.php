<?php

namespace App\Http\Resources\Search;

use App\Http\Resources\Product\CatalogProductResource;
use App\Http\Resources\Product\CategoryResource;
use App\Services\SearchSuggestionsResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SearchSuggestionsResult
 */
class SearchSuggestionsResource extends JsonResource
{
    /**
     * @return array{
     *     suggestions: list<string>,
     *     categories: AnonymousResourceCollection<int, CategoryResource>,
     *     queries: list<string>,
     *     products: AnonymousResourceCollection<int, CatalogProductResource>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'suggestions' => $this->suggestions,
            'categories' => CategoryResource::collection($this->categories),
            'queries' => $this->queries,
            'products' => CatalogProductResource::collection($this->products),
        ];
    }
}
