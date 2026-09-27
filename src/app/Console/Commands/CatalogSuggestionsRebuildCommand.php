<?php

namespace App\Console\Commands;

use App\Services\Elasticsearch\CatalogSuggestionBuilder;
use Elastic\Adapter\Documents\Document;
use Elastic\Adapter\Documents\DocumentManager;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CatalogSuggestionsRebuildCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'catalog:suggestions-rebuild';

    /**
     * @var string
     */
    protected $description = 'Rebuild catalog search suggestions from the live catalog index';

    /**
     * Upserts current phrases before dropping stale ones, so the index is never empty mid-rebuild.
     */
    public function handle(CatalogSuggestionBuilder $builder, DocumentManager $documents): int
    {
        $alias = (string)config('catalog.elasticsearch.suggestions_alias');
        $suggestions = Collection::make($builder->build())->map(
            static fn (array $phrase): Document => new Document(md5($phrase['text']), [
                'text' => $phrase['text'],
                'suggest' => [
                    'input' => $phrase['inputs'],
                    'weight' => $phrase['weight'],
                ],
            ]),
        );

        if ($suggestions->isNotEmpty()) {
            $documents->index($alias, $suggestions, true);
        }

        $documents->deleteByQuery($alias, [
            'bool' => [
                'must_not' => [
                    'ids' => ['values' => $suggestions->map(static fn (Document $document): string => $document->id())->all()],
                ],
            ],
        ], true);

        $this->info('Indexed ' . $suggestions->count() . ' suggestions into [' . $alias . '].');

        return self::SUCCESS;
    }
}
