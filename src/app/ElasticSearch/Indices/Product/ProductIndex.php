<?php

namespace App\ElasticSearch\Indices\Product;

use App\ElasticSearch\AbstractElasticIndex;
use Elastic\Elasticsearch\Client;
use Throwable;

/**
 * Product Elasticsearch index lifecycle (create, delete, refresh).
 */
class ProductIndex extends AbstractElasticIndex
{
    /**
     * Initialize client and product index name from config.
     */
    public function __construct()
    {
        parent::__construct();
        $this->elasticIndex = (string)config('services.search.product_index', 'products');
    }

    /**
     * Get the Elasticsearch client.
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Get the product index name.
     */
    public function name(): string
    {
        return (string)$this->elasticIndex;
    }

    /**
     * Create the product index with mapping and Russian analyzers.
     */
    public function create(): self
    {
        $namedEntityMapping = $this->namedEntityMapping();

        $this->client->indices()->create([
            'index' => $this->elasticIndex,
            'body' => [
                'settings' => [
                    'max_result_window' => 100000,
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'char_filter' => [
                            'yo_filter' => [
                                'type' => 'mapping',
                                'mappings' => ['ё=>е', 'Ё=>Е'],
                            ],
                        ],
                        'analyzer' => [
                            'my_search_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'russian_stop', 'word_delimiter'],
                                'char_filter' => ['yo_filter'],
                            ],
                            'text_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'russian_stop', 'word_delimiter'],
                                'char_filter' => ['yo_filter'],
                            ],
                        ],
                        'filter' => [
                            'russian_stop' => [
                                'type' => 'stop',
                                'stopwords' => '_russian_',
                            ],
                            'word_delimiter' => [
                                'catenate_all' => 'true',
                                'type' => 'word_delimiter',
                                'preserve_original' => 'true',
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'slug' => ['type' => 'keyword'],
                        'sku' => [
                            'type' => 'text',
                            'analyzer' => 'my_search_analyzer',
                            'fields' => [
                                'keyword' => ['type' => 'keyword'],
                            ],
                        ],
                        'short_name' => [
                            'type' => 'text',
                            'analyzer' => 'my_search_analyzer',
                        ],
                        'description' => [
                            'type' => 'text',
                            'analyzer' => 'text_analyzer',
                        ],
                        'price' => ['type' => 'float'],
                        'old_price' => ['type' => 'float'],
                        'rating' => ['type' => 'integer'],
                        'newness_rating' => ['type' => 'integer'],
                        'season_rating' => ['type' => 'integer'],
                        'sale_rating' => ['type' => 'integer'],
                        'created_at' => ['type' => 'date'],
                        'brand' => $namedEntityMapping,
                        'categories' => $namedEntityMapping,
                        'collection' => $namedEntityMapping,
                        'seasons' => $namedEntityMapping,
                        'sizes' => $namedEntityMapping,
                        'colors' => $namedEntityMapping,
                        'fabrics' => $namedEntityMapping,
                        'heels' => $namedEntityMapping,
                        'styles' => $namedEntityMapping,
                        'tags' => $namedEntityMapping,
                        'statuses' => ['type' => 'keyword'],
                        'images' => [
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'path' => ['type' => 'keyword'],
                                'url' => ['type' => 'keyword'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Delete the index when it exists.
     */
    public function deleteIfExists(): self
    {
        try {
            $exists = $this->client->indices()->exists(['index' => $this->elasticIndex]);
            $ok = method_exists($exists, 'asBool')
                ? $exists->asBool()
                : ($exists->getStatusCode() === 200);
            if ($ok) {
                $this->deleteIndex();
            }
        } catch (Throwable) {
        }

        return $this;
    }

    /**
     * Refresh the index after bulk writes.
     */
    public function refresh(): self
    {
        try {
            $this->client->indices()->refresh(['index' => $this->elasticIndex]);
        } catch (Throwable) {
        }

        return $this;
    }

    /**
     * Shared object mapping for named catalog entities.
     *
     * @return array{properties: array<string, mixed>}
     */
    private function namedEntityMapping(): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => [
                    'type' => 'text',
                    'analyzer' => 'my_search_analyzer',
                ],
                'slug' => ['type' => 'keyword'],
            ],
        ];
    }
}
