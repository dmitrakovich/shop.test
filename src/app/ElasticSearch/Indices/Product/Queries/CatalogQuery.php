<?php

namespace App\ElasticSearch\Indices\Product\Queries;

use App\ElasticSearch\Indices\Product\AbstractProductQuery;
use App\ElasticSearch\Indices\Product\Aggregations\AggregationBuilder;
use App\ElasticSearch\Indices\Product\Enums\ProductSortEnum;
use App\ElasticSearch\Indices\Product\Filters\DefaultProductFilter;
use App\ElasticSearch\Indices\Product\Search\DefaultProductSearch;
use App\ElasticSearch\Indices\Product\Transformer\DefaultProductTransformer;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Catalog product query with aggregations.
 */
class CatalogQuery extends AbstractProductQuery
{
    /**
     * @param  DefaultProductSearch  $searchBuilder  Query builder
     * @param  DefaultProductFilter  $filterBuilder  Filter builder
     * @param  DefaultProductTransformer  $transformer  Transformer
     * @param  AggregationBuilder  $aggregationBuilder  Aggregations
     */
    public function __construct(
        DefaultProductSearch $searchBuilder,
        DefaultProductFilter $filterBuilder,
        DefaultProductTransformer $transformer,
        private readonly AggregationBuilder $aggregationBuilder
    ) {
        parent::__construct($searchBuilder, $filterBuilder, $transformer);
    }

    /**
     * Set search query text.
     *
     * @param  string|null  $search  Search text
     * @return static Current instance
     */
    public function setSearch($search): static
    {
        $this->searchValue = $search;

        return $this;
    }

    /**
     * Set catalog sort key.
     *
     * @param  string  $sort  Sort key (newness, rating, price-up, price-down)
     * @return static Current instance
     */
    public function setSort(string $sort): static
    {
        $this->sort = match ($sort) {
            ProductSortEnum::Rating->value => ProductSortEnum::Rating,
            ProductSortEnum::PriceUp->value => ProductSortEnum::PriceUp,
            ProductSortEnum::PriceDown->value => ProductSortEnum::PriceDown,
            default => ProductSortEnum::Newness,
        };

        return $this;
    }

    /**
     * Execute the Elasticsearch query.
     *
     * @return self Current instance with filled searchResult
     */
    public function search(): self
    {
        $params = $this->getParams();
        $response = $this->client->search($params) ?? [];

        $total = $response['hits']['total']['value'] ?? 0;
        $products = $response['hits']['hits'] ?? [];
        $pagination = new LengthAwarePaginator(
            [],
            $total,
            $this->pageSize,
            LengthAwarePaginator::resolveCurrentPage(),
            []
        );

        $this->searchResult = [
            'total' => $total,
            'products' => $this->prepareProducts($products),
            'buckets' => $response['aggregations'] ?? [],
            'pagination' => $pagination,
        ];

        return $this;
    }

    /**
     * Return search params with aggregations.
     *
     * @return array<string, mixed> Search API params
     */
    public function getParams(): array
    {
        $params = parent::getParams();

        if ($this->withAggregations) {
            $params['body']['aggs'] = $this->aggregationBuilder->build($this->filters);
        }

        return $params;
    }

    /**
     * Prepare products from Elasticsearch hits.
     *
     * @param  list<array<string, mixed>>  $products  Hits
     * @return list<\stdClass> Catalog documents
     */
    protected function prepareProducts(array $products): array
    {
        return $this->transformProducts($products);
    }

    /**
     * Get search result payload.
     *
     * @return array<string, mixed> Result of search()
     */
    public function getSearchResult(): array
    {
        return $this->searchResult;
    }
}
