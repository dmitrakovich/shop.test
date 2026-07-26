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
    public function up(): void
    {
        $index = (string)config('catalog.elasticsearch.index');
        $alias = (string)config('catalog.elasticsearch.alias');

        Index::create($index, function (Mapping $mapping, Settings $settings): void {
            $settings->index([
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ]);

            $settings->analysis([
                'analyzer' => [
                    'catalog_russian' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'russian_stop', 'russian_stemmer'],
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
                ],
            ]);

            $russianText = ['analyzer' => 'catalog_russian'];

            $mapping->integer('id');
            $mapping->keyword('sku');
            $mapping->keyword('slug');
            $mapping->text('color_txt', $russianText);
            $mapping->text('search_text', $russianText);

            $mapping->scaledFloat('price', ['scaling_factor' => 100]);
            $mapping->scaledFloat('old_price', ['scaling_factor' => 100]);
            $mapping->boolean('has_discount');
            $mapping->boolean('is_new');
            $mapping->keyword('status_slugs');

            $mapping->integer('category_id');
            $mapping->integer('category_ids');
            $mapping->integer('brand_id');
            $mapping->integer('season_id');
            $mapping->integer('collection_id');

            $mapping->integer('size_ids');
            $mapping->integer('color_ids');
            $mapping->integer('fabric_ids');
            $mapping->integer('heel_ids');
            $mapping->integer('style_ids');
            $mapping->integer('tag_ids');

            $mapping->integer('rating');
            $mapping->integer('newness_rating');
            $mapping->integer('season_rating');
            $mapping->integer('sale_rating');

            $mapping->date('created_at');
        });

        Index::putAlias($index, $alias);
    }

    public function down(): void
    {
        $index = (string)config('catalog.elasticsearch.index');
        $alias = (string)config('catalog.elasticsearch.alias');

        Index::deleteAlias($index, $alias);
        Index::dropIfExists($index);
    }
}
