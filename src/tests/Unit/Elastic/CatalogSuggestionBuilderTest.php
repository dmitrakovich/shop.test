<?php

namespace Tests\Unit\Elastic;

use App\Models\Category;
use App\Services\Elasticsearch\CatalogSuggestionBuilder;
use Elastic\Adapter\Documents\DocumentManager;
use Mockery;
use Tests\TestCase;

class CatalogSuggestionBuilderTest extends TestCase
{
    private CatalogSuggestionBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        config(['catalog.elasticsearch.alias' => 'catalog']);
        $this->builder = new CatalogSuggestionBuilder(
            Mockery::mock(DocumentManager::class),
        );
    }

    public function test_aggregation_counts_categories_colors_tags_and_brands(): void
    {
        $params = $this->builder->aggregationParameters()->toArray();

        $this->assertSame('catalog', $params['index']);
        $this->assertSame(0, $params['body']['size']);
        $this->assertFalse($params['body']['_source']);
        $this->assertSame('categories.id', $params['body']['aggregations']['categories']['terms']['field']);
        $this->assertSame(
            'colors.id',
            $params['body']['aggregations']['categories']['aggs']['colors']['terms']['field'],
        );
        $this->assertSame('tags.id', $params['body']['aggregations']['tags']['terms']['field']);
        $this->assertSame('brand.id', $params['body']['aggregations']['brands']['terms']['field']);
    }

    public function test_phrases_skip_the_root_and_top_level_color_combinations(): void
    {
        $phrases = $this->phrases();

        $this->assertArrayNotHasKey('каталог', $phrases);
        $this->assertSame(200, $phrases['женская обувь']['weight']);
        $this->assertArrayNotHasKey('женская обувь черные', $phrases);

        $this->assertSame(143, $phrases['ботинки']['weight']);
        $this->assertSame(
            ['ботинки черные', 'черные', 'черные ботинки'],
            $phrases['ботинки черные']['inputs'],
        );
        $this->assertSame(90, $phrases['ботинки черные']['weight']);
        $this->assertSame(['ботинки синие', 'синие', 'синие ботинки'], $phrases['ботинки синие']['inputs']);
        $this->assertSame(['ботинки голубые', 'голубые', 'голубые ботинки'], $phrases['ботинки голубые']['inputs']);
        $this->assertSame(['ботинки рыжие', 'рыжие', 'рыжие ботинки'], $phrases['ботинки рыжие']['inputs']);
        $this->assertSame(
            ['ботинки мультиколор', 'мультиколор', 'мультиколор ботинки'],
            $phrases['ботинки мультиколор']['inputs'],
        );
    }

    public function test_category_tails_tags_brands_and_duplicate_weights(): void
    {
        $phrases = $this->phrases();

        $this->assertSame(
            ['туфли на каблуке', 'на каблуке', 'каблуке'],
            $phrases['туфли на каблуке']['inputs'],
        );
        $this->assertSame(114, $phrases['лодочки']['weight']);
        $this->assertSame(['лодочки'], $phrases['лодочки']['inputs']);
        $this->assertSame('loro piana', $phrases['loro piana']['text']);
        $this->assertSame(['barocco style', 'style'], $phrases['barocco style']['inputs']);
        $this->assertSame(1002, $phrases['barocco style']['weight']);
    }

    /**
     * @return array<string, array{text: string, inputs: list<string>, weight: int}>
     */
    private function phrases(): array
    {
        $root = Category::ROOT_CATEGORY_ID;

        $phrases = $this->builder->phrasesFromAggregations(
            [
                'categories' => [
                    'buckets' => [
                        [
                            'key' => $root,
                            'doc_count' => 1500,
                            'colors' => [
                                'buckets' => [
                                    ['key' => 1, 'doc_count' => 600],
                                ],
                            ],
                        ],
                        [
                            'key' => 35,
                            'doc_count' => 200,
                            'colors' => [
                                'buckets' => [
                                    ['key' => 1, 'doc_count' => 80],
                                ],
                            ],
                        ],
                        [
                            'key' => 19,
                            'doc_count' => 143,
                            'colors' => [
                                'buckets' => [
                                    ['key' => 1, 'doc_count' => 90],
                                    ['key' => 2, 'doc_count' => 10],
                                    ['key' => 3, 'doc_count' => 9],
                                    ['key' => 4, 'doc_count' => 4],
                                    ['key' => 5, 'doc_count' => 3],
                                ],
                            ],
                        ],
                        [
                            'key' => 3,
                            'doc_count' => 48,
                            'colors' => ['buckets' => []],
                        ],
                        [
                            'key' => 12,
                            'doc_count' => 10,
                            'colors' => ['buckets' => []],
                        ],
                    ],
                ],
                'tags' => [
                    'buckets' => [
                        ['key' => 7, 'doc_count' => 114],
                        ['key' => 8, 'doc_count' => 10],
                    ],
                ],
                'brands' => [
                    'buckets' => [
                        ['key' => 4, 'doc_count' => 1002],
                    ],
                ],
            ],
            [
                $root => ['title' => 'Каталог', 'parent_id' => null],
                35 => ['title' => 'Женская обувь', 'parent_id' => $root],
                19 => ['title' => 'Ботинки', 'parent_id' => 35],
                3 => ['title' => 'Туфли на каблуке', 'parent_id' => 2],
                12 => ['title' => 'Лодочки', 'parent_id' => 35],
            ],
            [
                1 => 'черный',
                2 => 'синий',
                3 => 'голубой',
                4 => 'рыжий',
                5 => 'мультиколор',
            ],
            [
                7 => 'лодочки',
                8 => '"Loro Piana"',
            ],
            [
                4 => 'BAROCCO style',
            ],
        );

        $byText = [];
        foreach ($phrases as $phrase) {
            $byText[$phrase['text']] = $phrase;
        }

        return $byText;
    }
}
