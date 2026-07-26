<?php

namespace App\ElasticSearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class AbstractElasticIndex
{
    /** @var string Elasticsearch index name */
    protected $elasticIndex;

    /** @var Client Elasticsearch client */
    protected Client $client;

    /** @var string|null Search query text */
    protected ?string $search = null;

    /** @var array<string, mixed> Search result payload */
    protected array $searchResult = [];

    /** @var int Zero-based page index */
    protected int $page = 0;

    /** @var int Page size */
    protected int $pageSize = 60;

    /** @var mixed Sort key (string or enum) */
    protected $sort = null;

    /** @var int Maximum result window */
    private int $maxResultWindow = 100000;

    /**
     * Initialize Elasticsearch client from config/services.php.
     */
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('services.search.hosts'))
            ->build();
    }

    /**
     * Delete the Elasticsearch index.
     *
     * @return static Current instance
     */
    public function deleteIndex(): static
    {
        $this->client->indices()->delete(['index' => $this->elasticIndex]);

        return $this;
    }

    /**
     * Set page number (1-based input).
     *
     * @param  int  $page  Page number
     * @return static Current instance
     */
    public function setPage(int $page): static
    {
        $page = ((int)$page) - 1;
        $page = ($page >= 0 && (($page * $this->pageSize) <= $this->maxResultWindow)) ? $page : 0;
        $this->page = $page;

        return $this;
    }

    /**
     * Set page size.
     *
     * @param  int  $size  Page size
     * @return static Current instance
     */
    public function setPageSize(int $size): static
    {
        $this->pageSize = max(1, min($size, 200));

        return $this;
    }

    /**
     * Set sort key.
     *
     * @param  string  $sort  Sort key
     * @return static Current instance
     */
    public function setSort(string $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Set search query text.
     *
     * @param  string|null  $search  Search text
     * @return static Current instance
     */
    public function setSearch($search): static
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Return search result payload.
     *
     * @return array<string, mixed> Result
     */
    public function getSearchResult(): array
    {
        return $this->searchResult;
    }
}
