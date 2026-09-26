<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

/**
 * Sort field for "newness in the current season".
 *
 * Documents are built by App\Services\Elasticsearch\CatalogDocumentBuilder.
 */
final class AddSeasonNewnessRatingToCatalogV1Index implements MigrationInterface
{
    private const string INDEX = 'catalog_v1';

    public function up(): void
    {
        Index::putMapping(self::INDEX, static function (Mapping $mapping): void {
            $mapping->integer('season_newness_rating');
        });
    }

    public function down(): void
    {
        // Elasticsearch cannot remove a mapped field without reindexing.
    }
};
