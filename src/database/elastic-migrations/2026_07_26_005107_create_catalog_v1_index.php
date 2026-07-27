<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

/**
 * Physical index catalog_v1 + write/read alias "catalog".
 *
 * Documents are built by App\Services\Elasticsearch\CatalogDocumentBuilder.
 */
final class CreateCatalogV1Index implements MigrationInterface
{
    private const string INDEX = 'catalog_v1';

    public function up(): void
    {
        $alias = (string)config('catalog.elasticsearch.alias');

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
                    'catalog_russian' => [
                        'type' => 'custom',
                        'char_filter' => ['yo_to_ye'],
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'russian_stop', 'russian_stemmer'],
                    ],
                    'catalog_sku' => [
                        'type' => 'custom',
                        'char_filter' => ['yo_to_ye'],
                        'tokenizer' => 'keyword',
                        'filter' => ['lowercase', 'sku_word_delimiter'],
                    ],
                ],
                'filter' => [
                    'russian_stop' => [
                        'type' => 'stop',
                        'stopwords' => '_russian_',
                    ],
                    'russian_stemmer' => [
                        'type' => 'stemmer',
                        'language' => 'russian',
                    ],
                    'sku_word_delimiter' => [
                        'type' => 'word_delimiter_graph',
                        'generate_word_parts' => true,
                        'generate_number_parts' => true,
                        'split_on_case_change' => true,
                        'split_on_numerics' => true,
                        'preserve_original' => true,
                    ],
                ],
            ]);

            $russianText = ['analyzer' => 'catalog_russian'];
            $namedEntity = [
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'text', 'analyzer' => 'catalog_russian'],
                ],
            ];

            $mapping->integer('id');
            $mapping->keyword('sku', [
                'fields' => [
                    'text' => [
                        'type' => 'text',
                        'analyzer' => 'catalog_sku',
                    ],
                ],
            ]);
            $mapping->keyword('slug');
            $mapping->object('brand', $namedEntity);
            $mapping->object('categories', $namedEntity);
            $mapping->text('short_name', $russianText);
            $mapping->text('color_txt', $russianText);
            $mapping->object('sizes', $namedEntity);
            $mapping->object('colors', $namedEntity);
            $mapping->object('tags', $namedEntity);

            $mapping->scaledFloat('price', ['scaling_factor' => 100]);
            $mapping->scaledFloat('old_price', ['scaling_factor' => 100]);
            $mapping->boolean('has_discount');
            $mapping->boolean('is_new');
            $mapping->keyword('status_slugs');

            $mapping->integer('season_id');
            $mapping->integer('collection_id');
            $mapping->integer('fabric_ids');
            $mapping->integer('heel_ids');
            $mapping->integer('style_ids');

            $mapping->integer('rating');
            $mapping->integer('newness_rating');
            $mapping->integer('season_rating');
            $mapping->integer('sale_rating');

            $mapping->date('created_at');
        });

        Index::putAlias(self::INDEX, $alias);
    }

    public function down(): void
    {
        $alias = (string)config('catalog.elasticsearch.alias');

        Index::deleteAlias(self::INDEX, $alias);
        Index::dropIfExists(self::INDEX);
    }
}
