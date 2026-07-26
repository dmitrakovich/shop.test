<?php

namespace App\ElasticSearch\Indices\Product;

use App\ElasticSearch\AbstractElasticIndex;
use App\ElasticSearch\Indices\Product\Enums\ProductSortEnum;
use App\ElasticSearch\Indices\Product\Filters\DefaultProductFilter;
use App\ElasticSearch\Indices\Product\Search\DefaultProductSearch;
use App\ElasticSearch\Indices\Product\Transformer\DefaultProductTransformer;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base for product Elasticsearch search queries.
 */
abstract class AbstractProductQuery extends AbstractElasticIndex
{
    /** @var string Elasticsearch index name */
    protected $elasticIndex = 'products';

    /** @var bool Track total hits */
    protected $trackTotalHits = true;

    /** @var bool Track scores */
    protected $trackScores = true;

    /** @var bool Include pagination in result */
    protected bool $withPagination = true;

    /** @var bool Include aggregations */
    protected bool $withAggregations = true;

    /** @var string|null Search text */
    protected ?string $searchValue = null;

    /** @var array<string, mixed> Active filters */
    protected array $filters = [];

    /**
     * @param  DefaultProductSearch  $searchBuilder  Query builder
     * @param  DefaultProductFilter  $filterBuilder  Filter builder
     * @param  DefaultProductTransformer  $transformer  Document transformer
     */
    public function __construct(
        protected readonly DefaultProductSearch $searchBuilder,
        protected readonly DefaultProductFilter $filterBuilder,
        protected readonly DefaultProductTransformer $transformer
    ) {
        parent::__construct();
        $this->elasticIndex = (string)config('services.search.product_index', 'products');
        $this->pageSize = (int)config('catalog.page_size', 12);
        $this->sort = ProductSortEnum::Newness;
    }

    /**
     * Build Elasticsearch search API parameters.
     *
     * @return array<string, mixed> Search API params
     */
    public function getParams(): array
    {
        $params = [];
        $params['index'] = $this->elasticIndex;
        $params['track_total_hits'] = $this->trackTotalHits;
        $params['track_scores'] = $this->trackScores;
        $params['body'] = [];
        $params['body']['query'] = $this->searchBuilder->getSearchQuery($this->searchValue);
        $params['body']['post_filter'] = [];
        $params['body']['post_filter']['bool'] = [];
        $params['body']['post_filter']['bool']['filter'] = $this->filterBuilder->getFilter($this->filters);
        $params['from'] = $this->page * $this->pageSize;
        $params['size'] = $this->pageSize;

        $sortConfig = $this->getSortConfig();
        $sortOpts = ['order' => $sortConfig['order']];
        if (array_key_exists('missing', $sortConfig)) {
            $sortOpts['missing'] = $sortConfig['missing'];
        }
        $params['body']['sort'] = [
            $sortConfig['field'] => $sortOpts,
        ];

        return $params;
    }

    /**
     * Get total document count without fetching hits.
     *
     * @return int Found documents count
     */
    public function getTotalOnly(): int
    {
        $oldTrackTotalHits = $this->trackTotalHits;
        $oldTrackScores = $this->trackScores;
        $oldWithAggregations = $this->withAggregations;
        $oldWithPagination = $this->withPagination;
        $oldPageSize = $this->pageSize;

        $this->trackTotalHits = true;
        $this->trackScores = false;
        $this->withAggregations = false;
        $this->withPagination = false;
        $this->pageSize = 0;

        $response = $this->client->search($this->getParams()) ?? [];
        $total = $response['hits']['total']['value'] ?? 0;

        $this->trackTotalHits = $oldTrackTotalHits;
        $this->trackScores = $oldTrackScores;
        $this->withAggregations = $oldWithAggregations;
        $this->withPagination = $oldWithPagination;
        $this->pageSize = $oldPageSize;

        return (int)$total;
    }

    /**
     * Check whether at least one product matches filters.
     *
     * @return bool Whether matches exist
     */
    public function hasAny(): bool
    {
        $oldTrackTotalHits = $this->trackTotalHits;
        $oldTrackScores = $this->trackScores;
        $oldWithAggregations = $this->withAggregations;
        $oldWithPagination = $this->withPagination;
        $oldPageSize = $this->pageSize;

        $this->trackTotalHits = false;
        $this->trackScores = false;
        $this->withAggregations = false;
        $this->withPagination = false;
        $this->pageSize = 1;

        $response = $this->client->search($this->getParams()) ?? [];
        $hits = $response['hits']['hits'] ?? [];

        $this->trackTotalHits = $oldTrackTotalHits;
        $this->trackScores = $oldTrackScores;
        $this->withAggregations = $oldWithAggregations;
        $this->withPagination = $oldWithPagination;
        $this->pageSize = $oldPageSize;

        return $hits !== [];
    }

    /**
     * Resolve sort field configuration.
     *
     * @return array{field: string, order: string, missing?: string} Field and order
     */
    protected function getSortConfig(): array
    {
        return match ($this->sort) {
            ProductSortEnum::Rating => [
                'field' => 'rating',
                'order' => 'desc',
            ],
            ProductSortEnum::PriceUp => [
                'field' => 'price',
                'order' => 'asc',
                'missing' => '_last',
            ],
            ProductSortEnum::PriceDown => [
                'field' => 'price',
                'order' => 'desc',
                'missing' => '_last',
            ],
            default => [
                'field' => 'newness_rating',
                'order' => 'desc',
            ],
        };
    }

    /**
     * Set catalog filters.
     *
     * @param  array<string, mixed>  $filters  Filters
     * @return self Current instance
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Execute search and return result payload.
     *
     * @return array<string, mixed> total, data, buckets, meta
     */
    public function getSearchResult(): array
    {
        $result = [];
        $response = $this->client->search($this->getParams()) ?? [];
        $products = (array)($response['hits']['hits'] ?? []);

        if ($this->trackTotalHits) {
            $total = $response['hits']['total']['value'] ?? 0;
            $result['total'] = $total;
            if ($this->withPagination) {
                $pagination = new LengthAwarePaginator(
                    [],
                    $total,
                    $this->pageSize,
                    LengthAwarePaginator::resolveCurrentPage(),
                    []
                );
                $result['meta'] = $pagination;
            }
        }

        if ($this->withAggregations) {
            $result['buckets'] = $response['aggregations'] ?? [];
        }

        $result['data'] = $this->transformProducts($products);

        return $result;
    }

    /**
     * Transform hits into catalog objects.
     *
     * @param  list<array<string, mixed>>  $products  ES hits
     * @return list<\stdClass> Catalog documents
     */
    protected function transformProducts(array $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $result[] = $this->transformer->transform($product);
        }

        return $result;
    }
}
