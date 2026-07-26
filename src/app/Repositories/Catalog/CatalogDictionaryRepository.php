<?php

namespace App\Repositories\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\Status;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Cached catalog facet dictionaries keyed by id (or slug for statuses).
 */
class CatalogDictionaryRepository
{
    /**
     * Get dictionaries for all facet types used by FilterPreparationService.
     *
     * @return array<string, array<int|string, object>> Dictionaries by facet type
     */
    public function getDictionaries(): array
    {
        return Cache::remember('catalog.es.dictionaries', now()->addDay(), function (): array {
            return [
                'categories' => $this->mapById(
                    Category::query()->get(['id', 'title', 'slug']),
                    nameField: 'title',
                ),
                'brands' => $this->mapById(Brand::query()->get(['id', 'name', 'slug'])),
                'sizes' => $this->mapById(Size::query()->get(['id', 'name', 'slug'])),
                'colors' => $this->mapById(Color::query()->get(['id', 'name', 'slug'])),
                'fabrics' => $this->mapById(Fabric::query()->get(['id', 'name', 'slug'])),
                'heels' => $this->mapById(Heel::query()->get(['id', 'name', 'slug'])),
                'seasons' => $this->mapById(Season::query()->get(['id', 'name', 'slug'])),
                'styles' => $this->mapById(Style::query()->get(['id', 'name', 'slug'])),
                'tags' => $this->mapById(Tag::query()->get(['id', 'name', 'slug'])),
                'collections' => $this->mapById(Collection::query()->get(['id', 'name', 'slug'])),
                'statuses' => $this->mapBySlug(Status::query()->get(['id', 'name', 'slug'])),
            ];
        });
    }

    /**
     * Map models to id-keyed stdClass objects.
     *
     * @param  \Illuminate\Support\Collection<int, object>  $items  Models
     * @param  string  $nameField  Name/title field
     * @return array<int, object> Map by id
     */
    private function mapById($items, string $nameField = 'name'): array
    {
        $out = [];
        foreach ($items as $item) {
            $obj = new stdClass;
            $obj->id = (int)$item->id;
            $obj->name = (string)($item->{$nameField} ?? '');
            $obj->title = (string)($item->title ?? $obj->name);
            $obj->slug = (string)($item->slug ?? '');
            $out[$obj->id] = $obj;
        }

        return $out;
    }

    /**
     * Map models to slug-keyed stdClass objects.
     *
     * @param  \Illuminate\Support\Collection<int, object>  $items  Models
     * @return array<string, object> Map by slug
     */
    private function mapBySlug($items): array
    {
        $out = [];
        foreach ($items as $item) {
            $obj = new stdClass;
            $obj->id = (int)$item->id;
            $obj->name = (string)($item->name ?? '');
            $obj->slug = (string)($item->slug ?? '');
            if ($obj->slug !== '') {
                $out[$obj->slug] = $obj;
            }
        }

        return $out;
    }
}
