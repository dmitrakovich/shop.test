<?php

namespace Tests\Feature\Console;

use App\Services\Elasticsearch\CatalogSuggestionBuilder;
use Elastic\Adapter\Documents\Document;
use Elastic\Adapter\Documents\DocumentManager;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class CatalogSuggestionsRebuildCommandTest extends TestCase
{
    public function test_it_upserts_phrases_then_drops_stale_documents(): void
    {
        config(['catalog.elasticsearch.suggestions_alias' => 'catalog_suggestions']);

        $builder = Mockery::mock(CatalogSuggestionBuilder::class);
        $builder->shouldReceive('build')->once()->andReturn([
            [
                'text' => 'ботинки',
                'inputs' => ['ботинки'],
                'weight' => 143,
            ],
        ]);
        $this->app->instance(CatalogSuggestionBuilder::class, $builder);

        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('index')
            ->once()
            ->ordered()
            ->with(
                'catalog_suggestions',
                Mockery::on(function (Collection $indexed): bool {
                    /** @var Document $document */
                    $document = $indexed->first();

                    return $indexed->count() === 1
                        && $document->id() === md5('ботинки')
                        && $document->content('text') === 'ботинки'
                        && $document->content('suggest.input') === ['ботинки']
                        && $document->content('suggest.weight') === 143;
                }),
                true,
            )
            ->andReturnSelf();
        $documents->shouldReceive('deleteByQuery')
            ->once()
            ->ordered()
            ->with(
                'catalog_suggestions',
                ['bool' => ['must_not' => ['ids' => ['values' => [md5('ботинки')]]]]],
                true,
            )
            ->andReturnSelf();
        $this->app->instance(DocumentManager::class, $documents);

        $this->artisan('catalog:suggestions-rebuild')
            ->expectsOutputToContain('Indexed 1 suggestions into [catalog_suggestions].')
            ->assertSuccessful();
    }

    public function test_empty_catalog_clears_suggestions_without_indexing(): void
    {
        config(['catalog.elasticsearch.suggestions_alias' => 'catalog_suggestions']);

        $builder = Mockery::mock(CatalogSuggestionBuilder::class);
        $builder->shouldReceive('build')->once()->andReturn([]);
        $this->app->instance(CatalogSuggestionBuilder::class, $builder);

        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldNotReceive('index');
        $documents->shouldReceive('deleteByQuery')
            ->once()
            ->with(
                'catalog_suggestions',
                ['bool' => ['must_not' => ['ids' => ['values' => []]]]],
                true,
            )
            ->andReturnSelf();
        $this->app->instance(DocumentManager::class, $documents);

        $this->artisan('catalog:suggestions-rebuild')
            ->expectsOutputToContain('Indexed 0 suggestions into [catalog_suggestions].')
            ->assertSuccessful();
    }
}
