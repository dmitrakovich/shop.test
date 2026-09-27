<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

/**
 * Phrase suggestions for catalog search autocomplete.
 *
 * Documents are built by App\Console\Commands\CatalogSuggestionsRebuildCommand.
 */
final class CreateCatalogSuggestionsV1Index implements MigrationInterface
{
    private const string INDEX = 'catalog_suggestions_v1';

    public function up(): void
    {
        $alias = (string)config('catalog.elasticsearch.suggestions_alias');

        Index::create(self::INDEX, static function (Mapping $mapping, Settings $settings): void {
            $settings->index([
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ]);

            $settings->analysis([
                'char_filter' => [
                    'yo_to_ye' => [
                        'type' => 'mapping',
                        'mappings' => ['ё=>е', 'Ё=>Е'],
                    ],
                ],
                'analyzer' => [
                    'catalog_suggest' => [
                        'type' => 'custom',
                        'char_filter' => ['yo_to_ye'],
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase'],
                    ],
                ],
            ]);

            $mapping->keyword('text');
            $mapping->completion('suggest', [
                'analyzer' => 'catalog_suggest',
                'search_analyzer' => 'catalog_suggest',
                'max_input_length' => 100,
            ]);
        });

        Index::putAlias(self::INDEX, $alias);
    }

    public function down(): void
    {
        $alias = (string)config('catalog.elasticsearch.suggestions_alias');

        Index::deleteAlias(self::INDEX, $alias);
        Index::dropIfExists(self::INDEX);
    }
}
