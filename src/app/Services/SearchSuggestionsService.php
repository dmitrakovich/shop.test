<?php

namespace App\Services;

use App\Helpers\KeyboardLayoutHelper;
use App\Models\Category;
use App\Models\Logs\SearchQueryLog;
use App\Models\Product;
use App\Services\Elasticsearch\CatalogSuggestionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SearchSuggestionsService
{
    /**
     * Shorter input keeps the empty-field state.
     */
    private const int MIN_QUERY_LENGTH = 2;

    private const int LIMIT = 8;

    private const int TOP_QUERIES_DAYS = 30;

    public function __construct(
        private readonly CatalogSuggestionService $catalogSuggestionService,
        private readonly ProductService $productService,
    ) {}

    public function forQuery(string $query): SearchSuggestionsResult
    {
        $query = trim($query);
        $short = mb_strlen($query) < self::MIN_QUERY_LENGTH;

        return new SearchSuggestionsResult(
            suggestions: $short ? [] : $this->catalogSuggestionService->suggest($query),
            categories: $short ? new Collection() : $this->categories($query),
            queries: $short ? $this->topQueries() : [],
            products: $short ? $this->popularProducts() : new Collection(),
        );
    }

    /**
     * Queries that found something, ranked by how many devices searched them.
     *
     * @return list<string>
     */
    private function topQueries(): array
    {
        /** @var list<string> $queries */
        $queries = Cache::remember('search_suggestions.top_queries', now()->addMinutes(10), function (): array {
            return SearchQueryLog::query()
                ->select('query')
                ->where('created_at', '>=', now()->subDays(self::TOP_QUERIES_DAYS))
                ->where('results_count', '>', 0)
                ->groupBy('query')
                ->orderByRaw('COUNT(DISTINCT device_id) DESC')
                ->orderBy('query')
                ->limit(self::LIMIT)
                ->pluck('query')
                ->map(static fn (mixed $query): string => (string)$query)
                ->all();
        });

        return $queries;
    }

    /**
     * @return Collection<int, Product>
     */
    private function popularProducts(): Collection
    {
        $products = Product::query()
            ->orderByDesc('rating')
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get();

        $this->productService->addEager($products);

        return $products;
    }

    /**
     * Categories whose title contains the query as typed or in the other keyboard layout,
     * shortest titles first.
     *
     * @return Collection<int, Category>
     */
    private function categories(string $query): Collection
    {
        $needles = array_filter([$query, KeyboardLayoutHelper::switch($query)]);

        return Category::query()
            ->withoutGlobalScope('order')
            ->whereKeyNot(Category::ROOT_CATEGORY_ID)
            ->where(function (Builder $builder) use ($needles): void {
                foreach ($needles as $needle) {
                    $builder->orWhere('title', 'like', '%' . addcslashes($needle, '%_\\') . '%');
                }
            })
            ->orderByRaw('CHAR_LENGTH(title)')
            ->orderBy('order')
            ->limit(self::LIMIT)
            ->get(['id', 'title', 'slug', 'path']);
    }
}
