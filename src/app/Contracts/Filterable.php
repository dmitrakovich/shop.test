<?php

namespace App\Contracts;

use App\Models\Url;
use Illuminate\Database\Eloquent\Model;

/**
 * Catalog filter attribute that can produce Elasticsearch filter clauses.
 *
 * @template TModel of Model
 */
interface Filterable
{
    /**
     * Elasticsearch catalog field for this filter, or null when not indexed.
     */
    public static function elasticField(): ?string;

    /**
     * Build Elasticsearch filter clauses for the selected URL filters.
     *
     * @param  array<string, Url>  $values
     * @return list<array<string, mixed>>
     */
    public static function elasticFilterClauses(array $values): array;

    /**
     * Aggregation / hydrate key column (`id` or `slug`).
     *
     * @return 'id'|'slug'
     */
    public static function elasticFacetKey(): string;

    /**
     * Columns selected when hydrating facet metadata from MySQL.
     *
     * @return list<string>
     */
    public static function elasticFacetColumns(): array;

    /**
     * Extra attributes appended to each facet value in the API payload.
     *
     * @return list<string>
     */
    public static function elasticFacetExtras(): array;

    /**
     * Extra WHERE constraints when hydrating facet metadata.
     *
     * @return array<string, mixed>
     */
    public static function elasticFacetWhere(): array;
}
