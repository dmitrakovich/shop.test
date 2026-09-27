<?php

namespace Tests\Unit\Elastic;

use App\Services\Elasticsearch\CatalogSuggestionService;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchResult;
use Mockery;
use Tests\TestCase;

class CatalogSuggestionServiceTest extends TestCase
{
    private CatalogSuggestionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['catalog.elasticsearch.suggestions_alias' => 'catalog_suggestions']);
        $this->service = new CatalogSuggestionService(
            Mockery::mock(DocumentManager::class),
        );
    }

    public function test_cyrillic_query_keeps_the_original_and_adds_the_switched_layout(): void
    {
        $suggest = $this->service->buildSearchParameters('бот')->toArray()['body']['suggest'];

        $this->assertSame(['phrases', 'phrases_layout'], array_keys($suggest));
        $this->assertSame('бот', $suggest['phrases']['prefix']);
        $this->assertSame(',jn', $suggest['phrases_layout']['prefix']);
        $this->assertSame('suggest', $suggest['phrases']['completion']['field']);
        $this->assertSame(10, $suggest['phrases']['completion']['size']);
        $this->assertTrue($suggest['phrases']['completion']['skip_duplicates']);
        $this->assertSame('AUTO', $suggest['phrases']['completion']['fuzzy']['fuzziness']);
        $this->assertTrue($suggest['phrases']['completion']['fuzzy']['unicode_aware']);
    }

    public function test_digits_do_not_add_a_layout_suggester(): void
    {
        $suggest = $this->service->buildSearchParameters('38')->toArray()['body']['suggest'];

        $this->assertSame(['phrases'], array_keys($suggest));
        $this->assertSame('38', $suggest['phrases']['prefix']);
    }

    public function test_wrong_layout_adds_a_second_suggester(): void
    {
        $params = $this->service->buildSearchParameters(',jnbyrb')->toArray();

        $this->assertSame(['catalog_suggestions'], explode(',', $params['index']));
        $this->assertSame(',jnbyrb', $params['body']['suggest']['phrases']['prefix']);
        $this->assertSame('ботинки', $params['body']['suggest']['phrases_layout']['prefix']);
        $this->assertSame(['text'], $params['body']['_source']);
        $this->assertSame(0, $params['body']['size']);
    }

    public function test_original_suggestions_win_over_the_switched_layout(): void
    {
        $service = $this->serviceReturning([
            'phrases' => [[
                'text' => 'бот',
                'offset' => 0,
                'length' => 3,
                'options' => [
                    ['text' => 'ботинки', '_source' => ['text' => 'ботинки']],
                    ['text' => 'ботинки черные', '_source' => ['text' => 'ботинки черные']],
                    ['text' => 'ботинки', '_source' => ['text' => 'ботинки']],
                ],
            ]],
            'phrases_layout' => [[
                'text' => 'бот',
                'offset' => 0,
                'length' => 3,
                'options' => [
                    ['text' => 'сумка', '_source' => ['text' => 'сумка']],
                ],
            ]],
        ]);

        $this->assertSame(['ботинки', 'ботинки черные'], $service->suggest('бот'));
    }

    public function test_switched_layout_is_used_when_the_original_has_no_options(): void
    {
        $service = $this->serviceReturning([
            'phrases' => [[
                'text' => ',jn',
                'offset' => 0,
                'length' => 3,
                'options' => [],
            ]],
            'phrases_layout' => [[
                'text' => 'бот',
                'offset' => 0,
                'length' => 3,
                'options' => [
                    ['text' => 'ботинки', '_source' => ['text' => 'ботинки']],
                ],
            ]],
        ]);

        $this->assertSame(['ботинки'], $service->suggest(',jn'));
    }

    public function test_blank_query_does_not_hit_elasticsearch(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldNotReceive('search');

        $service = new CatalogSuggestionService($documents);

        $this->assertSame([], $service->suggest('   '));
    }

    /**
     * @param  array<string, mixed>  $suggest
     */
    private function serviceReturning(array $suggest): CatalogSuggestionService
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->once()
            ->andReturn(new SearchResult([
                'hits' => [
                    'total' => ['value' => 0],
                    'hits' => [],
                ],
                'suggest' => $suggest,
            ]));

        return new CatalogSuggestionService($documents);
    }
}
