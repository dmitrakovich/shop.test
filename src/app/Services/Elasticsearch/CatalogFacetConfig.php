<?php

namespace App\Services\Elasticsearch;

use App\Enums\Catalog\CatalogFacetName;
use Illuminate\Database\Eloquent\Model;

final class CatalogFacetConfig
{
    /**
     * @param  class-string<Model>  $model
     * @param  'id'|'slug'  $key
     * @param  list<string>  $columns
     * @param  list<string>  $extras
     * @param  array<string, mixed>  $where
     */
    public function __construct(
        public readonly CatalogFacetName $name,
        public readonly string $model,
        public readonly string $field,
        public readonly string $key,
        public readonly array $columns,
        public readonly array $extras = [],
        public readonly array $where = [],
    ) {}
}
