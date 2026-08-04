<?php

namespace App\Services\Elasticsearch;

use App\Enums\Catalog\CatalogFacetName;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\Status;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\Tag;

final class CatalogFacetDefinition
{
    /**
     * @return list<CatalogFacetConfig>
     */
    public static function all(): array
    {
        return [
            new CatalogFacetConfig(
                name: CatalogFacetName::Categories,
                model: Category::class,
                field: 'categories.id',
                key: 'id',
                columns: ['id', 'slug', 'path', 'title', 'parent_id'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Statuses,
                model: Status::class,
                field: 'status_slugs',
                key: 'slug',
                columns: ['id', 'name', 'slug'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Fabrics,
                model: Fabric::class,
                field: 'fabric_ids',
                key: 'id',
                columns: ['id', 'name', 'slug'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Collections,
                model: ProductCollection::class,
                field: 'collection_id',
                key: 'id',
                columns: ['id', 'name', 'slug'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Sizes,
                model: Size::class,
                field: 'sizes.id',
                key: 'id',
                columns: ['id', 'name', 'slug', 'insole'],
                extras: ['insole'],
                where: ['is_active' => true],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Colors,
                model: Color::class,
                field: 'colors.id',
                key: 'id',
                columns: ['id', 'name', 'slug', 'value'],
                extras: ['value'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Heels,
                model: Heel::class,
                field: 'heel_ids',
                key: 'id',
                columns: ['id', 'name', 'slug'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Seasons,
                model: Season::class,
                field: 'season_id',
                key: 'id',
                columns: ['id', 'name', 'slug'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Styles,
                model: Style::class,
                field: 'style_ids',
                key: 'id',
                columns: ['id', 'name', 'slug'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Tags,
                model: Tag::class,
                field: 'tags.id',
                key: 'id',
                columns: ['id', 'name', 'slug', 'tag_group_id'],
                extras: ['tag_group_id'],
            ),
            new CatalogFacetConfig(
                name: CatalogFacetName::Brands,
                model: Brand::class,
                field: 'brand.id',
                key: 'id',
                columns: ['id', 'name', 'slug'],
            ),
        ];
    }

    /**
     * @param  class-string  $model
     */
    public static function forModel(string $model): ?CatalogFacetConfig
    {
        foreach (self::all() as $definition) {
            if ($definition->model === $model) {
                return $definition;
            }
        }

        return null;
    }
}
